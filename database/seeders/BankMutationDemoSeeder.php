<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\BankMutation;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BankMutationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->firstOrFail();

        $branch = Branch::query()
            ->where('tenant_id', $tenant->id)
            ->firstOrFail();

        $idr = Currency::query()
            ->where('code', 'IDR')
            ->firstOrFail();

        $banks = [
            [
                'bank_name' => 'BCA',
                'bank_code' => '014',
                'account_name' => 'PT Almara Putra Valasindo',
                'account_number' => '1234567890',
                'opening_balance' => 25000000,
                'mutations' => [
                    [
                        'days' => 4,
                        'reference' => 'BCA-TRF-0001',
                        'description' => 'Transfer masuk dari pelanggan',
                        'debit' => 0,
                        'credit' => 15000000,
                        'balance' => 40000000,
                        'status' => 'matched',
                        'matched_transaction_id' => '01DEMOBCATRF00000000000001',
                    ],
                    [
                        'days' => 3,
                        'reference' => 'BCA-PAY-0002',
                        'description' => 'Pembayaran supplier',
                        'debit' => 5000000,
                        'credit' => 0,
                        'balance' => 35000000,
                        'status' => 'unmatched',
                        'matched_transaction_id' => null,
                    ],
                    [
                        'days' => 2,
                        'reference' => 'BCA-CASH-0003',
                        'description' => 'Setoran tunai operasional',
                        'debit' => 0,
                        'credit' => 7500000,
                        'balance' => 42500000,
                        'status' => 'manual',
                        'matched_transaction_id' => null,
                    ],
                    [
                        'days' => 1,
                        'reference' => 'BCA-OPS-0004',
                        'description' => 'Biaya operasional cabang',
                        'debit' => 2250000,
                        'credit' => 0,
                        'balance' => 40250000,
                        'status' => 'unmatched',
                        'matched_transaction_id' => null,
                    ],
                ],
            ],

            [
                'bank_name' => 'Bank Mandiri',
                'bank_code' => '008',
                'account_name' => 'PT Almara Putra Valasindo',
                'account_number' => '9876543210',
                'opening_balance' => 50000000,
                'mutations' => [
                    [
                        'days' => 4,
                        'reference' => 'MDR-PAY-0001',
                        'description' => 'Pembayaran supplier',
                        'debit' => 12000000,
                        'credit' => 0,
                        'balance' => 38000000,
                        'status' => 'matched',
                        'matched_transaction_id' => '01DEMOMDRPAY00000000000001',
                    ],
                    [
                        'days' => 3,
                        'reference' => 'MDR-TRF-0002',
                        'description' => 'Transfer masuk dari pelanggan',
                        'debit' => 0,
                        'credit' => 8500000,
                        'balance' => 46500000,
                        'status' => 'matched',
                        'matched_transaction_id' => '01DEMOMDRTRF00000000000001',
                    ],
                    [
                        'days' => 2,
                        'reference' => 'MDR-OPS-0003',
                        'description' => 'Pembayaran biaya operasional',
                        'debit' => 3750000,
                        'credit' => 0,
                        'balance' => 42750000,
                        'status' => 'unmatched',
                        'matched_transaction_id' => null,
                    ],
                    [
                        'days' => 1,
                        'reference' => 'MDR-CAP-0004',
                        'description' => 'Setoran modal',
                        'debit' => 0,
                        'credit' => 20000000,
                        'balance' => 62750000,
                        'status' => 'manual',
                        'matched_transaction_id' => null,
                    ],
                ],
            ],

            [
                'bank_name' => 'BNI',
                'bank_code' => '009',
                'account_name' => 'PT Almara Putra Valasindo',
                'account_number' => '1122334455',
                'opening_balance' => 10000000,
                'mutations' => [
                    [
                        'days' => 4,
                        'reference' => 'BNI-TRF-0001',
                        'description' => 'Transfer masuk pelanggan',
                        'debit' => 0,
                        'credit' => 5000000,
                        'balance' => 15000000,
                        'status' => 'matched',
                        'matched_transaction_id' => '01DEMOBNITRF00000000000001',
                    ],
                    [
                        'days' => 3,
                        'reference' => 'BNI-OPS-0002',
                        'description' => 'Pembayaran operasional',
                        'debit' => 1500000,
                        'credit' => 0,
                        'balance' => 13500000,
                        'status' => 'unmatched',
                        'matched_transaction_id' => null,
                    ],
                    [
                        'days' => 2,
                        'reference' => 'BNI-CASH-0003',
                        'description' => 'Setoran kas',
                        'debit' => 0,
                        'credit' => 12000000,
                        'balance' => 25500000,
                        'status' => 'manual',
                        'matched_transaction_id' => null,
                    ],
                    [
                        'days' => 1,
                        'reference' => 'BNI-PAY-0004',
                        'description' => 'Pembayaran supplier',
                        'debit' => 4000000,
                        'credit' => 0,
                        'balance' => 21500000,
                        'status' => 'unmatched',
                        'matched_transaction_id' => null,
                    ],
                ],
            ],
        ];

        foreach ($banks as $bank) {

            $account = BankAccount::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'account_number' => $bank['account_number'],
                ],
                [
                    'branch_id' => $branch->id,
                    'bank_name' => $bank['bank_name'],
                    'bank_code' => $bank['bank_code'],
                    'account_name' => $bank['account_name'],
                    'currency_id' => $idr->id,
                    'opening_balance' => $bank['opening_balance'],
                    'is_active' => true,
                    'notes' => 'DATA DEMO - Bank Mutation',
                ]
            );

            foreach ($bank['mutations'] as $index => $mutation) {

                BankMutation::updateOrCreate(
                    [
                        'tenant_id' => $tenant->id,
                        'bank_account_id' => $account->id,
                        'external_id' => 'DEMO-' . $bank['bank_code'] . '-' . ($index + 1),
                    ],
                    [
                        'branch_id' => $branch->id,
                        'transaction_date' => Carbon::now()
                            ->subDays($mutation['days'])
                            ->setTime(10 + $index, 15, 0),

                        'value_date' => Carbon::now()
                            ->subDays($mutation['days'])
                            ->toDateString(),

                        'reference' => $mutation['reference'],
                        'description' => $mutation['description'],

                        'debit' => $mutation['debit'],
                        'credit' => $mutation['credit'],
                        'balance' => $mutation['balance'],

                        'external_id' =>
                            'DEMO-' .
                            $bank['bank_code'] .
                            '-' .
                            ($index + 1),

                        'source' => 'manual',

                        'reconciliation_status' => $mutation['status'],

                        'matched_transaction_id' =>
                            $mutation['matched_transaction_id'],

                        'notes' => 'Data demo untuk pengujian aplikasi.',
                    ]
                );
            }

            $this->command?->info(
                '✓ ' .
                $bank['bank_name'] .
                ' berhasil dibuat: ' .
                count($bank['mutations']) .
                ' mutasi.'
            );
        }

        $this->command?->newLine();
        $this->command?->info('==========================================');
        $this->command?->info('BANK MUTATION DEMO SELESAI');
        $this->command?->info('==========================================');
        $this->command?->info('BCA     : saldo akhir Rp40.250.000');
        $this->command?->info('Mandiri : saldo akhir Rp62.750.000');
        $this->command?->info('BNI     : saldo akhir Rp21.500.000');
    }
}
