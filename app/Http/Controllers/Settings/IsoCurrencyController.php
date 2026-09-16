<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\CurrencyVariant;
use App\Models\IsoCurrency;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IsoCurrencyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $search = trim((string) $request->input('search', ''));

        $currencies = IsoCurrency::query()
            ->active()
            ->search($search)
            ->with([
                'masterCurrency:id,code,name,flag,is_active',
                'entities' => function ($query) {
                    $query
                        ->orderBy('entity_name')
                        ->select([
                            'id',
                            'iso_currency_id',
                            'country_code',
                            'entity_name',
                        ]);
                },
            ])
            ->orderBy('code')
            ->get([
                'id',
                'code',
                'numeric_code',
                'name',
                'minor_unit',
            ]);

        return response()->json([
            'success' => true,
            'data' => $currencies,
        ]);
    }

    public function show(IsoCurrency $isoCurrency): JsonResponse
    {
        if (!$isoCurrency->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Currency ISO tidak aktif.',
            ], 404);
        }

        $isoCurrency->load([
            'entities' => function ($query) {
                $query->orderBy('entity_name');
            },
        ]);

        $masterCurrency = Currency::query()
            ->where('code', $isoCurrency->code)
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $isoCurrency->id,
                'code' => $isoCurrency->code,
                'numeric_code' => $isoCurrency->numeric_code,
                'name' => $isoCurrency->name,
                'minor_unit' => $isoCurrency->minor_unit,
                'entities' => $isoCurrency->entities,
                'master_currency' => $masterCurrency ? [
                    'id' => $masterCurrency->id,
                    'code' => $masterCurrency->code,
                    'name' => $masterCurrency->name,
                    'flag' => $masterCurrency->flag,
                    'is_active' => $masterCurrency->is_active,
                ] : null,
            ],
        ]);
    }

    /**
     * Tambahkan currency ISO ke Master Currency
     * dan langsung siapkan Standard Series.
     */
    public function addToMaster(IsoCurrency $isoCurrency): JsonResponse
    {
        if (!$isoCurrency->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Currency ISO tidak aktif.',
            ], 422);
        }

        $result = DB::transaction(function () use ($isoCurrency) {
            $currency = Currency::query()
                ->where('code', $isoCurrency->code)
                ->first();

            /*
             * Jika sudah ada di Master dan aktif,
             * jangan membuat duplicate.
             */
            if ($currency) {
                if (!$currency->is_active) {
                    return [
                        'status' => 'inactive',
                        'currency' => $currency,
                        'variant' => null,
                    ];
                }

                $variant = CurrencyVariant::query()
                    ->where('currency_id', $currency->id)
                    ->where('code', 'STD')
                    ->first();

                return [
                    'status' => 'existing',
                    'currency' => $currency,
                    'variant' => $variant,
                ];
            }

            /*
             * Buat Currency Master baru dari ISO 4217.
             *
             * Flag sengaja null.
             * ISO dapat memiliki banyak entity/negara,
             * sehingga kita tidak boleh mengambil satu
             * negara secara sembarangan sebagai flag utama.
             */
            $nextSortOrder = ((int) Currency::query()->max('sort_order')) + 1;

            $currency = Currency::create([
                'code' => strtoupper($isoCurrency->code),
                'name' => $isoCurrency->name,
                'name_local' => null,
                'numeric_code' => $isoCurrency->numeric_code,
                'flag' => null,
                'decimal_digits' => $isoCurrency->minor_unit ?? 2,
                'is_active' => true,
                'sort_order' => $nextSortOrder,
            ]);

            /*
             * Rate membutuhkan Variant / Series.
             * Karena currency baru harus bisa langsung dipakai
             * di Form Rate, buat Standard Series otomatis.
             */
            $variant = CurrencyVariant::create([
                'currency_id' => $currency->id,
                'name' => 'Standard',
                'code' => 'STD',
                'description' => 'Standard series',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 0,
            ]);

            return [
                'status' => 'created',
                'currency' => $currency,
                'variant' => $variant,
            ];
        });

        if ($result['status'] === 'inactive') {
            return response()->json([
                'success' => false,
                'message' => 'Currency dengan kode ' .
                    $isoCurrency->code .
                    ' sudah ada di Master tetapi sedang nonaktif.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'status' => $result['status'],
            'message' => $result['status'] === 'created'
                ? 'Currency berhasil ditambahkan ke Master Currency.'
                : 'Currency sudah tersedia di Master Currency.',
            'data' => [
                'currency' => [
                    'id' => $result['currency']->id,
                    'code' => $result['currency']->code,
                    'name' => $result['currency']->name,
                    'flag' => $result['currency']->flag,
                    'is_active' => $result['currency']->is_active,
                ],
                'variant' => $result['variant'] ? [
                    'id' => $result['variant']->id,
                    'name' => $result['variant']->name,
                    'code' => $result['variant']->code,
                    'is_default' => $result['variant']->is_default,
                    'is_active' => $result['variant']->is_active,
                ] : null,
            ],
        ]);
    }
}