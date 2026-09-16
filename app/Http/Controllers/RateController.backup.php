<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\RateSnapshot;
use App\Models\RateSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RateController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        $currencies = Currency::query()
            ->active()
            ->ordered()
            ->get();

        $sources = RateSource::query()
            ->where('tenant_id', $user->tenant_id)
            ->active()
            ->ordered()
            ->get();

        $allSources = RateSource::query()
            ->where('tenant_id', $user->tenant_id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $latestRates = RateSnapshot::query()
            ->where('tenant_id', $user->tenant_id)
            ->active()
            ->with(['currency', 'rateSource'])
            ->orderByDesc('effective_at')
            ->get()
            ->unique('currency_id')
            ->keyBy('currency_id');

        return view('settings.rates.index', [
            'currencies' => $currencies,
            'sources' => $sources,
            'allSources' => $allSources,
            'latestRates' => $latestRates,
        ]);
    }

    /**
     * Simpan kurs baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        $validated = $request->validate([
            'currency_id' => [
                'required',
                'integer',
                Rule::exists('currencies', 'id')
                    ->where(fn ($query) => $query->where('is_active', true)),
            ],

            'rate_source_id' => [
                'required',
                'integer',
                Rule::exists('rate_sources', 'id')
                    ->where(fn ($query) => $query
                        ->where('tenant_id', $user->tenant_id)
                        ->where('is_active', true)),
            ],

            'effective_at' => [
                'required',
                'date',
            ],

            'reference_rate' => [
                'required',
                'numeric',
                'min:0',
            ],

            'buy_spread' => [
                'required',
                'numeric',
            ],

            'sell_spread' => [
                'required',
                'numeric',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:500',
            ],
        ]);

        $referenceRate = (float) $validated['reference_rate'];
        $buySpread = (float) $validated['buy_spread'];
        $sellSpread = (float) $validated['sell_spread'];

        $buyRate = $referenceRate + $buySpread;
        $sellRate = $referenceRate + $sellSpread;

        if ($buyRate < 0 || $sellRate < 0) {
            return back()
                ->withInput()
                ->withErrors([
                    'reference_rate' => 'Hasil kurs beli/jual tidak boleh negatif.',
                ]);
        }

        /*
         * URL sumber diambil dari Master Sumber Kurs.
         * Jadi user tidak perlu memasukkan URL setiap kali input kurs.
         */
        $rateSource = RateSource::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $validated['rate_source_id'])
            ->where('is_active', true)
            ->firstOrFail();

        RateSnapshot::create([
            'tenant_id' => $user->tenant_id,
            'currency_id' => $validated['currency_id'],
            'rate_source_id' => $rateSource->id,
            'effective_at' => $validated['effective_at'],
            'reference_rate' => $referenceRate,
            'buy_spread' => $buySpread,
            'sell_spread' => $sellSpread,
            'buy_rate' => $buyRate,
            'sell_rate' => $sellRate,
            'source_url' => $rateSource->url,
            'notes' => $validated['notes'] ?? null,
            'is_active' => true,
            'created_by' => $user->id,
        ]);

        return redirect()
            ->route('settings.rates.index')
            ->with('success', 'Kurs berhasil disimpan.');
    }

    /**
     * Tambah Master Sumber Kurs.
     */
    public function storeSource(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
            ],

            'type' => [
                'required',
                Rule::in([
                    'reference',
                    'official',
                    'market',
                    'manual',
                    'api',
                ]),
            ],

            'url' => [
                'nullable',
                'url',
                'max:500',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
        ]);

        $code = strtoupper(trim($validated['code']));

        $exists = RateSource::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('code', $code)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'code' => 'Kode sumber kurs tersebut sudah digunakan.',
                ]);
        }

        RateSource::create([
            'tenant_id' => $user->tenant_id,
            'name' => trim($validated['name']),
            'code' => $code,
            'type' => $validated['type'],
            'url' => $validated['url'] ?? null,
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('settings.rates.index')
            ->with('success', 'Sumber kurs berhasil ditambahkan.');
    }

    /**
     * Update Master Sumber Kurs.
     */
    public function updateSource(
        Request $request,
        RateSource $rateSource
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        abort_unless(
            $rateSource->tenant_id === $user->tenant_id,
            404
        );

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'code' => [
                'required',
                'string',
                'max:30',
                'alpha_dash',
            ],

            'type' => [
                'required',
                Rule::in([
                    'reference',
                    'official',
                    'market',
                    'manual',
                    'api',
                ]),
            ],

            'url' => [
                'nullable',
                'url',
                'max:500',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:9999',
            ],
        ]);

        $code = strtoupper(trim($validated['code']));

        $exists = RateSource::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('code', $code)
            ->where('id', '!=', $rateSource->id)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'code' => 'Kode sumber kurs tersebut sudah digunakan.',
                ]);
        }

        $rateSource->update([
            'name' => trim($validated['name']),
            'code' => $code,
            'type' => $validated['type'],
            'url' => $validated['url'] ?? null,
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()
            ->route('settings.rates.index')
            ->with('success', 'Sumber kurs berhasil diperbarui.');
    }

    /**
     * Aktif/nonaktifkan Master Sumber Kurs.
     */
    public function toggleSource(
        Request $request,
        RateSource $rateSource
    ): RedirectResponse {
        $user = $request->user();

        abort_unless($user && $user->tenant_id, 403);

        abort_unless(
            $rateSource->tenant_id === $user->tenant_id,
            404
        );

        $rateSource->update([
            'is_active' => ! $rateSource->is_active,
        ]);

        return redirect()
            ->route('settings.rates.index')
            ->with(
                'success',
                $rateSource->is_active
                    ? 'Sumber kurs diaktifkan.'
                    : 'Sumber kurs dinonaktifkan.'
            );
    }
}