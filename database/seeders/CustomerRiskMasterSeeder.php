<?php

namespace Database\Seeders;

use App\Models\CustomerRiskMaster;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class CustomerRiskMasterSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = Tenant::query()->get();

        if ($tenants->isEmpty()) {
            $this->command?->warn(
                'Tidak ada tenant. CustomerRiskMasterSeeder tidak menjalankan seeding.'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | WARGA NEGARA
        |--------------------------------------------------------------------------
        |
        | Kode menggunakan ISO 3166-1 alpha-2.
        | Risk score adalah referensi awal internal aplikasi,
        | bukan skor resmi BI/PPATK.
        |
        */

        $nationalities = [
            ['Indonesia', 'ID', 'low', 0],
            ['Malaysia', 'MY', 'low', 0],
            ['Singapura', 'SG', 'low', 0],
            ['Brunei Darussalam', 'BN', 'low', 0],
            ['Thailand', 'TH', 'low', 0],
            ['Filipina', 'PH', 'low', 0],
            ['Vietnam', 'VN', 'low', 0],
            ['Kamboja', 'KH', 'low', 0],
            ['Laos', 'LA', 'low', 0],
            ['Myanmar', 'MM', 'medium', 10],
            ['Timor-Leste', 'TL', 'low', 0],

            ['Tiongkok', 'CN', 'medium', 10],
            ['Hong Kong', 'HK', 'medium', 10],
            ['Makau', 'MO', 'medium', 10],
            ['Taiwan', 'TW', 'medium', 10],
            ['Jepang', 'JP', 'low', 0],
            ['Korea Selatan', 'KR', 'low', 0],
            ['India', 'IN', 'low', 0],
            ['Pakistan', 'PK', 'medium', 10],
            ['Bangladesh', 'BD', 'medium', 10],
            ['Nepal', 'NP', 'low', 0],
            ['Sri Lanka', 'LK', 'low', 0],

            ['Australia', 'AU', 'low', 0],
            ['Selandia Baru', 'NZ', 'low', 0],

            ['Amerika Serikat', 'US', 'low', 0],
            ['Kanada', 'CA', 'low', 0],
            ['Meksiko', 'MX', 'low', 0],
            ['Brasil', 'BR', 'low', 0],
            ['Argentina', 'AR', 'low', 0],
            ['Chili', 'CL', 'low', 0],

            ['Inggris', 'GB', 'low', 0],
            ['Irlandia', 'IE', 'low', 0],
            ['Prancis', 'FR', 'low', 0],
            ['Jerman', 'DE', 'low', 0],
            ['Belanda', 'NL', 'low', 0],
            ['Belgia', 'BE', 'low', 0],
            ['Swiss', 'CH', 'low', 0],
            ['Austria', 'AT', 'low', 0],
            ['Italia', 'IT', 'low', 0],
            ['Spanyol', 'ES', 'low', 0],
            ['Portugal', 'PT', 'low', 0],
            ['Norwegia', 'NO', 'low', 0],
            ['Swedia', 'SE', 'low', 0],
            ['Denmark', 'DK', 'low', 0],
            ['Finlandia', 'FI', 'low', 0],
            ['Islandia', 'IS', 'low', 0],
            ['Polandia', 'PL', 'low', 0],
            ['Ceko', 'CZ', 'low', 0],
            ['Hongaria', 'HU', 'low', 0],
            ['Rumania', 'RO', 'low', 0],
            ['Bulgaria', 'BG', 'low', 0],
            ['Yunani', 'GR', 'low', 0],
            ['Ukraina', 'UA', 'medium', 10],
            ['Rusia', 'RU', 'medium', 10],

            ['Arab Saudi', 'SA', 'medium', 10],
            ['Uni Emirat Arab', 'AE', 'low', 0],
            ['Qatar', 'QA', 'low', 0],
            ['Kuwait', 'KW', 'low', 0],
            ['Bahrain', 'BH', 'low', 0],
            ['Oman', 'OM', 'low', 0],
            ['Yordania', 'JO', 'medium', 10],
            ['Lebanon', 'LB', 'medium', 10],
            ['Israel', 'IL', 'medium', 10],
            ['Turki', 'TR', 'low', 0],

            ['Afrika Selatan', 'ZA', 'low', 0],
            ['Mesir', 'EG', 'medium', 10],
            ['Maroko', 'MA', 'low', 0],
            ['Tunisia', 'TN', 'low', 0],
            ['Aljazair', 'DZ', 'medium', 10],
            ['Nigeria', 'NG', 'high', 30],
            ['Kenya', 'KE', 'low', 0],
            ['Tanzania', 'TZ', 'low', 0],
            ['Ghana', 'GH', 'low', 0],

            ['Lainnya', 'XX', 'medium', 10],
        ];


        /*
        |--------------------------------------------------------------------------
        | BIDANG PEKERJAAN
        |--------------------------------------------------------------------------
        |
        | Risk score adalah referensi awal internal.
        | Penilaian final nantinya dilakukan oleh Risk Engine
        | berdasarkan kombinasi berbagai faktor.
        |
        */

        $occupations = [
            ['Pegawai Swasta', 'EMP_PRIVATE', 'low', 0],
            ['Pegawai Negeri / ASN', 'EMP_ASN', 'low', 0],
            ['TNI', 'MILITARY', 'medium', 10],
            ['POLRI', 'POLICE', 'medium', 10],
            ['Pegawai BUMN', 'EMP_BUMN', 'low', 0],
            ['Pegawai BUMD', 'EMP_BUMD', 'low', 0],

            ['Pengusaha / Wiraswasta', 'ENTREPRENEUR', 'low', 0],
            ['Pemilik Perusahaan', 'OWNER', 'low', 0],
            ['Direktur / Komisaris', 'DIRECTOR', 'medium', 10],
            ['Pedagang', 'TRADER', 'low', 0],
            ['Pedagang Valuta Asing', 'MONEY_CHANGER', 'medium', 10],

            ['Dokter', 'DOCTOR', 'low', 0],
            ['Dokter Spesialis', 'SPECIALIST_DOCTOR', 'low', 0],
            ['Apoteker', 'PHARMACIST', 'low', 0],
            ['Perawat', 'NURSE', 'low', 0],
            ['Tenaga Kesehatan', 'HEALTH_WORKER', 'low', 0],

            ['Pengacara', 'LAWYER', 'medium', 10],
            ['Notaris', 'NOTARY', 'medium', 10],
            ['PPAT', 'PPAT', 'medium', 10],
            ['Akuntan', 'ACCOUNTANT', 'low', 0],
            ['Konsultan', 'CONSULTANT', 'low', 0],
            ['Auditor', 'AUDITOR', 'low', 0],

            ['Guru', 'TEACHER', 'low', 0],
            ['Dosen', 'LECTURER', 'low', 0],
            ['Peneliti', 'RESEARCHER', 'low', 0],

            ['Petani', 'FARMER', 'low', 0],
            ['Nelayan', 'FISHERMAN', 'low', 0],
            ['Peternak', 'BREEDER', 'low', 0],
            ['Buruh', 'LABORER', 'low', 0],
            ['Pekerja Migran', 'MIGRANT_WORKER', 'medium', 10],
            ['Pekerja Lepas / Freelancer', 'FREELANCER', 'low', 0],

            ['Karyawan Rumah Tangga', 'HOUSEHOLD_WORKER', 'low', 0],
            ['Ibu Rumah Tangga', 'HOUSEWIFE', 'low', 0],
            ['Pelajar', 'STUDENT', 'low', 0],
            ['Mahasiswa', 'COLLEGE_STUDENT', 'low', 0],
            ['Pensiunan', 'RETIREE', 'low', 0],
            ['Tidak Bekerja', 'UNEMPLOYED', 'medium', 10],

            ['Investor', 'INVESTOR', 'medium', 10],
            ['Pemegang Saham', 'SHAREHOLDER', 'medium', 10],
            ['Perbankan / Keuangan', 'FINANCE', 'low', 0],
            ['Asuransi', 'INSURANCE', 'low', 0],
            ['Properti / Real Estate', 'REAL_ESTATE', 'medium', 10],

            ['Teknologi Informasi', 'IT', 'low', 0],
            ['Programmer / Developer', 'DEVELOPER', 'low', 0],
            ['Desainer', 'DESIGNER', 'low', 0],
            ['Jurnalis', 'JOURNALIST', 'low', 0],
            ['Artis / Pekerja Seni', 'ARTIST', 'low', 0],
            ['Atlet', 'ATHLETE', 'low', 0],

            ['Lainnya', 'OTHER', 'low', 0],
        ];


        /*
        |--------------------------------------------------------------------------
        | SEED KE SETIAP TENANT
        |--------------------------------------------------------------------------
        */

        foreach ($tenants as $tenant) {

            foreach ($nationalities as [$name, $code, $riskLevel, $riskScore]) {

                CustomerRiskMaster::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'type' => 'nationality',
                        'name' => $name,
                    ],
                    [
                        'code' => $code,
                        'risk_level' => $riskLevel,
                        'risk_score' => $riskScore,
                        'description' => 'Referensi kewarganegaraan customer.',
                        'is_active' => true,
                    ]
                );
            }


            foreach ($occupations as [$name, $code, $riskLevel, $riskScore]) {

                CustomerRiskMaster::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'type' => 'occupation',
                        'name' => $name,
                    ],
                    [
                        'code' => $code,
                        'risk_level' => $riskLevel,
                        'risk_score' => $riskScore,
                        'description' => 'Referensi bidang pekerjaan customer.',
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info(
            'Master Customer Risk berhasil disiapkan untuk '
            . $tenants->count()
            . ' tenant.'
        );
    }
}