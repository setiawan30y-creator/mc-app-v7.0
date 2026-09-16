<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\BankMutation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankMutationService
{
    /**
     * Daftar mutasi untuk satu rekening.
     */
    public function list(
        string $tenantId,
        string $branchId,
        string $bankAccountId
    ): Collection {
        $account = $this->findAccount(
            $tenantId,
            $branchId,
            $bankAccountId
        );

        return $account->mutations()
            ->with('bankAccount.currency')
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();
    }

    /**
     * Daftar mutasi global seluruh rekening pada tenant + branch aktif.
     *
     * Filter yang didukung:
     * - bank_account_id
     * - reconciliation_status
     * - date_from
     * - date_to
     * - search
     */
    public function globalList(
        string $tenantId,
        string $branchId,
        array $filters = [],
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = BankMutation::query()
            ->where('bank_mutations.tenant_id', $tenantId)
            ->where('bank_mutations.branch_id', $branchId)
            ->with([
                'bankAccount.currency',
            ]);

        /*
         * Filter rekening.
         */
        if (!empty($filters['bank_account_id'])) {
            $query->where(
                'bank_mutations.bank_account_id',
                $filters['bank_account_id']
            );
        }

        /*
         * Filter status rekonsiliasi.
         */
        if (!empty($filters['reconciliation_status'])) {
            $query->where(
                'bank_mutations.reconciliation_status',
                $filters['reconciliation_status']
            );
        }

        /*
         * Filter tanggal mulai.
         */
        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'bank_mutations.transaction_date',
                '>=',
                $filters['date_from']
            );
        }

        /*
         * Filter tanggal akhir.
         */
        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'bank_mutations.transaction_date',
                '<=',
                $filters['date_to']
            );
        }

        /*
         * Pencarian global.
         *
         * Bisa mencari:
         * - reference
         * - description
         * - external_id
         * - notes
         */
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'bank_mutations.reference',
                        'like',
                        '%' . $search . '%'
                    )
                        ->orWhere(
                            'bank_mutations.description',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'bank_mutations.external_id',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'bank_mutations.notes',
                            'like',
                            '%' . $search . '%'
                        );
                });
            }
        }

        return $query
            ->orderByDesc('bank_mutations.transaction_date')
            ->orderByDesc('bank_mutations.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Ringkasan mutasi global berdasarkan mata uang.
     *
     * PENTING:
     * IDR, USD, SGD, EUR, dll tidak boleh dijumlahkan
     * menjadi satu angka karena mata uang berbeda.
     */
    public function globalSummary(
        string $tenantId,
        string $branchId,
        array $filters = []
    ): Collection {
        $query = BankMutation::query()
            ->join(
                'bank_accounts',
                'bank_accounts.id',
                '=',
                'bank_mutations.bank_account_id'
            )
            ->join(
                'currencies',
                'currencies.id',
                '=',
                'bank_accounts.currency_id'
            )
            ->where(
                'bank_mutations.tenant_id',
                $tenantId
            )
            ->where(
                'bank_mutations.branch_id',
                $branchId
            );

        /*
         * Filter rekening.
         */
        if (!empty($filters['bank_account_id'])) {
            $query->where(
                'bank_mutations.bank_account_id',
                $filters['bank_account_id']
            );
        }

        /*
         * Filter status.
         */
        if (!empty($filters['reconciliation_status'])) {
            $query->where(
                'bank_mutations.reconciliation_status',
                $filters['reconciliation_status']
            );
        }

        /*
         * Filter tanggal.
         */
        if (!empty($filters['date_from'])) {
            $query->whereDate(
                'bank_mutations.transaction_date',
                '>=',
                $filters['date_from']
            );
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate(
                'bank_mutations.transaction_date',
                '<=',
                $filters['date_to']
            );
        }

        /*
         * Filter pencarian.
         */
        if (!empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            if ($search !== '') {
                $query->where(function ($q) use ($search) {
                    $q->where(
                        'bank_mutations.reference',
                        'like',
                        '%' . $search . '%'
                    )
                        ->orWhere(
                            'bank_mutations.description',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'bank_mutations.external_id',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'bank_mutations.notes',
                            'like',
                            '%' . $search . '%'
                        );
                });
            }
        }

        return $query
            ->selectRaw('
                currencies.id as currency_id,
                currencies.code as currency_code,
                currencies.name as currency_name,
                COALESCE(SUM(bank_mutations.credit), 0) as total_credit,
                COALESCE(SUM(bank_mutations.debit), 0) as total_debit,
                COUNT(bank_mutations.id) as mutation_count
            ')
            ->groupBy(
                'currencies.id',
                'currencies.code',
                'currencies.name'
            )
            ->orderBy('currencies.code')
            ->get();
    }

    /**
     * Semua rekening aktif pada branch.
     */
    public function globalAccounts(
        string $tenantId,
        string $branchId
    ): Collection {
        return BankAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->with('currency')
            ->orderBy('bank_name')
            ->orderBy('account_name')
            ->get();
    }

    /**
     * Detail satu mutasi.
     */
    public function find(
        string $tenantId,
        string $branchId,
        string $mutationId
    ): BankMutation {
        return BankMutation::query()
            ->where('id', $mutationId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->with('bankAccount.currency')
            ->firstOrFail();
    }

    /**
     * Membuat mutasi baru.
     */
    public function create(
        string $tenantId,
        string $branchId,
        array $data
    ): BankMutation {
        $account = $this->findAccount(
            $tenantId,
            $branchId,
            $data['bank_account_id'] ?? null
        );

        $debit = $this->normalizeAmount(
            $data['debit'] ?? 0
        );

        $credit = $this->normalizeAmount(
            $data['credit'] ?? 0
        );

        $this->validateMutationAmounts(
            $debit,
            $credit
        );

        $source = $data['source'] ?? 'manual';

        if (!in_array(
            $source,
            [
                'manual',
                'import',
                'api',
                'bank_statement',
            ],
            true
        )) {
            throw ValidationException::withMessages([
                'source' => 'Source mutasi tidak valid.',
            ]);
        }

        $status = $data['reconciliation_status']
            ?? 'unmatched';

        if (!in_array(
            $status,
            [
                'unmatched',
                'matched',
                'manual',
                'ignored',
            ],
            true
        )) {
            throw ValidationException::withMessages([
                'reconciliation_status'
                    => 'Status rekonsiliasi tidak valid.',
            ]);
        }

        $externalId = isset($data['external_id'])
            ? trim((string) $data['external_id'])
            : null;

        if ($externalId !== null && $externalId !== '') {
            $exists = BankMutation::query()
                ->where('tenant_id', $tenantId)
                ->where(
                    'bank_account_id',
                    $account->id
                )
                ->where(
                    'external_id',
                    $externalId
                )
                ->exists();

            if ($exists) {
                throw ValidationException::withMessages([
                    'external_id'
                        => 'External ID tersebut sudah terdaftar pada rekening ini.',
                ]);
            }
        } else {
            $externalId = null;
        }

        $matchedTransactionId = null;

        if ($status === 'matched') {
            $matchedTransactionId = isset(
                $data['matched_transaction_id']
            )
                ? trim(
                    (string) $data['matched_transaction_id']
                )
                : null;

            if ($matchedTransactionId === '') {
                $matchedTransactionId = null;
            }

            if ($matchedTransactionId === null) {
                throw ValidationException::withMessages([
                    'matched_transaction_id'
                        => 'Transaksi MC wajib diisi untuk status matched.',
                ]);
            }
        }

        return DB::transaction(
            function () use (
                $tenantId,
                $branchId,
                $account,
                $data,
                $debit,
                $credit,
                $source,
                $status,
                $externalId,
                $matchedTransactionId
            ): BankMutation {
                return BankMutation::create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'bank_account_id' => $account->id,
                    'transaction_date'
                        => $data['transaction_date'],
                    'value_date'
                        => $data['value_date'] ?? null,
                    'reference'
                        => isset($data['reference'])
                            ? trim(
                                (string) $data['reference']
                            )
                            : null,
                    'description'
                        => isset($data['description'])
                            ? trim(
                                (string) $data['description']
                            )
                            : null,
                    'debit' => $debit,
                    'credit' => $credit,
                    'balance'
                        => $data['balance'] ?? null,
                    'external_id' => $externalId,
                    'source' => $source,
                    'reconciliation_status' => $status,
                    'matched_transaction_id'
                        => $matchedTransactionId,
                    'notes'
                        => isset($data['notes'])
                            ? trim(
                                (string) $data['notes']
                            )
                            : null,
                ]);
            }
        );
    }

    /**
     * Rekonsiliasi mutasi.
     */
    public function reconcile(
        string $tenantId,
        string $branchId,
        string $mutationId,
        string $status,
        ?string $matchedTransactionId = null
    ): BankMutation {
        $mutation = $this->find(
            $tenantId,
            $branchId,
            $mutationId
        );

        if (!in_array(
            $status,
            [
                'unmatched',
                'matched',
                'manual',
                'ignored',
            ],
            true
        )) {
            throw ValidationException::withMessages([
                'reconciliation_status'
                    => 'Status rekonsiliasi tidak valid.',
            ]);
        }

        if ($status === 'matched') {
            $matchedTransactionId =
                $matchedTransactionId !== null
                    ? trim($matchedTransactionId)
                    : null;

            if (
                $matchedTransactionId === null
                || $matchedTransactionId === ''
            ) {
                throw ValidationException::withMessages([
                    'matched_transaction_id'
                        => 'Transaksi MC wajib diisi untuk status matched.',
                ]);
            }
        } else {
            $matchedTransactionId = null;
        }

        $mutation->update([
            'reconciliation_status' => $status,
            'matched_transaction_id'
                => $matchedTransactionId,
        ]);

        return $mutation->fresh([
            'bankAccount.currency',
        ]);
    }

    /**
     * Tandai ignored.
     */
    public function ignore(
        string $tenantId,
        string $branchId,
        string $mutationId
    ): BankMutation {
        return $this->reconcile(
            $tenantId,
            $branchId,
            $mutationId,
            'ignored'
        );
    }

    /**
     * Hitung saldo setelah mutasi tertentu.
     */
    public function calculateBalanceAfterMutation(
        string $tenantId,
        string $branchId,
        string $mutationId
    ): string {
        $mutation = $this->find(
            $tenantId,
            $branchId,
            $mutationId
        );

        $account = $mutation->bankAccount;

        $totals = BankMutation::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where(
                'bank_account_id',
                $account->id
            )
            ->where(function ($query) use ($mutation) {
                $query
                    ->where(
                        'transaction_date',
                        '<',
                        $mutation->transaction_date
                    )
                    ->orWhere(function ($query) use ($mutation) {
                        $query
                            ->where(
                                'transaction_date',
                                '=',
                                $mutation->transaction_date
                            )
                            ->where(
                                'id',
                                '<=',
                                $mutation->id
                            );
                    });
            })
            ->selectRaw('
                COALESCE(SUM(credit), 0) AS total_credit,
                COALESCE(SUM(debit), 0) AS total_debit
            ')
            ->first();

        return bcadd(
            bcsub(
                (string) $account->opening_balance,
                (string) (
                    $totals->total_debit ?? '0'
                ),
                2
            ),
            (string) (
                $totals->total_credit ?? '0'
            ),
            2
        );
    }

    /**
     * Cari rekening dalam tenant + branch aktif.
     */
    private function findAccount(
        string $tenantId,
        string $branchId,
        ?string $bankAccountId
    ): BankAccount {
        if (!$bankAccountId) {
            throw ValidationException::withMessages([
                'bank_account_id'
                    => 'Rekening bank wajib dipilih.',
            ]);
        }

        return BankAccount::query()
            ->where('id', $bankAccountId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->firstOrFail();
    }

    /**
     * Normalisasi nominal.
     */
    private function normalizeAmount(
        mixed $amount
    ): string {
        if ($amount === null || $amount === '') {
            return '0.00';
        }

        $amount = trim((string) $amount);
        $amount = str_replace(',', '', $amount);

        if ($amount === '') {
            return '0.00';
        }

        if (!preg_match(
            '/^\d+(?:\.\d+)?$/',
            $amount
        )) {
            throw ValidationException::withMessages([
                'amount'
                    => 'Nominal mutasi harus berupa angka positif atau nol.',
            ]);
        }

        if (bccomp($amount, '0', 4) < 0) {
            throw ValidationException::withMessages([
                'amount'
                    => 'Nominal debit/credit tidak boleh negatif.',
            ]);
        }

        return bcadd($amount, '0', 2);
    }

    /**
     * Validasi debit / credit.
     */
    private function validateMutationAmounts(
        string $debit,
        string $credit
    ): void {
        $hasDebit = bccomp(
            $debit,
            '0.00',
            2
        ) > 0;

        $hasCredit = bccomp(
            $credit,
            '0.00',
            2
        ) > 0;

        if (!$hasDebit && !$hasCredit) {
            throw ValidationException::withMessages([
                'amount'
                    => 'Debit atau credit harus memiliki nominal.',
            ]);
        }

        if ($hasDebit && $hasCredit) {
            throw ValidationException::withMessages([
                'amount'
                    => 'Debit dan credit tidak boleh diisi bersamaan.',
            ]);
        }
    }
}