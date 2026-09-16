<?php

$tenant = \App\Models\Tenant::firstOrFail();

$branch = \App\Models\Branch::query()
    ->where('tenant_id', $tenant->id)
    ->firstOrFail();

$currency = \App\Models\Currency::query()
    ->where('code', 'USD')
    ->firstOrFail();

$account = (new \App\Services\BankAccountService)->create(
    $tenant->id,
    $branch->id,
    [
        'bank_name' => 'TEST BANK',
        'account_name' => 'TEST ACCOUNT',
        'account_number' => 'TEST-' . time(),
        'currency_id' => $currency->id,
        'opening_balance' => '10000.00',
        'is_active' => true,
    ]
);

$service = new \App\Services\BankMutationService;

$m1 = $service->create(
    $tenant->id,
    $branch->id,
    [
        'bank_account_id' => $account->id,
        'transaction_date' => now(),
        'description' => 'TEST CREDIT',
        'credit' => '2500.00',
        'debit' => '0',
        'source' => 'manual',
    ]
);

$m2 = $service->create(
    $tenant->id,
    $branch->id,
    [
        'bank_account_id' => $account->id,
        'transaction_date' => now()->addSecond(),
        'description' => 'TEST DEBIT',
        'credit' => '0',
        'debit' => '1000.00',
        'source' => 'manual',
    ]
);

$balance = (new \App\Services\BankAccountService)
    ->calculateBalance(
        $tenant->id,
        $branch->id,
        $account->id
    );

dump([
    'opening_balance' => $account->opening_balance,
    'credit' => $m1->credit,
    'debit' => $m2->debit,
    'calculated_balance' => $balance,
]);

$m2->delete();
$m1->delete();
$account->delete();

dump('TEST CLEANUP OK');
