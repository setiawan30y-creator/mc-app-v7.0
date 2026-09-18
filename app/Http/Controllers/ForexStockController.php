<?php

namespace App\Http\Controllers;

use App\Models\CurrencyDenomination;
use App\Models\ForexStock;
use App\Models\McTransactionItem;
use App\Models\OpeningBalance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ForexStockController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tenantId = $user->tenant_id;
        $branchId = $user->branch_id;
        $date = $request->date('date')?->toDateString() ?? Carbon::today()->toDateString();
        $day = Carbon::parse($date)->startOfDay();

        // Saldo awal resmi menjadi baseline pertama stok. Setelah itu,
        // snapshot forex_stocks (jika ada) tetap menjadi opening harian.
        $snapshot = ForexStock::query()
            ->with(['variant.currency', 'denomination'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereDate('stock_date', $day->copy()->subDay()->toDateString())
            ->get()
            ->keyBy('currency_denomination_id');

        $openingBalances = OpeningBalance::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('balance_type', 'forex')
            ->whereDate('balance_date', '<=', $date)
            ->where('status', 'finalized')
            ->with(['variant.currency', 'denomination'])
            ->orderByDesc('balance_date')
            ->get();

        $openingByDenomination = $openingBalances
            ->groupBy('currency_denomination_id')
            ->map(fn ($items) => $items->first());

        $baselineDate = $openingBalances->max('balance_date');

        // Transaksi paid/completed menjadi pembelian/penjualan dan ikut membentuk
        // saldo setelah opening balance. Relasi transaction sengaja di-eager-load
        // karena tanggal transaksi dipakai untuk memisahkan opening dan movement hari ini.
        $items = McTransactionItem::query()
            ->with(['transaction', 'currency', 'currencyVariant', 'currencyDenomination'])
            ->whereHas('transaction', function ($query) use ($tenantId, $branchId, $day, $baselineDate) {
                $query->where('tenant_id', $tenantId)
                    ->where('branch_id', $branchId)
                    ->whereIn('status', ['paid', 'completed'])
                    ->when($baselineDate, fn ($q) => $q->whereDate('transaction_date', '>=', Carbon::parse($baselineDate)->toDateString()))
                    ->whereDate('transaction_date', '<=', $day->toDateString());
            })
            ->get();

        $movements = $items->groupBy('currency_denomination_id');
        $denominationIds = $snapshot->keys()
            ->merge($openingByDenomination->keys())
            ->merge($movements->keys())
            ->filter()->unique();

        $denominations = $denominationIds->isNotEmpty()
            ? CurrencyDenomination::query()->with(['variant.currency'])->whereIn('id', $denominationIds)->get()->keyBy('id')
            : collect();

        $rows = $denominationIds->map(function ($denominationId) use ($snapshot, $openingByDenomination, $movements, $denominations, $day) {
            $denomination = $denominations->get($denominationId);
            $openingBalance = $openingByDenomination->get($denominationId);
            $stockSnapshot = $snapshot->get($denominationId);
            $allItems = $movements->get($denominationId, collect());
            $dayItems = $allItems->filter(fn ($item) => $item->transaction && Carbon::parse($item->transaction->transaction_date)->isSameDay($day));
            $priorItems = $allItems->reject(fn ($item) => $item->transaction && Carbon::parse($item->transaction->transaction_date)->isSameDay($day));

            $baselineQty = (float) ($openingBalance?->quantity ?? $stockSnapshot?->quantity ?? 0);
            $baselineRp = (float) ($openingBalance?->amount_rp ?? 0);

            if ($stockSnapshot) {
                $openingQty = (float) $stockSnapshot->quantity;
                $openingRp = $baselineRp > 0 ? $baselineRp : null;
            } else {
                $priorPurchase = $priorItems->where('direction', 'buy');
                $priorSales = $priorItems->where('direction', 'sell');
                $openingQty = $baselineQty + (float) $priorPurchase->sum('quantity') - (float) $priorSales->sum('quantity');
                $openingRp = $baselineRp + (float) $priorPurchase->sum('subtotal') - (float) $priorSales->sum('subtotal');
                if ($openingRp == 0 && $openingQty == 0) $openingRp = null;
            }

            $purchaseItems = $dayItems->where('direction', 'buy');
            $salesItems = $dayItems->where('direction', 'sell');
            $purchaseQty = (float) $purchaseItems->sum('quantity');
            $purchaseRp = (float) $purchaseItems->sum('subtotal');
            $salesQty = (float) $salesItems->sum('quantity');
            $salesRp = (float) $salesItems->sum('subtotal');

            $purchaseRate = $purchaseQty > 0 ? $purchaseRp / $purchaseQty : null;
            $salesRate = $salesQty > 0 ? $salesRp / $salesQty : null;
            $openingRate = $openingQty != 0 && $openingRp !== null ? $openingRp / $openingQty : null;
            $endingQty = $openingQty + $purchaseQty - $salesQty;
            $endingRp = $openingRp !== null ? $openingRp + $purchaseRp - $salesRp : null;
            $endingRate = ($endingRp !== null && $endingQty != 0) ? $endingRp / $endingQty : null;

            return (object) [
                'currency_code' => $denomination?->variant?->currency?->code ?? '—',
                'currency_name' => $denomination?->variant?->currency?->name ?? '—',
                'variant_name' => $denomination?->variant?->name ?? '—',
                'denomination_label' => $denomination?->display_label ?? '—',
                'opening' => ['qty' => $openingQty, 'rate' => $openingRate, 'rp' => $openingRp],
                'purchase' => ['qty' => $purchaseQty, 'rate' => $purchaseRate, 'rp' => $purchaseRp],
                'sales' => ['qty' => $salesQty, 'rate' => $salesRate, 'rp' => $salesRp],
                'ending' => ['qty' => $endingQty, 'rate' => $endingRate, 'rp' => $endingRp],
            ];
        })->sortBy(['currency_code', 'denomination_label'])->values();

        $summary = [
            'currencies' => $rows->pluck('currency_code')->filter()->unique()->count(),
            'denominations' => $rows->count(),
            'units' => (float) $rows->sum(fn ($row) => $row->ending['qty']),
            'value' => (float) $rows->sum(fn ($row) => $row->ending['rp'] ?? 0),
        ];

        return view('forex-stocks.index', compact('rows', 'summary', 'date'));
    }
}
