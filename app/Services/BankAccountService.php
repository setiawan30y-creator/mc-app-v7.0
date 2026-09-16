<?php

namespace App\Services;

use App\Models\BankAccount;
use App\Models\Branch;
use App\Models\Currency;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BankAccountService
{
    /**
     * Ambil seluruh rekening aktif milik tenant + branch.
     */
    public function list(
        string $tenantId,
        string $branchId,
        bool $activeOnly = false
    ): Collection {
        $query = BankAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->with('currency')
            ->orderBy('bank_name')
            ->orderBy('account_name');

        if ($activeOnly) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    /**
     * Ambil satu rekening yang benar-benar berada
     * pada tenant dan branch yang diminta.
     */
    public function find(
        string $tenantId,
        string $branchId,
        string $bankAccountId
    ): BankAccount {
        return BankAccount::query()
            ->where('id', $bankAccountId)
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->with('currency')
            ->firstOrFail();
    }

    /**
     * Membuat rekening bank baru.
     */
    public function create(
        string $tenantId,
        string $branchId,
        array $data
    ): BankAccount {
        $this->validateBranchBelongsToTenant(
            $tenantId,
            $branchId
        );

        $currency = $this->validateCurrency(
            $data['currency_id'] ?? null
        );

        $accountNumber = trim(
            (string) ($data['account_number'] ?? '')
        );

        if ($accountNumber === '') {
            throw ValidationException::withMessages([
                'account_number' => 'Nomor rekening wajib diisi.',
            ]);
        }

        $duplicate = BankAccount::query()
            ->where('tenant_id', $tenantId)
            ->where('account_number', $accountNumber)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'account_number' =>
                    'Nomor rekening tersebut sudah terdaftar pada tenant ini.',
            ]);
        }

        return DB::transaction(function () use (
            $tenantId,
            $branchId,
            $data,
            $currency,
            $accountNumber
        ): BankAccount {
            return BankAccount::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'bank_name' => trim((string) $data['bank_name']),
                'bank_code' => isset($data['bank_code'])
                    ? trim((string) $data['bank_code'])
                    : null,
                'account_name' => trim((string) $data['account_name']),
                'account_number' => $accountNumber,
                'currency_id' => $currency->id,
                'opening_balance' => $data['opening_balance'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'notes' => isset($data['notes'])
                    ? trim((string) $data['notes'])
                    : null,
            ]);
        });
    }

    /**
     * Memperbarui rekening.
     */
    public function update(
        string $tenantId,
        string $branchId,
        string $bankAccountId,
        array $data
    ): BankAccount {
        $account = $this->find(
            $tenantId,
            $branchId,
            $bankAccountId
        );

        if (array_key_exists('currency_id', $data)) {
            $this->validateCurrency($data['currency_id']);
        }

        if (array_key_exists('account_number', $data)) {
            $accountNumber = trim(
                (string) $data['account_number']
            );

            if ($accountNumber === '') {
                throw ValidationException::withMessages([
                    'account_number' =>
                        'Nomor rekening wajib diisi.',
                ]);
            }

            $duplicate = BankAccount::query()
                ->where('tenant_id', $tenantId)
                ->where('account_number', $accountNumber)
                ->where('id', '!=', $bankAccountId)
                ->exists();

            if ($duplicate) {
                throw ValidationException::withMessages([
                    'account_number' =>
                        'Nomor rekening tersebut sudah terdaftar pada tenant ini.',
                ]);
            }

            $data['account_number'] = $accountNumber;
        }

        $allowed = [
            'bank_name',
            'bank_code',
            'account_name',
            'account_number',
            'currency_id',
            'opening_balance',
            'is_active',
            'notes',
        ];

        $payload = [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $payload[$field] = match ($field) {
                    'bank_name',
                    'bank_code',
                    'account_name',
                    'account_number',
                    'notes' => $data[$field] === null
                        ? null
                        : trim((string) $data[$field]),
                    default => $data[$field],
                };
            }
        }

        $account->update($payload);

        return $account->fresh([
            'currency',
            'branch',
            'tenant',
        ]);
    }

    /**
     * Nonaktifkan rekening.
     */
    public function deactivate(
        string $tenantId,
        string $branchId,
        string $bankAccountId
    ): BankAccount {
        $account = $this->find(
            $tenantId,
            $branchId,
            $bankAccountId
        );

        $account->update([
            'is_active' => false,
        ]);

        return $account->fresh();
    }

    /**
     * Aktifkan kembali rekening.
     */
    public function activate(
        string $tenantId,
        string $branchId,
        string $bankAccountId
    ): BankAccount {
        $account = $this->find(
            $tenantId,
            $branchId,
            $bankAccountId
        );

        $account->update([
            'is_active' => true,
        ]);

        return $account->fresh();
    }

    /**
     * Saldo rekening:
     *
     * opening_balance + credit - debit.
     *
     * Tidak menggunakan kolom current_balance,
     * karena saldo dihitung dari sumber transaksi.
     */
    public function calculateBalance(
        string $tenantId,
        string $branchId,
        string $bankAccountId
    ): string {
        $account = $this->find(
            $tenantId,
            $branchId,
            $bankAccountId
        );

        return $account->calculated_balance;
    }

    /**
     * Pastikan branch memang milik tenant.
     */
    private function validateBranchBelongsToTenant(
        string $tenantId,
        string $branchId
    ): Branch {
        $branch = Branch::query()
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $branch) {
            throw ValidationException::withMessages([
                'branch_id' =>
                    'Cabang tidak valid atau bukan milik tenant ini.',
            ]);
        }

        return $branch;
    }

    /**
     * Pastikan currency tersedia dan aktif.
     */
    private function validateCurrency(
        mixed $currencyId
    ): Currency {
        if (! $currencyId) {
            throw ValidationException::withMessages([
                'currency_id' =>
                    'Mata uang wajib dipilih.',
            ]);
        }

        $currency = Currency::query()
            ->whereKey($currencyId)
            ->where('is_active', true)
            ->first();

        if (! $currency) {
            throw ValidationException::withMessages([
                'currency_id' =>
                    'Mata uang tidak valid atau sedang tidak aktif.',
            ]);
        }

        return $currency;
    }
}