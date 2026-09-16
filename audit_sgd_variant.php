<?php

use App\Models\CurrencyVariant;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== AUDIT VARIANT SGD ===\n";

$variants = CurrencyVariant::query()
    ->where('currency_id', 2)
    ->orderBy('id')
    ->get([
        'id',
        'currency_id',
        'code',
        'name',
        'is_default',
        'is_active',
    ]);

foreach ($variants as $variant) {
    echo "\n";
    echo "ID          : {$variant->id}\n";
    echo "Currency ID : {$variant->currency_id}\n";
    echo "Code        : {$variant->code}\n";
    echo "Name        : {$variant->name}\n";
    echo "Default     : " . ($variant->is_default ? 'YES' : 'NO') . "\n";
    echo "Active      : " . ($variant->is_active ? 'YES' : 'NO') . "\n";
}

echo "\n=== SELESAI ===\n";