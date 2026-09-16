<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\CurrencyDenomination;
use App\Models\CurrencyVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class CurrencyDenominationController extends Controller
{
    /**
     * Menampilkan master denomination.
     */
    public function index(Request $request): View
    {
        $currencies = Currency::query()
            ->active()
            ->ordered()
            ->get();

        $selectedCurrencyId = $request->integer('currency_id');

        if (!$selectedCurrencyId && $currencies->isNotEmpty()) {
            $selectedCurrencyId = $currencies->first()->id;
        }

        $variants = collect();

        if ($selectedCurrencyId) {
            $variants = CurrencyVariant::query()
                ->where('currency_id', $selectedCurrencyId)
                ->active()
                ->ordered()
                ->get();
        }

        $selectedVariantId = $request->integer('variant_id');

        if (
            $selectedVariantId &&
            !$variants->contains('id', $selectedVariantId)
        ) {
            $selectedVariantId = null;
        }

        if (!$selectedVariantId && $variants->isNotEmpty()) {
            $selectedVariantId = $variants->first()->id;
        }

        $denominations = collect();

        if ($selectedVariantId) {
            $denominations = CurrencyDenomination::query()
                ->where('currency_variant_id', $selectedVariantId)
                ->ordered()
                ->get();
        }

        return view('settings.denominations.index', [
            'currencies' => $currencies,
            'variants' => $variants,
            'denominations' => $denominations,
            'selectedCurrencyId' => $selectedCurrencyId,
            'selectedVariantId' => $selectedVariantId,
        ]);
    }

    /**
     * Menyimpan denomination baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency_variant_id' => [
                'required',
                'integer',
                'exists:currency_variants,id',
            ],
            'value' => [
                'required',
                'numeric',
                'min:0.000001',
                'max:999999999999.999999',
            ],
            'type' => [
                'required',
                'string',
                Rule::in(CurrencyDenomination::TYPES),
            ],
            'label' => [
                'nullable',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
        ]);

        $variant = CurrencyVariant::query()
            ->with('currency')
            ->findOrFail($validated['currency_variant_id']);

        /*
         * Pastikan variant masih aktif dan currency induknya aktif.
         * Master global tetap harus menjaga hierarchy yang valid.
         */
        if (!$variant->is_active || !$variant->currency->is_active) {
            return back()
                ->withInput()
                ->withErrors([
                    'currency_variant_id' =>
                        'Currency variant yang dipilih tidak aktif.',
                ]);
        }

        /*
         * Duplicate hanya dianggap sama jika:
         * currency_variant_id + value + type sama.
         *
         * Contoh:
         * USD 1 banknote -> boleh
         * USD 1 coin     -> boleh
         * USD 1 banknote -> duplicate
         * USD 1 coin     -> duplicate
         */
        $exists = CurrencyDenomination::query()
            ->where('currency_variant_id', $variant->id)
            ->where('value', $validated['value'])
            ->where('type', $validated['type'])
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'value' =>
                        'Denomination dengan nilai dan tipe tersebut sudah ada pada variant ini.',
                ]);
        }

        CurrencyDenomination::create([
            'currency_variant_id' => $variant->id,
            'value' => $validated['value'],
            'type' => $validated['type'],
            'label' => $validated['label'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('settings.denominations.index', [
                'currency_id' => $variant->currency_id,
                'variant_id' => $variant->id,
            ])
            ->with('success', 'Denomination berhasil ditambahkan.');
    }

    /**
     * Memperbarui denomination.
     */
    public function update(
        Request $request,
        CurrencyDenomination $currencyDenomination
    ): RedirectResponse {
        $validated = $request->validate([
            'value' => [
                'required',
                'numeric',
                'min:0.000001',
                'max:999999999999.999999',
            ],
            'type' => [
                'required',
                'string',
                Rule::in(CurrencyDenomination::TYPES),
            ],
            'label' => [
                'nullable',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
        ]);

        $duplicate = CurrencyDenomination::query()
            ->where('currency_variant_id', $currencyDenomination->currency_variant_id)
            ->where('value', $validated['value'])
            ->where('type', $validated['type'])
            ->whereKeyNot($currencyDenomination->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'value' =>
                        'Denomination dengan nilai dan tipe tersebut sudah ada pada variant ini.',
                ]);
        }

        $currencyDenomination->update([
            'value' => $validated['value'],
            'type' => $validated['type'],
            'label' => $validated['label'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $variant = $currencyDenomination->variant;

        return redirect()
            ->route('settings.denominations.index', [
                'currency_id' => $variant->currency_id,
                'variant_id' => $variant->id,
            ])
            ->with('success', 'Denomination berhasil diperbarui.');
    }

    /**
     * Mengaktifkan / menonaktifkan denomination.
     *
     * Tidak menghapus data secara fisik agar histori
     * transaksi dan rate tetap aman.
     */
    public function toggle(
        CurrencyDenomination $currencyDenomination
    ): RedirectResponse {
        $currencyDenomination->update([
            'is_active' => !$currencyDenomination->is_active,
        ]);

        $variant = $currencyDenomination->variant;

        return redirect()
            ->route('settings.denominations.index', [
                'currency_id' => $variant->currency_id,
                'variant_id' => $variant->id,
            ])
            ->with(
                'success',
                $currencyDenomination->is_active
                    ? 'Denomination berhasil diaktifkan.'
                    : 'Denomination berhasil dinonaktifkan.'
            );
    }
}