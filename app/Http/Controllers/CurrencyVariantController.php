<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\CurrencyVariant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CurrencyVariantController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        $currencies = Currency::query()
            ->active()
            ->ordered()
            ->get();

        if ($currencies->isEmpty()) {
            return view('settings.currency-variants.index', [
                'currencies' => $currencies,
                'variants' => collect(),
                'selectedCurrencyId' => null,
            ]);
        }

        $selectedCurrencyId = $request->integer('currency_id');

        if (
            !$selectedCurrencyId ||
            !$currencies->contains('id', $selectedCurrencyId)
        ) {
            $selectedCurrencyId = $currencies->first()->id;
        }

        $variants = CurrencyVariant::query()
            ->where('currency_id', $selectedCurrencyId)
            ->ordered()
            ->get();

        if (
            $request->has('currency_id') &&
            (int) $request->integer('currency_id') !== (int) $selectedCurrencyId
        ) {
            return redirect()
                ->route('settings.currency-variants.index', [
                    'currency_id' => $selectedCurrencyId,
                ]);
        }

        return view('settings.currency-variants.index', [
            'currencies' => $currencies,
            'variants' => $variants,
            'selectedCurrencyId' => $selectedCurrencyId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'currency_id' => [
                'required',
                'integer',
                'exists:currencies,id',
            ],
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_default' => [
                'nullable',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
        ]);

        $currency = Currency::query()->findOrFail($validated['currency_id']);

        if (!$currency->is_active) {
            return back()
                ->withInput()
                ->withErrors([
                    'currency_id' => 'Currency yang dipilih tidak aktif.',
                ]);
        }

        $nameExists = CurrencyVariant::query()
            ->where('currency_id', $currency->id)
            ->whereRaw('LOWER(name) = ?', [
                mb_strtolower(trim($validated['name'])),
            ])
            ->exists();

        if ($nameExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Nama variant tersebut sudah digunakan pada currency ini.',
                ]);
        }

        if (
            isset($validated['code']) &&
            trim((string) $validated['code']) !== ''
        ) {
            $code = trim((string) $validated['code']);

            $codeExists = CurrencyVariant::query()
                ->where('currency_id', $currency->id)
                ->whereRaw('LOWER(code) = ?', [
                    mb_strtolower($code),
                ])
                ->exists();

            if ($codeExists) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'code' => 'Kode variant tersebut sudah digunakan pada currency ini.',
                    ]);
            }

            $validated['code'] = $code;
        } else {
            $validated['code'] = null;
        }

        $isDefault = (bool) ($validated['is_default'] ?? false);

        /*
         * Variant baru selalu aktif.
         * Jika dijadikan default, default variant lain pada
         * currency yang sama harus dilepas terlebih dahulu.
         */
        if ($isDefault) {
            CurrencyVariant::query()
                ->where('currency_id', $currency->id)
                ->update([
                    'is_default' => false,
                ]);
        }

        CurrencyVariant::create([
            'currency_id' => $currency->id,
            'name' => trim($validated['name']),
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'is_default' => $isDefault,
            'is_active' => true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('settings.currency-variants.index', [
                'currency_id' => $currency->id,
            ])
            ->with('success', 'Currency variant berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        CurrencyVariant $currencyVariant
    ): RedirectResponse {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'is_default' => [
                'nullable',
                'boolean',
            ],
            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:999999',
            ],
        ]);

        $currency = $currencyVariant->currency;

        if (!$currency || !$currency->is_active) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Currency parent tidak aktif atau tidak ditemukan.',
                ]);
        }

        $nameExists = CurrencyVariant::query()
            ->where('currency_id', $currencyVariant->currency_id)
            ->whereRaw('LOWER(name) = ?', [
                mb_strtolower(trim($validated['name'])),
            ])
            ->whereKeyNot($currencyVariant->id)
            ->exists();

        if ($nameExists) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'Nama variant tersebut sudah digunakan pada currency ini.',
                ]);
        }

        if (
            isset($validated['code']) &&
            trim((string) $validated['code']) !== ''
        ) {
            $code = trim((string) $validated['code']);

            $codeExists = CurrencyVariant::query()
                ->where('currency_id', $currencyVariant->currency_id)
                ->whereRaw('LOWER(code) = ?', [
                    mb_strtolower($code),
                ])
                ->whereKeyNot($currencyVariant->id)
                ->exists();

            if ($codeExists) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'code' => 'Kode variant tersebut sudah digunakan pada currency ini.',
                    ]);
            }

            $validated['code'] = $code;
        } else {
            $validated['code'] = null;
        }

        $isDefault = (bool) ($validated['is_default'] ?? false);

        /*
         * Variant nonaktif tidak boleh menjadi default.
         */
        if (!$currencyVariant->is_active) {
            $isDefault = false;
        }

        if ($isDefault) {
            CurrencyVariant::query()
                ->where('currency_id', $currencyVariant->currency_id)
                ->whereKeyNot($currencyVariant->id)
                ->update([
                    'is_default' => false,
                ]);
        }

        $currencyVariant->update([
            'name' => trim($validated['name']),
            'code' => $validated['code'],
            'description' => $validated['description'] ?? null,
            'is_default' => $isDefault,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('settings.currency-variants.index', [
                'currency_id' => $currencyVariant->currency_id,
            ])
            ->with('success', 'Currency variant berhasil diperbarui.');
    }

    public function toggle(
        CurrencyVariant $currencyVariant
    ): RedirectResponse {
        /*
         * Jika variant aktif akan dinonaktifkan dan saat ini
         * merupakan default, default harus dilepas.
         */
        if ($currencyVariant->is_active) {
            $currencyVariant->update([
                'is_active' => false,
                'is_default' => false,
            ]);

            return redirect()
                ->route('settings.currency-variants.index', [
                    'currency_id' => $currencyVariant->currency_id,
                ])
                ->with(
                    'success',
                    'Currency variant berhasil dinonaktifkan.'
                );
        }

        $currency = $currencyVariant->currency;

        if (!$currency || !$currency->is_active) {
            return back()
                ->withErrors([
                    'currency_id' => 'Currency parent tidak aktif.',
                ]);
        }

        $currencyVariant->update([
            'is_active' => true,
        ]);

        return redirect()
            ->route('settings.currency-variants.index', [
                'currency_id' => $currencyVariant->currency_id,
            ])
            ->with(
                'success',
                'Currency variant berhasil diaktifkan.'
            );
    }

    public function setDefault(
        CurrencyVariant $currencyVariant
    ): RedirectResponse {
        $currency = $currencyVariant->currency;

        if (!$currency || !$currency->is_active) {
            return back()
                ->withErrors([
                    'currency_id' => 'Currency parent tidak aktif.',
                ]);
        }

        if (!$currencyVariant->is_active) {
            return back()
                ->withErrors([
                    'name' => 'Variant yang nonaktif tidak dapat dijadikan default.',
                ]);
        }

        CurrencyVariant::query()
            ->where('currency_id', $currencyVariant->currency_id)
            ->update([
                'is_default' => false,
            ]);

        $currencyVariant->update([
            'is_default' => true,
        ]);

        return redirect()
            ->route('settings.currency-variants.index', [
                'currency_id' => $currencyVariant->currency_id,
            ])
            ->with(
                'success',
                'Currency variant berhasil dijadikan default.'
            );
    }
}
