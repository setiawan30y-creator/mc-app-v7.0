<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankMutation;
use App\Models\CashMovement;
use App\Models\Currency;
use App\Models\McTransactionPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class LedgerPostingService
{
    public const MOVEMENT_TRANSACTION_PAYMENT = 'transaction_payment';
    public const MOVEMENT_EXPENSE = 'expense';
    public const SOURCE_TRANSACTION = 'manual';
    public const RECONCILIATION_MATCHED = 'matched';

    /**
     * Post a confirmed transaction payment into the financial ledger.
     *
     * IDR customer_pays      => Cash IN / Bank CREDIT.
     * IDR customer_receives  => Cash OUT / Bank DEBIT.
     *
     * The operation is idempotent: an already-posted payment is not posted twice.
     */
    public function postPayment(McTransactionPayment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $payment = McTransactionPayment::query()
                ->with(['transaction', 'settlement', 'bankAccount'])
                ->lockForUpdate()
                ->findOrFail($payment->getKey());

            if ($payment->payment_status === 'failed') {
                throw new RuntimeException('Payment gagal tidak dapat diposting ke ledger.');
            }

            if (!$payment->settlement) {
                throw new RuntimeException('Settlement payment tidak ditemukan.');
            }

            $idr = Currency::query()
                ->whereKey($payment->currency_id)
                ->where('code', 'IDR')
                ->first();

            if (!$idr) {
                throw new RuntimeException('Currency payment harus IDR.');
            }

            $direction = $payment->settlement->direction === 'customer_pays'
                ? 'in'
                : 'out';

            if ($payment->payment_method === 'cash') {
                $this->postCashPayment($payment, $direction);
            } elseif ($payment->payment_method === 'transfer') {
                $this->postBankPayment($payment, $direction);
            } else {
                throw new RuntimeException(
                    'Payment method ledger tidak didukung: ' . $payment->payment_method
                );
            }
        });
    }

    /**
     * Post a posted expense into the same cash/bank ledger used by transactions.
     *
     * $data:
     * - tenant_id
     * - branch_id
     * - source_type: cash|bank
     * - bank_account_id: required for bank
     * - amount
     * - reference
     * - notes
     * - created_by
     * - expense_key: optional idempotency key stored as reference
     */
    public function postExpense(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $tenantId = trim((string) ($data['tenant_id'] ?? ''));
            $branchId = trim((string) ($data['branch_id'] ?? ''));
            $sourceType = strtolower(trim((string) ($data['source_type'] ?? '')));
            $amount = round((float) ($data['amount'] ?? 0), 2);
            $reference = trim((string) ($data['reference'] ?? ''));
            $notes = $data['notes'] ?? null;
            $createdBy = $data['created_by'] ?? null;

            if ($tenantId === '' || $branchId === '') {
                throw new RuntimeException('Tenant dan branch pengeluaran wajib diisi.');
            }

            if (!in_array($sourceType, ['cash', 'bank'], true)) {
                throw new RuntimeException('Sumber pengeluaran harus cash atau bank.');
            }

            if ($amount <= 0) {
                throw new RuntimeException('Nominal pengeluaran harus lebih besar dari 0.');
            }

            if ($reference === '') {
                throw new RuntimeException('Nomor referensi pengeluaran wajib diisi.');
            }

            if ($sourceType === 'cash') {
                $this->postExpenseCash(
                    $tenantId,
                    $branchId,
                    $amount,
                    $reference,
                    $notes,
                    $createdBy
                );
                return;
            }

            $bankAccountId = trim((string) ($data['bank_account_id'] ?? ''));
            if ($bankAccountId === '') {
                throw new RuntimeException('Rekening bank wajib dipilih untuk pengeluaran bank.');
            }

            $this->postExpenseBank(
                $tenantId,
                $branchId,
                $bankAccountId,
                $amount,
                $reference,
                $notes,
                $createdBy
            );
        });
    }

    protected function postExpenseCash(
        string $tenantId,
        string $branchId,
        float $amount,
        string $reference,
        ?string $notes,
        ?int $createdBy
    ): void {
        $existing = CashMovement::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('movement_type', self::MOVEMENT_EXPENSE)
            ->where('reference', $reference)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return;
        }

        $idr = Currency::query()->where('code', 'IDR')->first();
        if (!$idr) {
            throw new RuntimeException('Currency IDR tidak ditemukan.');
        }

        CashMovement::query()->create([
            'id' => (string) Str::ulid(),
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'transaction_id' => null,
            'payment_id' => null,
            'currency_id' => $idr->id,
            'currency_variant_id' => null,
            'currency_denomination_id' => null,
            'direction' => 'out',
            'quantity' => 1,
            'amount' => $amount,
            'movement_type' => self::MOVEMENT_EXPENSE,
            'reference' => $reference,
            'notes' => $notes,
            'created_by' => $createdBy,
        ]);
    }

    protected function postExpenseBank(
        string $tenantId,
        string $branchId,
        string $bankAccountId,
        float $amount,
        string $reference,
        ?string $notes,
        ?int $createdBy
    ): void {
        $bank = BankAccount::query()
            ->whereKey($bankAccountId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->lockForUpdate()
            ->first();

        if (!$bank) {
            throw new RuntimeException('Rekening bank pengeluaran tidak ditemukan.');
        }

        $existing = BankMutation::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('bank_account_id', $bank->id)
            ->where('source', self::SOURCE_TRANSACTION)
            ->where('reference', $reference)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return;
        }

        $lastMutation = BankMutation::query()
            ->where('bank_account_id', $bank->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->lockForUpdate()
            ->first();

        $previousBalance = $lastMutation?->balance;
        if ($previousBalance === null) {
            $previousBalance = $bank->opening_balance ?? 0;
        }

        $balance = round((float) $previousBalance - $amount, 2);

        BankMutation::query()->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'bank_account_id' => $bank->id,
            'transaction_date' => now(),
            'value_date' => now()->toDateString(),
            'reference' => $reference,
            'description' => 'Pengeluaran ' . $reference,
            'debit' => $amount,
            'credit' => 0,
            'balance' => $balance,
            'external_id' => null,
            'source' => self::SOURCE_TRANSACTION,
            'reconciliation_status' => self::RECONCILIATION_MATCHED,
            'matched_transaction_id' => null,
            'notes' => $notes,
        ]);
    }

    protected function postCashPayment(
        McTransactionPayment $payment,
        string $direction
    ): void {
        $existing = CashMovement::query()
            ->where('payment_id', $payment->id)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return;
        }

        $transaction = $payment->transaction;

        CashMovement::query()->create([
            'id' => (string) Str::ulid(),
            'tenant_id' => $transaction->tenant_id,
            'branch_id' => $transaction->branch_id,
            'transaction_id' => $transaction->id,
            'payment_id' => $payment->id,
            'currency_id' => $payment->currency_id,
            'currency_variant_id' => null,
            'currency_denomination_id' => null,
            'direction' => $direction,
            'quantity' => 1,
            'amount' => $payment->amount,
            'movement_type' => self::MOVEMENT_TRANSACTION_PAYMENT,
            'reference' => $transaction->transaction_no,
            'notes' => $payment->notes,
            'created_by' => $payment->confirmed_by,
        ]);
    }

    protected function postBankPayment(
        McTransactionPayment $payment,
        string $direction
    ): void {
        $existing = $payment->bank_mutation_id
            ? BankMutation::query()
                ->whereKey($payment->bank_mutation_id)
                ->lockForUpdate()
                ->first()
            : null;

        if ($existing) {
            return;
        }

        $bank = BankAccount::query()
            ->whereKey($payment->bank_account_id)
            ->where('tenant_id', $payment->transaction->tenant_id)
            ->where('branch_id', $payment->transaction->branch_id)
            ->lockForUpdate()
            ->first();

        if (!$bank) {
            throw new RuntimeException('Rekening bank payment tidak ditemukan.');
        }

        $lastMutation = BankMutation::query()
            ->where('bank_account_id', $bank->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->lockForUpdate()
            ->first();

        $previousBalance = $lastMutation?->balance;
        if ($previousBalance === null) {
            $previousBalance = $bank->opening_balance ?? 0;
        }

        $amount = round((float) $payment->amount, 2);
        $credit = $direction === 'in' ? $amount : 0;
        $debit = $direction === 'out' ? $amount : 0;
        $balance = round((float) $previousBalance + $credit - $debit, 2);

        $mutation = BankMutation::query()->create([
            'tenant_id' => $payment->transaction->tenant_id,
            'branch_id' => $payment->transaction->branch_id,
            'bank_account_id' => $bank->id,
            'transaction_date' => $payment->paid_at ?? now(),
            'value_date' => ($payment->paid_at ?? now())->toDateString(),
            'reference' => $payment->transfer_reference ?: $payment->transaction->transaction_no,
            'description' => 'Payment transaksi ' . $payment->transaction->transaction_no,
            'debit' => $debit,
            'credit' => $credit,
            'balance' => $balance,
            'external_id' => $payment->transfer_external_id,
            'source' => self::SOURCE_TRANSACTION,
            'reconciliation_status' => self::RECONCILIATION_MATCHED,
            'matched_transaction_id' => $payment->transaction->id,
            'notes' => $payment->notes,
        ]);

        $payment->forceFill([
            'bank_mutation_id' => $mutation->id,
        ])->save();
    }
}
