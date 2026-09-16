<?php

namespace Database\Seeders;

use App\Models\RateSource;
use Illuminate\Database\Seeder;

class RateSourceSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = '01m27xwzveq1rjdw7axwzybef4';

        $sources = [
            [
                'name' => 'SmartDeal',
                'code' => 'SMARTDEAL',
                'type' => 'reference',
                'url' => 'https://www.smartdeal.co.id/',
                'description' => 'Sumber kurs operasional Money Changer dari SmartDeal.',
                'is_active' => true,
                'sort_order' => 10,
            ],
            [
                'name' => 'Bank Indonesia',
                'code' => 'BI',
                'type' => 'official',
                'url' => 'https://www.bi.go.id/id/fungsi-utama/moneter/informasi-kurs/default.aspx',
                'description' => 'Referensi kurs resmi Bank Indonesia dan standar referensi mata uang.',
                'is_active' => true,
                'sort_order' => 20,
            ],
        ];

        foreach ($sources as $source) {
            RateSource::updateOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'code' => $source['code'],
                ],
                [
                    'name' => $source['name'],
                    'type' => $source['type'],
                    'url' => $source['url'],
                    'description' => $source['description'],
                    'is_active' => $source['is_active'],
                    'sort_order' => $source['sort_order'],
                ]
            );
        }
    }
}