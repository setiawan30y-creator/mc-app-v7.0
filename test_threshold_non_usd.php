<?php

use App\Models\Branch;
use App\Models\ComplianceThresholdRule;
use App\Models\Currency;
use App\Models\CurrencyVariant;
use App\Models\Customer;
use App\Models\RateSnapshot;
use App\Models\Tenant;
use App\Services\McTransactionService;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenantId = '01m27xwzveq1rjdw7axwzybef4';
$branchId = '01m27ytb2k27ks2dpmrp71tdj6';
$customerId = '01m29ysap9b4ppcxcavr3k51gg';

echo "=== NON-USD THRESHOLD TEST ===\n";

$tenant = Tenant::findOrFail($tenantId);
$branch = Branch::findOrFail($branchId);
$customer = Customer::findOrFail($customerId);

$currency = Currency::where('code', 'SGD')->firstOrFail();

$variant = CurrencyVariant::query()
    ->where('currency_id', $currency->id)
    ->where('is_active', true)
    ->orderByDesc('is_default')
    ->orderBy('id')
    ->firstOrFail();

$rule = ComplianceThresholdRule::query()
    ->where('tenant_id', $tenantId)
    ->where('code', 'RULE-001')
    ->firstOrFail();

$usdSnapshot = RateSnapshot::query()
    ->where('tenant_id', $tenantId)
    ->where('currency_id', 1)
    ->where('currency_variant_id', 1)
    ->whereNull('currency_denomination_id')
    ->where('is_active', true)
    ->orderByDesc('effective_at')
    ->orderByDesc('id')
    ->firstOrFail();

$sgdSnapshot = RateSnapshot::query()
    ->where('tenant_id', $tenantId)
    ->where('currency_id', $currency->id)
    ->where('currency_variant_id', $variant->id)
    ->whereNull('currency_denomination_id')
    ->where('is_active', true)
    ->orderByDesc('effective_at')
    ->orderByDesc('id')
    ->firstOrFail();

$transactionDate = $sgdSnapshot->effective_at->copy()->addMinute();

echo "Tenant       : {$tenant->code}\n";
echo "Branch       : {$branch->code}\n";
echo "Customer     : {$customer->customer_number}\n";
echo "Currency     : {$currency->code}\n";
echo "Variant      : {$variant->id}\n";
echo "USD Snapshot : #{$usdSnapshot->id}\n";
echo "USD Buy Rate : {$usdSnapshot->buy_rate}\n";
echo "SGD Snapshot : #{$sgdSnapshot->id}\n";
echo "SGD Buy Rate : {$sgdSnapshot->buy_rate}\n";
echo "Transaction  : {$transactionDate}\n";
echo "Rule         : {$rule->code}\n";
echo "Basis        : {$rule->basis}\n";
echo "Limit        : {$rule->limit_amount}\n";

DB::beginTransaction();

try {
    $service = app(McTransactionService::class);

    $transaction = $service->create([
        'tenant_id' => $tenantId,
        'branch_id' => $branchId,
        'customer_id' => $customerId,

        'transaction_date' => $transactionDate,

        'status' => 'pending_payment',

        'fund_source_type' => 'salary',
        'fund_source_detail' => 'TEST NON USD',

        'transaction_purpose_type' => 'travel',
        'transaction_purpose_detail' => 'TEST NON USD',

        'pickup_same_as_customer' => true,

        'send_wa_receipt' => false,

        'notes' => 'TEST NON USD THRESHOLD',

        'items' => [
            [
                'currency_id' => $currency->id,
                'currency_variant_id' => $variant->id,
                'currency_denomination_id' => null,
                'direction' => 'buy',
                'quantity' => 100,
                'rate' => $sgdSnapshot->buy_rate,
                'rate_snapshot_id' => $sgdSnapshot->id,
                'notes' => 'TEST SGD 100',
            ],
        ],
    ]);

    $item = $transaction->items()
        ->with([
            'currency',
            'currencyVariant',
            'rateSnapshot',
            'complianceThresholdRule',
            'thresholdRateSnapshot',
        ])
        ->firstOrFail();

    $expectedEquivalent =
        (100 * (float) $sgdSnapshot->buy_rate)
        / (float) $usdSnapshot->buy_rate;

    echo "\n--- TRANSACTION ---\n";
    echo "Transaction No : {$transaction->transaction_no}\n";
    echo "Status         : {$transaction->status}\n";
    echo "Settlement     : {$transaction->settlement_status}\n";

    echo "\n--- ITEM ---\n";
    echo "Currency       : {$item->currency->code}\n";
    echo "Direction      : {$item->direction}\n";
    echo "Quantity       : {$item->quantity}\n";
    echo "Rate           : {$item->rate}\n";
    echo "Subtotal       : {$item->subtotal}\n";
    echo "Rate Snapshot  : #{$item->rate_snapshot_id}\n";

    echo "\n--- THRESHOLD ---\n";
    echo "Rule ID        : {$item->compliance_threshold_rule_id}\n";
    echo "Rule Code      : {$item->complianceThresholdRule->code}\n";
    echo "Rule Basis     : {$item->complianceThresholdRule->basis}\n";
    echo "Rule Limit     : {$item->complianceThresholdRule->limit_amount}\n";
    echo "Equivalent     : {$item->threshold_equivalent_amount}\n";
    echo "Threshold Curr : {$item->threshold_currency_id}\n";
    echo "USD Equivalent : {$item->usd_equivalent_amount}\n";
    echo "Threshold Rate : {$item->threshold_rate}\n";
    echo "Threshold Snap : #{$item->threshold_rate_snapshot_id}\n";

    echo "\n--- VALIDATION ---\n";

    if ($item->compliance_threshold_rule_id !== $rule->id) {
        throw new RuntimeException('Rule ID tidak sesuai.');
    }

    if ((int) $item->threshold_currency_id !== 1) {
        throw new RuntimeException('Threshold currency bukan USD.');
    }

    if ((int) $item->threshold_rate_snapshot_id !== (int) $usdSnapshot->id) {
        throw new RuntimeException('Threshold snapshot bukan snapshot USD.');
    }

    if (abs(
        (float) $item->threshold_equivalent_amount
        - $expectedEquivalent
    ) > 0.01) {
        throw new RuntimeException(
            "Equivalent USD tidak sesuai. Expected {$expectedEquivalent}, got {$item->threshold_equivalent_amount}"
        );
    }

    if (abs(
        (float) $item->usd_equivalent_amount
        - $expectedEquivalent
    ) > 0.01) {
        throw new RuntimeException(
            "USD equivalent tidak sesuai. Expected {$expectedEquivalent}, got {$item->usd_equivalent_amount}"
        );
    }

    if (abs(
        (float) $item->threshold_rate
        - (float) $usdSnapshot->buy_rate
    ) > 0.00000001) {
        throw new RuntimeException(
            "Threshold rate tidak sesuai. Expected {$usdSnapshot->buy_rate}, got {$item->threshold_rate}"
        );
    }

    echo "Rule validation        : PASS\n";
    echo "Currency validation    : PASS\n";
    echo "USD snapshot           : PASS\n";
    echo "Equivalent calculation : PASS\n";
    echo "Threshold rate         : PASS\n";

    DB::rollBack();

    echo "\nDATABASE ROLLBACK      : PASS\n";
    echo "=== TEST NON-USD PASS ===\n";

} catch (\Throwable $e) {
    DB::rollBack();

    echo "\nTEST FAILED\n";
    echo $e->getMessage() . "\n";

    exit(1);
}