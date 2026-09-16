<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\McTransactionSequence;
use App\Models\Tenant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class McTransactionSequenceService
{
    /**
     * Generate nomor transaksi berikutnya secara atomic.
     *
     * Format:
     * TENANT_CODE/BRANCH_CODE/YYYYMMDD/000001
     */
    public function next(
        string $tenantId,
        string $branchId,
        ?string $sequenceDate = null
    ): string {
        return DB::transaction(function () use (
            $tenantId,
            $branchId,
            $sequenceDate
        ) {
            $date = $sequenceDate
                ? \Carbon\Carbon::parse($sequenceDate)->startOfDay()
                : now()->startOfDay();

            $sequence = McTransactionSequence::query()
                ->where('tenant_id', $tenantId)
                ->where('branch_id', $branchId)
                ->whereDate('sequence_date', $date->toDateString())
                ->lockForUpdate()
                ->first();

            if (!$sequence) {
                try {
                    $sequence = McTransactionSequence::query()->create([
                        'tenant_id' => $tenantId,
                        'branch_id' => $branchId,
                        'sequence_date' => $date->toDateString(),
                        'last_number' => 0,
                    ]);
                } catch (QueryException $e) {
                    $sequence = McTransactionSequence::query()
                        ->where('tenant_id', $tenantId)
                        ->where('branch_id', $branchId)
                        ->whereDate('sequence_date', $date->toDateString())
                        ->lockForUpdate()
                        ->first();

                    if (!$sequence) {
                        throw $e;
                    }
                }
            }

            $sequence->increment('last_number');

            $sequence->refresh();

            $tenant = Tenant::query()->find($tenantId);
            $branch = Branch::query()->find($branchId);

            if (!$tenant) {
                throw new RuntimeException(
                    "Tenant {$tenantId} tidak ditemukan."
                );
            }

            if (!$branch) {
                throw new RuntimeException(
                    "Branch {$branchId} tidak ditemukan."
                );
            }

            $tenantCode = strtoupper(trim((string) $tenant->code));
            $branchCode = strtoupper(trim((string) $branch->code));

            if ($tenantCode === '') {
                throw new RuntimeException(
                    'Tenant code tidak boleh kosong.'
                );
            }

            if ($branchCode === '') {
                throw new RuntimeException(
                    'Branch code tidak boleh kosong.'
                );
            }

            return sprintf(
                '%s/%s/%s/%06d',
                $tenantCode,
                $branchCode,
                $date->format('Ymd'),
                $sequence->last_number
            );
        });
    }
}