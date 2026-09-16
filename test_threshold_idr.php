<?php

use App\Models\ComplianceThresholdRule;
use App\Models\Currency;
use App\Models\CurrencyVariant;
use App\Models\RateSnapshot;
use App\Models\Tenant;
use App\Models\Branch;
use App\Models\Customer;
use App\Services\McTransactionService;
use App\Services\McTransactionThresholdService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$tenant = Tenant::query()
    ->where('code', 'ALMARA')
    ->firstOrFail();

$branch = Branch::query()
    ->where('tenant_id', $tenant->id)
    ->where('code', 'PUSAT')
    ->firstOrFail();

$customer = Customer::query()
    ->where('tenant_id', $tenant->id)
    ->where('branch_id', $branch->id)
    ->firstOrFail();

$sgd = Currency::query()
    ->where('code', 'SGD')
    ->firstOrFail();

$variant = CurrencyVariant::query()
    ->where('currency_id', $sgd->id)
    ->where('code', 'STD')
    ->firstOrFail();

$sgdSnapshot = RateSnapshot::query()
    ->where('tenant_id', $tenant->id)
    ->where('currency_id', $sgd->id)
    ->where('currency_variant_id', $variant->id)
    ->where('is_active', true)
    ->orderByDesc('effective_at')
    ->orderByDesc('id')
    ->firstOrFail();

$transactionDate = Carbon::parse($sgdSnapshot->effective_at)->addMinute();

try {
    DB::transaction(function () use (
        $tenant,
        $branch,
        $customer,
        $sgd,
        $variant,
        $sgdSnapshot,
        $transactionDate
    ) {
        $rule = ComplianceThresholdRule::create([
            'id' => (string) Str::ulid(),
            'tenant_id' => $tenant->id,
            'name' => 'TEST Threshold IDR',
            'code' => 'TEST-IDR-001',
            'basis' => 'idr',
            'limit_amount' => 20000000,
            'period' => 'monthly',
            'effective_from' => $transactionDate->copy()->subMinute(),
            'effective_until' => null,
            'is_active' => true,
            'regulation_reference' => 'TEST ONLY',
            'notes' => 'Temporary rollback test',
        ]);

        $transaction = app(McTransactionService::class)->create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'transaction_date' => $transactionDate,
            'status' => 'pending_payment',
            'items' => [
                [
                    'currency_id' => $sgd->id,
                    'currency_variant_id' => $variant->id,
                    'currency_denomination_id' => null,
                    'direction' => 'buy',
                    'quantity' => 100,
                    'rate' => $sgdSnapshot->buy_rate,
                    'rate_snapshot_id' => $sgdSnapshot->id,
                    'notes' => 'TEST IDR BASIS',
                ],
            ],
        ]);

        $item = $transaction->items->first();

        $summary = app(McTransactionThresholdService::class)
            ->getSummary($transaction);

        echo PHP_EOL;
        echo "=== IDR BASIS THRESHOLD TEST ===" . PHP_EOL;
        echo "Transaction : {$transaction->transaction_no}" . PHP_EOL;
        echo "Rule        : {$rule->code}" . PHP_EOL;
        echo "Basis       : {$rule->basis}" . PHP_EOL;
        echo "Limit       : {$rule->limit_amount}" . PHP_EOL;

        echo PHP_EOL . "--- ITEM ---" . PHP_EOL;
        echo "Currency       : {$item->currency_id}" . PHP_EOL;
        echo "Direction      : {$item->direction}" . PHP_EOL;
        echo "Quantity       : {$item->quantity}" . PHP_EOL;
        echo "Rate           : {$item->rate}" . PHP_EOL;
        echo "Subtotal       : {$item->subtotal}" . PHP_EOL;
        echo "Rate Snapshot  : #{$item->rate_snapshot_id}" . PHP_EOL;

        echo PHP_EOL . "--- THRESHOLD ---" . PHP_EOL;
        echo "Rule ID        : {$item->compliance_threshold_rule_id}" . PHP_EOL;
        echo "Equivalent     : {$item->threshold_equivalent_amount}" . PHP_EOL;
        echo "Threshold Curr : {$item->threshold_currency_id}" . PHP_EOL;
        echo "USD Equivalent : {$item->usd_equivalent_amount}" . PHP_EOL;
        echo "Threshold Rate : {$item->threshold_rate}" . PHP_EOL;
        echo "Threshold Snap : "
            . ($item->threshold_rate_snapshot_id ?? 'NULL')
            . PHP_EOL;

        $passRule =
            $item->compliance_threshold_rule_id === $rule->id
            && $item->complianceThresholdRule?->basis === 'idr';

        $passEquivalent =
            abs(
                (float) $item->threshold_equivalent_amount
                - (float) $item->subtotal
            ) < 0.01;

        $passCurrency =
            (int) $item->threshold_currency_id === 18;

        $passRate =
            abs((float) $item->threshold_rate - 1) < 0.00000001;

        $passSnapshot =
            $item->threshold_rate_snapshot_id === null;

        $passLimit =
            abs((float) $summary['limit_amount'] - 20000000) < 0.01;

        echo PHP_EOL . "--- VALIDATION ---" . PHP_EOL;
        echo "Rule basis             : "
            . ($passRule ? 'PASS' : 'FAIL') . PHP_EOL;

        echo "Equivalent = subtotal  : "
            . ($passEquivalent ? 'PASS' : 'FAIL') . PHP_EOL;

        echo "IDR currency           : "
            . ($passCurrency ? 'PASS' : 'FAIL') . PHP_EOL;

        echo "Threshold rate = 1     : "
            . ($passRate ? 'PASS' : 'FAIL') . PHP_EOL;

        echo "Threshold snapshot NULL: "
            . ($passSnapshot ? 'PASS' : 'FAIL') . PHP_EOL;

        echo "Summary limit          : "
            . ($passLimit ? 'PASS' : 'FAIL') . PHP_EOL;

        echo "Summary usage          : {$summary['usage_amount']}" . PHP_EOL;

        if (
            !$passRule ||
            !$passEquivalent ||
            !$passCurrency ||
            !$passRate ||
            !$passSnapshot ||
            !$passLimit
        ) {
            throw new RuntimeException('IDR BASIS TEST FAILED');
        }

        echo PHP_EOL;
        echo "ALL VALIDATION         : PASS" . PHP_EOL;
        echo "DATABASE ROLLBACK      : PASS" . PHP_EOL;

        throw new RuntimeException('__ROLLBACK_TEST__');
    });
} catch (RuntimeException $e) {
    if ($e->getMessage() !== '__ROLLBACK_TEST__') {
        throw $e;
    }
}

echo PHP_EOL;
echo "=== TEST THRESHOLD IDR PASS ===" . PHP_EOL;


