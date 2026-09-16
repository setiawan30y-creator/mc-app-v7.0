<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\CurrencyVariant;
use Illuminate\Database\Seeder;

class CurrencyVariantSeeder extends Seeder
{
    public function run(): void
    {
        $currencies = Currency::query()
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get();

        foreach ($currencies as $currency) {
            CurrencyVariant::updateOrCreate(
                [
                    'currency_id' => $currency->id,
                    'name' => 'Standard',
                ],
                [
                    'code' => 'STD',
                    'description' => 'Variant standar/default untuk mata uang ini.',
                    'is_default' => true,
                    'is_active' => true,
                    'sort_order' => 1,
                ]
            );
        }
    }
}