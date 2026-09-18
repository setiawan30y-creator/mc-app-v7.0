<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\CurrencyDenomination;
use App\Models\ForexStock;
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

        $stocks = ForexStock::query()
            ->with(['variant.currency', 'denomination'])
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereDate('stock_date', $date)
            ->orderBy('currency_variant_id')
            ->get();

        $currencies = Currency::query()
            ->with(['variants' => fn ($q) => $q->active()->ordered()->with(['denominations' => fn ($d) => $d->active()->ordered()])])
            ->active()
            ->ordered()
            ->get();

        $currencyVariants = $currencies->flatMap(function ($currency) {
            return $currency->variants->map(function ($variant) {
                return [
                    'id' => $variant->id,
                    'denominations' => $variant->denominations->map(function ($denomination) {
                        return [
                            'id' => $denomination->id,
                            'label' => $denomination->display_label,
                            'type' => $denomination->type_label,
                        ];
                    })->values()->all(),
                ];
            });
        })->values()->all();

        $summary = [
            'currencies' => $stocks->pluck('variant.currency.code')->filter()->unique()->count(),
            'denominations' => $stocks->count(),
            'units' => (float) $stocks->sum('quantity'),
            'value' => (float) $stocks->sum(fn ($stock) => ((float) $stock->quantity) * ((float) $stock->denomination->value)),
        ];

        return view('forex-stocks.index', compact('stocks', 'currencies', 'currencyVariants', 'summary', 'date'));
    }

    public function upsert(Request $request)
    {
        $data = $request->validate([
            'currency_variant_id' => ['required', 'exists:currency_variants,id'],
            'currency_denomination_id' => ['required', 'exists:currency_denominations,id'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'stock_date' => ['required', 'date'],
        ]);

        $user = $request->user();
        $denomination = CurrencyDenomination::with('variant')->findOrFail($data['currency_denomination_id']);

        abort_unless((string) $denomination->currency_variant_id === (string) $data['currency_variant_id'], 422);

        ForexStock::updateOrCreate(
            [
                'tenant_id' => $user->tenant_id,
                'branch_id' => $user->branch_id,
                'currency_denomination_id' => $denomination->id,
                'stock_date' => $data['stock_date'],
            ],
            [
                'currency_variant_id' => $denomination->currency_variant_id,
                'quantity' => $data['quantity'],
            ]
        );

        return back()->with('success', 'Stok valas berhasil disimpan.');
    }
}
