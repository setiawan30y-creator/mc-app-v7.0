<?php

namespace App\Services;

use App\Models\CashClosing;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class ClosingPeriodGuard
{
    public function assertOpen(
        CarbonInterface|string $businessDate,
        ?string $tenantId = null,
        ?string $branchId = null,
    ): void {
        $date = $businessDate instanceof CarbonInterface
            ? $businessDate->toDateString()
            : date('Y-m-d', strtotime((string) $businessDate));

        $tenantId ??= auth()->user()?->tenant_id;
        $branchId ??= auth()->user()?->branch_id;

        if (!$tenantId) {
            return;
        }

        $closed = CashClosing::query()
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('business_date', $date)
            ->where('status', 'closed')
            ->exists();

        if ($closed) {
            throw ValidationException::withMessages([
                'business_date' => 'Periode tanggal ' . $date . ' sudah CLOSED. Gunakan Adjustment untuk koreksi.',
            ]);
        }
    }

    public function isClosed(
        CarbonInterface|string $businessDate,
        ?string $tenantId = null,
        ?string $branchId = null,
    ): bool {
        $date = $businessDate instanceof CarbonInterface
            ? $businessDate->toDateString()
            : date('Y-m-d', strtotime((string) $businessDate));

        $tenantId ??= auth()->user()?->tenant_id;
        $branchId ??= auth()->user()?->branch_id;

        if (!$tenantId) {
            return false;
        }

        return CashClosing::query()
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('business_date', $date)
            ->where('status', 'closed')
            ->exists();
    }
}
