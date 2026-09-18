<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\ForexStock;
use App\Models\McTransactionItem;
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
        $previousDate = $day->copy()->subDay()->toDateString();

        // Stok awal diambil dari saldo stok terakhir yang tersimpan sebelum tanggal laporan.
        $openingStocks = ForexStock::query()
            ->with(['variant.currency', 'denomination'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereDate('stock_date', $previousDate)
            ->get()
            ->keyBy('currency_denomination_id');

        // Pembelian/penjualan normal berasal dari item transaksi yang sudah dibayar.
        $items = McTransactionItem::query()
            ->with(['currency', 'currencyVariant', 'currencyDenomination'])
            ->whereHas('transaction', function ($query) use ($tenantId, $branchId, $day) {
                $query->where('tenant_id', $tenantId)
                    ->where('branch_id', $branchId)
                    ->whereDate('transaction_date', $day->toDateString())
                    ->whereIn('status', ['paid', 'completed']);
            })
            ->get();

        $movements = $items->groupBy('currency_denomination_id');
        $denominationIds = $openingStocks->keys()->merge($movements->keys())->filter()->unique();

        $denominations = $denominationIds->isNotEmpty()
            ? \App\Models\CurrencyDenomination::query()
                ->with(['variant.currency'])
                ->whereIn('id', $denominationIds)
                ->get()
                ->keyBy('id')
            : collect();

        $rows = $denominationIds->map(function ($denominationId) use ($openingStocks, $movements, $denominations) {
            $denomination = $denominations->get($denominationId);
            $opening = $openingStocks->get($denominationId);
            $dayItems = $movements->get($denominationId, collect());

            $purchaseItems = $dayItems->where('direction', 'buy');
            $salesItems = $dayItems->where('direction', 'sell');

            $purchaseQty = (float) $purchaseItems->sum('quantity');
            $purchaseRp = (float) $purchaseItems->sum('subtotal');
            $salesQty = (float) $salesItems->sum('quantity');
            $salesRp = (float) $salesItems->sum('subtotal');
            $openingQty = (float) ($opening?->quantity ?? 0);

            $purchaseRate = $purchaseQty > 0 ? $purchaseRp / $purchaseQty : null;
            $salesRate = $salesQty > 0 ? $salesRp / $salesQty : null;

            // Nilai saldo mengikuti arus nominal transaksi. Cost opening belum disimpan
            // pada tabel forex_stocks, sehingga kurs opening/ending akan ditampilkan
            // bila cost basis tersedia dari saldo sebelumnya atau transaksi hari ini.
            $openingRate = $purchaseRate ?? $salesRate;
            $openingRp = $openingRate !== null ? $openingQty * $openingRate : null;
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
