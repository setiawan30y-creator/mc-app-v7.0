<?php

namespace App\Services;

use App\Models\BankMutation;
use App\Models\CashClosing;
use App\Models\CashMovement;
use App\Models\Currency;
use App\Models\Gantungan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CashClosingReconciliationService
{
    /**
     * Build the operational closing numbers from ledger sources.
     *
     * Cash: previous physical closing + cash movements for the period.
     * Bank: bank mutations for the period.
     * Gantungan: outstanding operational hanging amount at the business date.
     */
    public function calculate(string $tenantId, ?string $branchId, string $businessDate, string $shift): array
    {
        $start = Carbon::parse($businessDate, config('app.timezone'))->startOfDay();
        $end = $shift === 'morning'
            ? $start->copy()->setTime(12, 0, 0)
            : $start->copy()->endOfDay();

        $previous = CashClosing::query()
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->where('status', 'closed')
            ->where(function ($q) use ($start) {
                $q->where('business_date', '<', $start->toDateString())
                    ->orWhere(function ($q2) use ($start) {
                        $q2->whereDate('business_date', $start->toDateString())
                            ->where('shift', 'morning');
                    });
            })
            ->orderByDesc('business_date')
            ->orderByDesc('closed_at')
            ->first();

        $opening = $previous ? (float) $previous->physical_cash_amount : 0.0;

        $idr = Currency::query()->where('code', 'IDR')->first();
        $cashIn = 0.0;
        $cashOut = 0.0;

        if ($idr) {
            $movements = CashMovement::query()
                ->where('tenant_id', $tenantId)
                ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
                ->where('currency_id', $idr->id)
                ->whereBetween('created_at', [$start, $end])
                ->get(['direction', 'amount']);

            foreach ($movements as $movement) {
                $amount = (float) $movement->amount;
                if (in_array(strtolower((string) $movement->direction), ['in', 'inflow', 'credit', 'receive'], true)) {
                    $cashIn += $amount;
                } else {
                    $cashOut += $amount;
                }
            }
        }

        $expectedCash = round($opening + $cashIn - $cashOut, 2);

        $bankQuery = BankMutation::query()
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('transaction_date', [$start, $end]);

        $bankCredit = (float) $bankQuery->sum('credit');
        $bankDebit = (float) $bankQuery->sum('debit');
        $bankNet = round($bankCredit - $bankDebit, 2);

        $hanging = (float) Gantungan::query()
            ->where('tenant_id', $tenantId)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->whereDate('business_date', '<=', $businessDate)
            ->whereIn('status', ['open', 'partial'])
            ->sum('outstanding_amount');

        return [
            'opening_cash_amount' => round($opening, 2),
            'cash_in_amount' => round($cashIn, 2),
            'cash_out_amount' => round($cashOut, 2),
            'expected_cash_amount' => $expectedCash,
            'bank_credit_amount' => round($bankCredit, 2),
            'bank_debit_amount' => round($bankDebit, 2),
            'bank_system_amount' => $bankNet,
            'hanging_amount' => round($hanging, 2),
            'period_start' => $start,
            'period_end' => $end,
            'previous_closing_id' => $previous?->id,
        ];
    }

    public function apply(CashClosing $closing): CashClosing
    {
        $result = $this->calculate(
            $closing->tenant_id,
            $closing->branch_id,
            $closing->business_date->toDateString(),
            $closing->shift
        );

        $physical = (float) $closing->physical_cash_amount;
        $cashDifference = round($physical - $result['expected_cash_amount'], 2);

        $closing->update([
            'opening_cash_amount' => $result['opening_cash_amount'],
            'expected_cash_amount' => $result['expected_cash_amount'],
            'expected_amount' => $result['expected_cash_amount'],
            'physical_amount' => $physical,
            'cash_difference_amount' => $cashDifference,
            'difference_amount' => $cashDifference,
            'hanging_amount' => $result['hanging_amount'],
            'bank_system_amount' => $result['bank_system_amount'],
            'bank_difference_amount' => 0,
        ]);

        return $closing->refresh();
    }

    public function canClose(CashClosing $closing): bool
    {
        return bccomp((string) $closing->difference_amount, '0', 2) === 0;
    }
}
