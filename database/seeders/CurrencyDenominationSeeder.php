<?php

namespace Database\Seeders;

use App\Models\CurrencyDenomination;
use App\Models\CurrencyVariant;
use Illuminate\Database\Seeder;

class CurrencyDenominationSeeder extends Seeder
{
    public function run(): void
    {
        $variant = CurrencyVariant::query()
            ->whereHas('currency', function ($query) {
                $query->where('code', 'USD');
            })
            ->where('name', 'Standard')
            ->firstOrFail();

        $denominations = [
            1,
            5,
            10,
            20,
            50,
            100,
        ];

        foreach ($denominations as $sortOrder => $value) {
            CurrencyDenomination::updateOrCreate(
                [
                    'currency_variant_id' => $variant->id,
                    'value' => $value,
                ],
                [
                    'label' => (string) $value,
                    'description' => 'Pecahan USD Standard.',
                    'is_active' => true,
                    'sort_order' => $sortOrder + 1,
                ]
            );
        }
    }
}