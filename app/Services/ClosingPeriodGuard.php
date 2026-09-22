<?php

namespace App\Services;

use App\Models\CashClosing;
use Carbon\CarbonInterface;
use Illuminate\Validation\ValidationException;

class ClosingPeriodGuard
{
    /**
     * Prevent posting or editing ledger data inside a closed closing period.
     *
     * The guard is intentionally independent from controllers so Transaction,
     * Cash, Bank and Forex inventory services can share the same rule.
     */
    public function assertOpen(CarbonInterface|string $businessDate): void
    {
        $date = $businessDate instanceof CarbonInterface
            ? $businessDate->toDateString()
            : (string) $businessDate;

        $closed = CashClosing::query()
            ->whereDate('closing_date', $date)
            ->where('status', CashClosing::STATUS_CLOSED)
            ->exists();

        if ($closed) {
            throw ValidationException::withMessages([
                'business_date' => 'Periode tanggal ' . $date . ' sudah CLOSED. Gunakan Adjustment untuk koreksi.',
            ]);
        }
    }

    /**
     * Returns whether a date belongs to a closed period without throwing.
     */
    public function isClosed(CarbonInterface|string $businessDate): bool
    {
        $date = $businessDate instanceof CarbonInterface
            ? $businessDate->toDateString()
            : (string) $businessDate;

        return CashClosing::query()
            ->whereDate('closing_date', $date)
            ->where('status', CashClosing::STATUS_CLOSED)
            ->exists();
    }
}
