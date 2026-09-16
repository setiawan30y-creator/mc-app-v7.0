<?php

namespace Database\Seeders;

use App\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            [
                'code' => 'IDR',
                'name' => 'Indonesian Rupiah',
                'name_local' => 'Rupiah Indonesia',
                'numeric_code' => '360',
                'flag' => '🇮🇩',
                'decimal_digits' => 2,
                'sort_order' => 10,
            ],
            [
                'code' => 'USD',
                'name' => 'US Dollar',
                'name_local' => 'United States Dollar',
                'numeric_code' => '840',
                'flag' => '🇺🇸',
                'decimal_digits' => 2,
                'sort_order' => 20,
            ],
            [
                'code' => 'SGD',
                'name' => 'Singapore Dollar',
                'name_local' => 'Singapore Dollar',
                'numeric_code' => '702',
                'flag' => '🇸🇬',
                'decimal_digits' => 2,
                'sort_order' => 30,
            ],
            [
                'code' => 'MYR',
                'name' => 'Malaysian Ringgit',
                'name_local' => 'Ringgit Malaysia',
                'numeric_code' => '458',
                'flag' => '🇲🇾',
                'decimal_digits' => 2,
                'sort_order' => 40,
            ],
            [
                'code' => 'SAR',
                'name' => 'Saudi Riyal',
                'name_local' => 'Saudi Riyal',
                'numeric_code' => '682',
                'flag' => '🇸🇦',
                'decimal_digits' => 2,
                'sort_order' => 50,
            ],
            [
                'code' => 'AED',
                'name' => 'UAE Dirham',
                'name_local' => 'United Arab Emirates Dirham',
                'numeric_code' => '784',
                'flag' => '🇦🇪',
                'decimal_digits' => 2,
                'sort_order' => 60,
            ],
            [
                'code' => 'EUR',
                'name' => 'Euro',
                'name_local' => 'Euro',
                'numeric_code' => '978',
                'flag' => '🇪🇺',
                'decimal_digits' => 2,
                'sort_order' => 70,
            ],
            [
                'code' => 'GBP',
                'name' => 'Pound Sterling',
                'name_local' => 'Pound Sterling',
                'numeric_code' => '826',
                'flag' => '🇬🇧',
                'decimal_digits' => 2,
                'sort_order' => 80,
            ],
            [
                'code' => 'JPY',
                'name' => 'Japanese Yen',
                'name_local' => 'Japanese Yen',
                'numeric_code' => '392',
                'flag' => '🇯🇵',
                'decimal_digits' => 2,
                'sort_order' => 90,
            ],
            [
                'code' => 'CNY',
                'name' => 'Yuan Renminbi',
                'name_local' => 'Chinese Yuan',
                'numeric_code' => '156',
                'flag' => '🇨🇳',
                'decimal_digits' => 2,
                'sort_order' => 100,
            ],
            [
                'code' => 'KRW',
                'name' => 'Won',
                'name_local' => 'South Korean Won',
                'numeric_code' => '410',
                'flag' => '🇰🇷',
                'decimal_digits' => 2,
                'sort_order' => 110,
            ],
            [
                'code' => 'THB',
                'name' => 'Baht',
                'name_local' => 'Thai Baht',
                'numeric_code' => '764',
                'flag' => '🇹🇭',
                'decimal_digits' => 2,
                'sort_order' => 120,
            ],
            [
                'code' => 'AUD',
                'name' => 'Australian Dollar',
                'name_local' => 'Australian Dollar',
                'numeric_code' => '036',
                'flag' => '🇦🇺',
                'decimal_digits' => 2,
                'sort_order' => 130,
            ],
            [
                'code' => 'CAD',
                'name' => 'Canadian Dollar',
                'name_local' => 'Canadian Dollar',
                'numeric_code' => '124',
                'flag' => '🇨🇦',
                'decimal_digits' => 2,
                'sort_order' => 140,
            ],
            [
                'code' => 'CHF',
                'name' => 'Swiss Franc',
                'name_local' => 'Swiss Franc',
                'numeric_code' => '756',
                'flag' => '🇨🇭',
                'decimal_digits' => 2,
                'sort_order' => 150,
            ],
            [
                'code' => 'NZD',
                'name' => 'New Zealand Dollar',
                'name_local' => 'New Zealand Dollar',
                'numeric_code' => '554',
                'flag' => '🇳🇿',
                'decimal_digits' => 2,
                'sort_order' => 160,
            ],
            [
                'code' => 'INR',
                'name' => 'Indian Rupee',
                'name_local' => 'Indian Rupee',
                'numeric_code' => '356',
                'flag' => '🇮🇳',
                'decimal_digits' => 2,
                'sort_order' => 170,
            ],
            [
                'code' => 'PHP',
                'name' => 'Philippine Peso',
                'name_local' => 'Philippine Peso',
                'numeric_code' => '608',
                'flag' => '🇵🇭',
                'decimal_digits' => 2,
                'sort_order' => 180,
            ],
            [
                'code' => 'VND',
                'name' => 'Vietnamese Dong',
                'name_local' => 'Vietnamese Dong',
                'numeric_code' => '704',
                'flag' => '🇻🇳',
                'decimal_digits' => 3,
                'sort_order' => 190,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                [
                    'name' => $currency['name'],
                    'name_local' => $currency['name_local'],
                    'numeric_code' => $currency['numeric_code'],
                    'flag' => $currency['flag'],
                    'decimal_digits' => $currency['decimal_digits'],
                    'is_active' => true,
                    'sort_order' => $currency['sort_order'],
                ]
            );
        }
    }
}