<?php

use App\Models\RateSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenantId = '01m27xwzveq1rjdw7axwzybef4';

echo "=== CREATE SGD RATE SNAPSHOT TEST ===\n";

$existing = RateSnapshot::query()
    ->where('tenant_id', $tenantId)
    ->where('currency_id', 2)
    ->where('currency_variant_id', 2)
    ->whereNull('currency_denomination_id')
    ->where('is_active', true)
    ->first();

if ($existing) {
    echo "Snapshot SGD Variant #2 sudah ada.\n";
    echo "ID       : {$existing->id}\n";
    echo "Buy Rate : {$existing->buy_rate}\n";
    echo "Sell Rate: {$existing->sell_rate}\n";
    exit;
}

$now = Carbon::now();

$rate = RateSnapshot::create([
    'tenant_id' => $tenantId,
    'currency_id' => 2,
    'rate_source_id' => 1,

    'effective_at' => $now,

    'reference_buy_rate' => 13850,
    'reference_sell_rate' => 13850,

    'buy_spread' => -50,
    'sell_spread' => 150,

    'buy_rate' => 13800,
    'sell_rate' => 14000,

    'source_url' => 'https://www.smartdeal.co.id/',
    'notes' => 'TEST - Rate SGD/STD Almara',

    'is_active' => true,

    'created_by' => null,

    'currency_variant_id' => 2,
    'currency_denomination_id' => null,
]);

echo "\nSnapshot berhasil dibuat.\n";
echo "ID             : {$rate->id}\n";
echo "Currency ID    : {$rate->currency_id}\n";
echo "Variant ID     : {$rate->currency_variant_id}\n";
echo "Buy Rate       : {$rate->buy_rate}\n";
echo "Sell Rate      : {$rate->sell_rate}\n";
echo "Effective At   : {$rate->effective_at}\n";
echo "Active         : " . ($rate->is_active ? 'YES' : 'NO') . "\n";

echo "\n=== SELESAI ===\n";