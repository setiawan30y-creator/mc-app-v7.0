<?php

namespace App\Services;

use App\Models\ComplianceThresholdRule;
use Illuminate\Support\Carbon;
use RuntimeException;

class ComplianceThresholdRuleService
{
    /**
     * Cari rule threshold yang berlaku untuk tenant
     * pada waktu transaksi tertentu.
     */
    public function resolve(
        string $tenantId,
        Carbon|string $transactionDate
    ): ComplianceThresholdRule {
        $date = $transactionDate instanceof Carbon
            ? $transactionDate
            : Carbon::parse($transactionDate);

        $rule = ComplianceThresholdRule::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->where('effective_from', '<=', $date)
            ->where(function ($query) use ($date) {
                $query
                    ->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date);
            })
            ->orderByDesc('effective_from')
            ->orderByDesc('id')
            ->first();

        if (!$rule) {
            throw new RuntimeException(
                'Rule compliance threshold yang berlaku tidak ditemukan '
                . 'untuk tenant dan tanggal transaksi tersebut.'
            );
        }

        return $rule;
    }

    /**
     * Validasi rule sebelum digunakan.
     */
    public function validate(ComplianceThresholdRule $rule): void
    {
        if (!$rule->is_active) {
            throw new RuntimeException(
                'Rule compliance threshold tidak aktif.'
            );
        }

        if ((float) $rule->limit_amount <= 0) {
            throw new RuntimeException(
                'Limit compliance threshold harus lebih besar dari 0.'
            );
        }

        if (!in_array(
            $rule->basis,
            ['usd', 'idr'],
            true
        )) {
            throw new RuntimeException(
                'Basis compliance threshold tidak valid.'
            );
        }

        if (!in_array(
            $rule->period,
            ['daily', 'monthly', 'yearly'],
            true
        )) {
            throw new RuntimeException(
                'Periode compliance threshold tidak valid.'
            );
        }

        if (
            $rule->effective_until !== null &&
            $rule->effective_until->lt($rule->effective_from)
        ) {
            throw new RuntimeException(
                'Tanggal akhir rule tidak boleh sebelum tanggal mulai.'
            );
        }
    }

    /**
     * Cek apakah rule baru bertabrakan dengan rule aktif
     * milik tenant yang sama.
     */
    public function hasOverlap(
        string $tenantId,
        Carbon|string $effectiveFrom,
        Carbon|string|null $effectiveUntil = null,
        ?string $ignoreRuleId = null
    ): bool {
        $from = $effectiveFrom instanceof Carbon
            ? $effectiveFrom
            : Carbon::parse($effectiveFrom);

        $until = $effectiveUntil !== null
            ? (
                $effectiveUntil instanceof Carbon
                    ? $effectiveUntil
                    : Carbon::parse($effectiveUntil)
            )
            : null;

        $query = ComplianceThresholdRule::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true);

        if ($ignoreRuleId !== null) {
            $query->where('id', '!=', $ignoreRuleId);
        }

        $query->where(function ($q) use ($from, $until) {
            /*
             * Existing rule starts before new rule ends
             */
            $q->where('effective_from', '<=', $until ?? Carbon::create(9999, 12, 31))

                /*
                 * Existing rule has no end OR ends after new rule starts
                 */
                ->where(function ($q2) use ($from) {
                    $q2
                        ->whereNull('effective_until')
                        ->orWhere('effective_until', '>=', $from);
                });
        });

        return $query->exists();
    }

    /**
     * Ambil limit rule.
     */
    public function getLimit(
        ComplianceThresholdRule $rule
    ): string {
        $this->validate($rule);

        return number_format(
            (float) $rule->limit_amount,
            2,
            '.',
            ''
        );
    }

    /**
     * Basis threshold.
     */
    public function getBasis(
        ComplianceThresholdRule $rule
    ): string {
        $this->validate($rule);

        return $rule->basis;
    }

    /**
     * Periode threshold.
     */
    public function getPeriod(
        ComplianceThresholdRule $rule
    ): string {
        $this->validate($rule);

        return $rule->period;
    }
}