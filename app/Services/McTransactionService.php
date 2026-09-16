<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Currency;
use App\Models\CurrencyDenomination;
use App\Models\CurrencyVariant;
use App\Models\Customer;
use App\Models\McTransaction;
use App\Models\McTransactionItem;
use App\Models\RateSnapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class McTransactionService
{
    public function __construct(
        protected McTransactionSequenceService $sequenceService,
        protected McTransactionSettlementBuilderService $settlementBuilder,
        protected McTransactionThresholdService $thresholdService,
    ) {
    }

    /**
     * Create one complete MC transaction.
     *
     * Flow:
     * Transaction
     *   -> Items
     *   -> Compliance Threshold
     *   -> Settlements
     */
    public function create(array $data): McTransaction
    {
        return DB::transaction(function () use ($data) {
            $tenantId = $this->requiredString($data, 'tenant_id');
            $branchId = $this->requiredString($data, 'branch_id');
            $customerId = $this->requiredString($data, 'customer_id');

            $transactionDate = $this->validateTransactionDate(
                $data['transaction_date'] ?? null
            );

            $this->validateBranchBelongsToTenant(
                $branchId,
                $tenantId
            );

            $this->validateCustomerBelongsToBranch(
                $customerId,
                $tenantId,
                $branchId
            );

            $items = $data['items'] ?? [];

            if (!is_array($items) || count($items) === 0) {
                throw new RuntimeException(
                    'Transaksi minimal harus memiliki satu item.'
                );
            }

            $status = $data['status'] ?? 'pending_payment';

            $allowedStatuses = [
                'draft',
                'pending_payment',
                'paid',
                'completed',
                'cancelled',
                'rejected',
            ];

            if (!in_array($status, $allowedStatuses, true)) {
                throw new RuntimeException(
                    'Status transaksi tidak valid.'
                );
            }

            /*
             * Transaksi baru tidak boleh langsung dibuat
             * paid/completed.
             */
            if (in_array($status, ['paid', 'completed'], true)) {
                throw new RuntimeException(
                    'Transaksi baru tidak boleh langsung berstatus paid/completed.'
                );
            }

            /*
             * Invoice number dibuat hanya saat transaksi
             * benar-benar berhasil dibuat.
             */
            $transactionNo = $this->sequenceService->next(
                $tenantId,
                $branchId,
                $transactionDate
            );

            $transaction = McTransaction::query()->create([
                'id' => (string) Str::ulid(),

                'tenant_id' => $tenantId,
                'branch_id' => $branchId,

                'transaction_no' => $transactionNo,
                'customer_id' => $customerId,
                'transaction_date' => $transactionDate,

                'status' => $status,
                'settlement_status' => 'pending',

                /*
                 * Source of funds.
                 *
                 * Sesuai schema:
                 * fund_source_type
                 * fund_source_detail
                 */
                'fund_source_type' =>
                    $data['fund_source_type']
                    ?? $data['source_of_funds']
                    ?? null,

                'fund_source_detail' =>
                    $data['fund_source_detail']
                    ?? null,

                /*
                 * Transaction purpose.
                 *
                 * Sesuai schema:
                 * transaction_purpose_type
                 * transaction_purpose_detail
                 */
                'transaction_purpose_type' =>
                    $data['transaction_purpose_type']
                    ?? $data['purpose']
                    ?? null,

                'transaction_purpose_detail' =>
                    $data['transaction_purpose_detail']
                    ?? null,

                /*
                 * Pickup snapshot.
                 */
                'pickup_same_as_customer' =>
                    array_key_exists(
                        'pickup_same_as_customer',
                        $data
                    )
                        ? (bool) $data['pickup_same_as_customer']
                        : true,

                'pickup_party_type' =>
                    $data['pickup_party_type'] ?? null,

                'pickup_party_id' =>
                    $data['pickup_party_id'] ?? null,

                'pickup_name' =>
                    $data['pickup_name'] ?? null,

                'pickup_phone' =>
                    $data['pickup_phone'] ?? null,

                'pickup_identity_type' =>
                    $data['pickup_identity_type'] ?? null,

                'pickup_identity_number' =>
                    $data['pickup_identity_number'] ?? null,

                'pickup_relationship' =>
                    $data['pickup_relationship'] ?? null,

                'pickup_status' =>
                    $data['pickup_status'] ?? 'not_required',

                'pickup_at' =>
                    $data['pickup_at'] ?? null,

                'handed_over_by' =>
                    $data['handed_over_by'] ?? null,

                /*
                 * WhatsApp receipt.
                 */
                'send_wa_receipt' =>
                    array_key_exists(
                        'send_wa_receipt',
                        $data
                    )
                        ? (bool) $data['send_wa_receipt']
                        : false,

                'wa_template_id' =>
                    $data['wa_template_id'] ?? null,

                'wa_template_version' =>
                    $data['wa_template_version'] ?? null,

                'wa_status' =>
                    $data['wa_status'] ?? 'pending',

                'wa_sent_at' =>
                    $data['wa_sent_at'] ?? null,

                'wa_message_id' =>
                    $data['wa_message_id'] ?? null,

                'wa_error' =>
                    $data['wa_error'] ?? null,

                /*
                 * Audit.
                 */
                'created_by' =>
                    $data['created_by'] ?? null,

                'updated_by' =>
                    $data['updated_by'] ?? null,

                'notes' =>
                    $data['notes'] ?? null,
            ]);

            /*
             * 1. Create transaction items.
             */
            foreach ($items as $item) {
                $this->createItem(
                    $transaction,
                    $item
                );
            }

            /*
             * 2. Calculate compliance threshold.
             */
            $this->thresholdService->calculateTransaction(
                $transaction
            );

            /*
             * 3. Generate settlement otomatis.
             */
            $this->settlementBuilder->build(
                $transaction
            );

            return $transaction->fresh([
                'items',
                'settlements',
            ]);
        });
    }

    /**
     * Create transaction item.
     */
    public function createItem(
        McTransaction $transaction,
        array $data
    ): McTransactionItem {
        $currencyId = (int) (
            $data['currency_id'] ?? 0
        );

        $variantId = (int) (
            $data['currency_variant_id'] ?? 0
        );

        $denominationId =
            array_key_exists(
                'currency_denomination_id',
                $data
            )
                && $data['currency_denomination_id'] !== null
                && $data['currency_denomination_id'] !== ''
                ? (int) $data['currency_denomination_id']
                : null;

        $direction = strtolower(
            trim(
                (string) (
                    $data['direction'] ?? ''
                )
            )
        );

        $quantity = (float) (
            $data['quantity'] ?? 0
        );

        $rate = (float) (
            $data['rate'] ?? 0
        );

        $rateSnapshotId =
            $data['rate_snapshot_id']
            ?? null;

        if ($currencyId <= 0) {
            throw new RuntimeException(
                'Currency item wajib dipilih.'
            );
        }

        if ($variantId <= 0) {
            throw new RuntimeException(
                'Series/Variant item wajib dipilih.'
            );
        }

        if (!in_array($direction, ['buy', 'sell'], true)) {
            throw new RuntimeException(
                'Direction item harus buy atau sell.'
            );
        }

        if ($quantity <= 0) {
            throw new RuntimeException(
                'Quantity item harus lebih besar dari 0.'
            );
        }

        if ($rate <= 0) {
            throw new RuntimeException(
                'Rate item harus lebih besar dari 0.'
            );
        }

        if (
            $rateSnapshotId === null
            || $rateSnapshotId === ''
        ) {
            throw new RuntimeException(
                'Rate snapshot wajib dipilih.'
            );
        }

        /*
         * Currency.
         */
        $currency = Currency::query()
            ->find($currencyId);

        if (!$currency) {
            throw new RuntimeException(
                'Currency tidak ditemukan.'
            );
        }

        /*
         * Variant harus milik currency.
         */
        $variant = CurrencyVariant::query()
            ->whereKey($variantId)
            ->where('currency_id', $currencyId)
            ->first();

        if (!$variant) {
            throw new RuntimeException(
                'Series/Variant tidak sesuai dengan currency.'
            );
        }

        /*
         * Rate snapshot:
         * - tenant sama
         * - currency sama
         * - variant sama
         * - aktif
         */
        $rateSnapshot = RateSnapshot::query()
            ->whereKey($rateSnapshotId)
            ->where(
                'tenant_id',
                $transaction->tenant_id
            )
            ->where(
                'currency_id',
                $currencyId
            )
            ->where(
                'currency_variant_id',
                $variantId
            )
            ->where(
                'is_active',
                true
            )
            ->first();

        if (!$rateSnapshot) {
            throw new RuntimeException(
                'Rate snapshot tidak ditemukan, tidak aktif, atau tidak sesuai currency/variant.'
            );
        }

        /*
         * Denomination optional.
         */
        $denomination = null;

        if ($denominationId !== null) {
            $denomination = CurrencyDenomination::query()
                ->whereKey($denominationId)
                ->where(
                    'currency_variant_id',
                    $variantId
                )
                ->first();

            if (!$denomination) {
                throw new RuntimeException(
                    'Denomination tidak sesuai dengan Series/Variant.'
                );
            }
        }

        /*
         * Jika snapshot menggunakan denomination,
         * item wajib menggunakan denomination tersebut.
         */
        if (
            $rateSnapshot->currency_denomination_id !== null
        ) {
            if (
                $denominationId === null
                || (int) $rateSnapshot->currency_denomination_id
                    !== $denominationId
            ) {
                throw new RuntimeException(
                    'Denomination item tidak sesuai dengan rate snapshot.'
                );
            }
        }

        /*
         * Rate harus sama dengan snapshot.
         *
         * BUY  -> buy_rate
         * SELL -> sell_rate
         */
        $expectedRate = $direction === 'buy'
            ? (float) $rateSnapshot->buy_rate
            : (float) $rateSnapshot->sell_rate;

        if (
            round($rate, 8)
            !== round($expectedRate, 8)
        ) {
            throw new RuntimeException(
                'Rate transaksi harus sama dengan rate pada rate snapshot.'
            );
        }

        /*
         * Subtotal dihitung server-side.
         */
        $subtotal = round(
            $quantity * $rate,
            2
        );

        /*
         * Threshold fields akan diisi oleh
         * McTransactionThresholdService.
         */
        return McTransactionItem::query()->create([
            'id' => (string) Str::ulid(),

            'transaction_id' =>
                $transaction->id,

            'currency_id' =>
                $currencyId,

            'currency_variant_id' =>
                $variantId,

            'currency_denomination_id' =>
                $denominationId,

            'direction' =>
                $direction,

            'quantity' =>
                $quantity,

            'rate' =>
                $rate,

            'rate_snapshot_id' =>
                $rateSnapshot->id,

            'subtotal' =>
                $subtotal,

            'compliance_threshold_rule_id' =>
                null,

            'threshold_equivalent_amount' =>
                null,

            'threshold_currency_id' =>
                null,

            'usd_equivalent_amount' =>
                null,

            'threshold_rate' =>
                null,

            'threshold_rate_snapshot_id' =>
                null,

            'notes' =>
                $data['notes'] ?? null,
        ]);
    }

    /**
     * Validate transaction date.
     */
    protected function validateTransactionDate(
        mixed $value
    ): Carbon {
        if ($value === null || $value === '') {
            return now();
        }

        try {
            return $value instanceof Carbon
                ? $value
                : Carbon::parse($value);
        } catch (Throwable $e) {
            throw new RuntimeException(
                'Tanggal transaksi tidak valid.'
            );
        }
    }

    /**
     * Validate branch belongs to tenant.
     */
    protected function validateBranchBelongsToTenant(
        string $branchId,
        string $tenantId
    ): Branch {
        $branch = Branch::query()
            ->whereKey($branchId)
            ->where(
                'tenant_id',
                $tenantId
            )
            ->first();

        if (!$branch) {
            throw new RuntimeException(
                'Branch tidak ditemukan atau bukan milik tenant.'
            );
        }

        return $branch;
    }

    /**
     * Validate customer belongs to tenant + branch.
     */
    protected function validateCustomerBelongsToBranch(
        string $customerId,
        string $tenantId,
        string $branchId
    ): Customer {
        $customer = Customer::query()
            ->whereKey($customerId)
            ->where(
                'tenant_id',
                $tenantId
            )
            ->where(
                'branch_id',
                $branchId
            )
            ->first();

        if (!$customer) {
            throw new RuntimeException(
                'Customer tidak ditemukan atau bukan milik branch transaksi.'
            );
        }

        if (
            isset($customer->status)
            && $customer->status !== 'active'
        ) {
            throw new RuntimeException(
                'Customer tidak aktif.'
            );
        }

        return $customer;
    }

    /**
     * Required string helper.
     */
    protected function requiredString(
        array $data,
        string $key
    ): string {
        $value = $data[$key] ?? null;

        if (
            $value === null
            || trim((string) $value) === ''
        ) {
            throw new RuntimeException(
                "{$key} wajib diisi."
            );
        }

        return trim((string) $value);
    }

    /**
     * Find transaction by transaction number.
     */
    public function findByTransactionNo(
        string $tenantId,
        string $transactionNo
    ): ?McTransaction {
        return McTransaction::query()
            ->where(
                'tenant_id',
                $tenantId
            )
            ->where(
                'transaction_no',
                $transactionNo
            )
            ->with([
                'items',
                'settlements',
                'payments',
            ])
            ->first();
    }

    /**
     * Find transaction by ID within tenant.
     */
    public function find(
        string $tenantId,
        string $transactionId
    ): ?McTransaction {
        return McTransaction::query()
            ->where(
                'tenant_id',
                $tenantId
            )
            ->whereKey($transactionId)
            ->with([
                'items',
                'settlements',
                'payments',
            ])
            ->first();
    }

    /**
     * Get transaction items.
     */
    public function items(
        McTransaction $transaction
    ): Collection {
        return $transaction->items()
            ->with([
                'currency',
                'currencyVariant',
                'currencyDenomination',
                'rateSnapshot',
                'complianceThresholdRule',
                'thresholdRateSnapshot',
            ])
            ->get();
    }

    /**
     * Get settlements.
     */
    public function settlements(
        McTransaction $transaction
    ): Collection {
        return $transaction->settlements()
            ->with([
                'currency',
                'payments',
            ])
            ->get();
    }

    /**
     * Get payments.
     */
    public function payments(
        McTransaction $transaction
    ): Collection {
        return $transaction->payments()
            ->with([
                'currency',
                'bankAccount',
                'bankMutation',
            ])
            ->get();
    }

    /**
     * Get fulfilled booking.
     */
    public function fulfilledBooking(
        McTransaction $transaction
    ): mixed {
        return $transaction->fulfilledBooking();
    }
}