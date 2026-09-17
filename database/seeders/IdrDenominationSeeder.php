<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\CurrencyDenomination;
use App\Models\CurrencyVariant;
use Illuminate\Database\Seeder;

class IdrDenominationSeeder extends Seeder
{
    public function run(): void
    {
        $idr = Currency::query()->where('code', 'IDR')->first();

        if (! $idr) {
            $this->command?->warn('Currency IDR tidak ditemukan. Seeder pecahan IDR dilewati.');
            return;
        }

        $variant = CurrencyVariant::query()->firstOrCreate(
            [
                'currency_id' => $idr->id,
                'name' => 'Uang Rupiah Indonesia',
            ],
            [
                'code' => 'IDR-CURRENT',
                'description' => 'Seri pecahan Rupiah untuk operasional kas.',
                'is_default' => true,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $variant->update([
            'is_active' => true,
            'is_default' => true,
        ]);

        $denominations = [
            [100000, 'banknote', 'Rp 100.000', 1],
            [75000, 'banknote', 'Rp 75.000', 2],
            [50000, 'banknote', 'Rp 50.000', 3],
            [20000, 'banknote', 'Rp 20.000', 4],
            [10000, 'banknote', 'Rp 10.000', 5],
            [5000, 'banknote', 'Rp 5.000', 6],
            [2000, 'banknote', 'Rp 2.000', 7],
            [1000, 'banknote', 'Rp 1.000', 8],
            [1000, 'coin', 'Rp 1.000 Koin', 9],
            [500, 'coin', 'Rp 500 Koin', 10],
            [200, 'coin', 'Rp 200 Koin', 11],
            [100, 'coin', 'Rp 100 Koin', 12],
        ];

        foreach ($denominations as [$value, $type, $label, $sortOrder]) {
            CurrencyDenomination::query()->updateOrCreate(
                [
                    'currency_variant_id' => $variant->id,
                    'value' => $value,
                    'type' => $type,
                ],
                [
                    'label' => $label,
                    'description' => 'Pecahan IDR untuk perhitungan kas fisik.',
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]
            );
        }

        // Nonaktifkan pecahan yang tidak digunakan untuk Closing Kas Fisik.
        CurrencyDenomination::query()
            ->where('currency_variant_id', $variant->id)
            ->whereIn('value', [200000, 500000, 1000000])
            ->update(['is_active' => false]);
    }
}
