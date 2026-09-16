<?php

use App\Models\RateSnapshot;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenantId = '01m27xwzveq1rjdw7axwzybef4';

echo "=== AUDIT RATE SGD ===\n";

$rates = RateSnapshot::query()
    ->where('tenant_id', $tenantId)
    ->where('currency_id', 2)
    ->orderBy('id')
    ->get([
        'id',
        'currency_id',
        'currency_variant_id',
        'effective_at',
        'buy_rate',
        'sell_rate',
        'is_active',
    ]);

if ($rates->isEmpty()) {
    echo "Tidak ada RateSnapshot SGD.\n";
    exit;
}

foreach ($rates as $rate) {
    echo "\n";
    echo "ID             : {$rate->id}\n";
    echo "Currency ID    : {$rate->currency_id}\n";
    echo "Variant ID     : " . ($rate->currency_variant_id ?? 'NULL') . "\n";
    echo "Effective At   : {$rate->effective_at}\n";
    echo "Buy Rate       : {$rate->buy_rate}\n";
    echo "Sell Rate      : {$rate->sell_rate}\n";
    echo "Active         : " . ($rate->is_active ? 'YES' : 'NO') . "\n";
}

echo "\n=== SELESAI ===\n";