<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\CurrencyDenomination;
use App\Models\CurrencyVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurrencyDenominationController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        /*
         * ============================================================
         * 1. LOAD CURRENCY AKTIF
         * ============================================================
         *
         * Currency adalah parent utama dari Variant.
         */
        $currencies = Currency::query()
            ->active()
            ->ordered()
            ->get();

        /*
         * Ambil currency_id dari URL.
         *
         * Jika kosong atau tidak ditemukan pada currency aktif,
         * gunakan currency aktif pertama.
         */
        $selectedCurrencyId = $request->integer('currency_id');

        if (
            !$selectedCurrencyId ||
            !$currencies->contains('id', $selectedCurrencyId)
        ) {
            $selectedCurrencyId = $currencies->first()?->id;
        }

        /*
         * ============================================================
         * 2. LOAD VARIANT BERDASARKAN CURRENCY
         * ============================================================
         *
         * HANYA variant milik currency yang dipilih yang boleh tampil.
         *
         * Contoh:
         *
         * currency_id = 2 (SGD)
         *
         * maka variant yang boleh tampil hanya:
         * variant_id = 2 (SGD Standard)
         *
         * Variant IDR 18 tidak boleh ikut.
         */
        $variants = collect();

        if ($selectedCurrencyId) {
            $variants = CurrencyVariant::query()
                ->where('currency_id', $selectedCurrencyId)
                ->active()
                ->ordered()
                ->get();
        }

        /*
         * ============================================================
         * 3. VALIDASI VARIANT
         * ============================================================
         *
         * Ambil variant_id dari URL.
         */
        $selectedVariantId = $request->integer('variant_id');

        /*
         * Jika variant kosong atau variant tersebut bukan milik
         * currency yang sedang dipilih, gunakan variant pertama
         * yang valid.
         */
        if (
            !$selectedVariantId ||
            !$variants->contains('id', $selectedVariantId)
        ) {
            $selectedVariantId = $variants->first()?->id;
        }

        /*
         * ============================================================
         * 4. CANONICAL URL
         * ============================================================
         *
         * Pastikan URL browser selalu mencerminkan kombinasi
         * Currency + Variant yang benar.
         *
         * Contoh URL SALAH:
         *
         * ?currency_id=2&variant_id=18
         *
         * karena:
         * currency_id 2 = SGD
         * variant_id 18 = IDR
         *
         * Maka otomatis diarahkan menjadi:
         *
         * ?currency_id=2&variant_id=2
         *
         * Ini membuat URL tidak menyimpan kombinasi parent-child
         * yang tidak valid.
         */
        $requestCurrencyId = $request->integer('currency_id');
        $requestVariantId = $request->integer('variant_id');

        if (
            $selectedCurrencyId &&
            $selectedVariantId &&
            (
                $requestCurrencyId !== (int) $selectedCurrencyId ||
                $requestVariantId !== (int) $selectedVariantId
            )
        ) {
            return redirect()->route('settings.denominations.index', [
                'currency_id' => $selectedCurrencyId,
                'variant_id' => $selectedVariantId,
            ]);
        }

        /*
         * ============================================================
         * 5. LOAD DENOMINATION
         * ============================================================
         *
         * Denomination hanya boleh mengambil data dari variant
         * yang sudah tervalidasi di atas.
         */
        $denominations = collect();

        if ($selectedVariantId) {
            $denominations = CurrencyDenomination::query()
                ->where('currency_variant_id', $selectedVariantId)
                ->ordered()
                ->get();
        }

        /*
         * ============================================================
         * 6. RENDER VIEW
         * ============================================================
         */
        return view('settings.denominations.index', [
            'currencies' => $currencies,
            'variants' => $variants,
            'denominations' => $denominations,
            'selectedCurrencyId' => $selectedCurrencyId,
            'selectedVariantId' => $selectedVariantId,
        ]);
    }

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

        /*
         * Pastikan variant benar-benar ada dan ambil parent Currency.
         */
        $variant = CurrencyVariant::query()
            ->with('currency')
            ->findOrFail($validated['currency_variant_id']);

        /*
         * Variant dan Currency parent harus aktif.
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
         * Cegah duplikasi denomination pada:
         *
         * Variant + Value + Type
         *
         * Contoh:
         * USD Standard + 100 + Banknote
         *
         * boleh berdampingan dengan:
         * USD Standard + 100 + Coin
         *
         * jika memang dibutuhkan oleh data master.
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

        /*
         * Setelah berhasil, kembali ke Currency + Variant yang sama.
         */
        return redirect()
            ->route('settings.denominations.index', [
                'currency_id' => $variant->currency_id,
                'variant_id' => $variant->id,
            ])
            ->with('success', 'Denomination berhasil ditambahkan.');
    }

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

        /*
         * Cegah duplikasi pada Variant + Value + Type,
         * kecuali record yang sedang diedit.
         */
        $duplicate = CurrencyDenomination::query()
            ->where(
                'currency_variant_id',
                $currencyDenomination->currency_variant_id
            )
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