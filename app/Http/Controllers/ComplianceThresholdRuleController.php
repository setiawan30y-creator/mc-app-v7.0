<?php

namespace App\Http\Controllers;

use App\Models\ComplianceThresholdRule;
use App\Services\ComplianceThresholdRuleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class ComplianceThresholdRuleController extends Controller
{
    public function __construct(
        protected ComplianceThresholdRuleService $ruleService
    ) {
    }

    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;

        $rules = ComplianceThresholdRule::query()
            ->where('tenant_id', $tenantId)
            ->orderByDesc('effective_from')
            ->orderByDesc('created_at')
            ->get();

        $now = now();

        $activeRule = $rules->first(
            fn (ComplianceThresholdRule $rule) =>
                $rule->isEffectiveAt($now)
        );

        return view('settings.compliance-threshold.index', [
            'rules' => $rules,
            'activeRule' => $activeRule,
            'now' => $now,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $tenantId = auth()->user()->tenant_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'basis' => ['required', Rule::in(['usd', 'idr'])],
            'limit_amount' => ['required', 'numeric', 'gt:0'],
            'period' => ['required', Rule::in(['daily', 'monthly', 'yearly'])],
            'effective_from' => ['required', 'date'],
            'effective_until' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
            'regulation_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
            'notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $effectiveFrom = Carbon::parse(
            $validated['effective_from']
        );

        $effectiveUntil = !empty($validated['effective_until'])
            ? Carbon::parse($validated['effective_until'])
            : null;

        $isActive = (bool) ($validated['is_active'] ?? false);

        try {
            DB::transaction(function () use (
                $tenantId,
                $validated,
                $effectiveFrom,
                $effectiveUntil,
                $isActive
            ) {
                /*
                 * Validate proposed rule as active.
                 */
                $validationRule = new ComplianceThresholdRule([
                    'tenant_id' => $tenantId,
                    'name' => $validated['name'],
                    'basis' => $validated['basis'],
                    'limit_amount' => $validated['limit_amount'],
                    'period' => $validated['period'],
                    'effective_from' => $effectiveFrom,
                    'effective_until' => $effectiveUntil,
                    'is_active' => true,
                ]);

                $this->ruleService->validate($validationRule);

                /*
                 * Generate RULE-xxx automatically.
                 *
                 * Code from browser is intentionally ignored.
                 */
                $lastNumber = ComplianceThresholdRule::query()
                    ->where('tenant_id', $tenantId)
                    ->where('code', 'like', 'RULE-%')
                    ->lockForUpdate()
                    ->pluck('code')
                    ->map(function ($code) {
                        return preg_match(
                            '/^RULE-(\d+)$/',
                            $code,
                            $matches
                        )
                            ? (int) $matches[1]
                            : 0;
                    })
                    ->max();

                $code = 'RULE-' . str_pad(
                    (string) ($lastNumber + 1),
                    3,
                    '0',
                    STR_PAD_LEFT
                );

                /*
                 * Active rule versioning.
                 *
                 * Existing active rule is automatically closed immediately
                 * before the new rule becomes effective.
                 */
                if ($isActive) {
                    $existingRules = ComplianceThresholdRule::query()
                        ->where('tenant_id', $tenantId)
                        ->where('is_active', true)
                        ->orderBy('effective_from')
                        ->lockForUpdate()
                        ->get();

                    foreach ($existingRules as $existingRule) {
                        if (!$this->periodsOverlap(
                            $existingRule->effective_from,
                            $existingRule->effective_until,
                            $effectiveFrom,
                            $effectiveUntil
                        )) {
                            continue;
                        }

                        $oldFrom = Carbon::parse(
                            $existingRule->effective_from
                        );

                        if ($oldFrom->lt($effectiveFrom)) {
                            $existingRule->update([
                                'effective_until' => $effectiveFrom
                                    ->copy()
                                    ->subSecond(),
                                'updated_by' => auth()->id(),
                            ]);
                        } else {
                            $existingRule->update([
                                'is_active' => false,
                                'updated_by' => auth()->id(),
                            ]);
                        }
                    }
                }

                ComplianceThresholdRule::create([
                    'id' => (string) str()->ulid(),
                    'tenant_id' => $tenantId,
                    'name' => $validated['name'],
                    'code' => $code,
                    'basis' => $validated['basis'],
                    'limit_amount' => $validated['limit_amount'],
                    'period' => $validated['period'],
                    'effective_from' => $effectiveFrom,
                    'effective_until' => $effectiveUntil,
                    'is_active' => $isActive,
                    'regulation_reference' =>
                        $validated['regulation_reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                ]);
            });
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'rule' => $e->getMessage(),
                ]);
        }

        return redirect()
            ->route('settings.compliance-threshold.index')
            ->with(
                'success',
                'Rule transaction threshold berhasil dibuat.'
            );
    }

    public function update(
        Request $request,
        ComplianceThresholdRule $complianceThresholdRule
    ): RedirectResponse {
        $tenantId = auth()->user()->tenant_id;

        abort_unless(
            $complianceThresholdRule->tenant_id === $tenantId,
            404
        );

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'basis' => ['required', Rule::in(['usd', 'idr'])],
            'limit_amount' => ['required', 'numeric', 'gt:0'],
            'period' => ['required', Rule::in(['daily', 'monthly', 'yearly'])],
            'effective_from' => ['required', 'date'],
            'effective_until' => [
                'nullable',
                'date',
                'after_or_equal:effective_from',
            ],
            'regulation_reference' => [
                'nullable',
                'string',
                'max:255',
            ],
            'notes' => ['nullable', 'string'],
        ]);

        $effectiveFrom = Carbon::parse(
            $validated['effective_from']
        );

        $effectiveUntil = !empty($validated['effective_until'])
            ? Carbon::parse($validated['effective_until'])
            : null;

        try {
            DB::transaction(function () use (
                $tenantId,
                $validated,
                $effectiveFrom,
                $effectiveUntil,
                $complianceThresholdRule
            ) {
                $validationRule = new ComplianceThresholdRule([
                    'tenant_id' => $tenantId,
                    'name' => $validated['name'],
                    'basis' => $validated['basis'],
                    'limit_amount' => $validated['limit_amount'],
                    'period' => $validated['period'],
                    'effective_from' => $effectiveFrom,
                    'effective_until' => $effectiveUntil,
                    'is_active' => true,
                ]);

                $this->ruleService->validate($validationRule);

                if ($complianceThresholdRule->is_active) {
                    $existingRules = ComplianceThresholdRule::query()
                        ->where('tenant_id', $tenantId)
                        ->where('is_active', true)
                        ->where(
                            'id',
                            '!=',
                            $complianceThresholdRule->id
                        )
                        ->orderBy('effective_from')
                        ->lockForUpdate()
                        ->get();

                    foreach ($existingRules as $existingRule) {
                        if (!$this->periodsOverlap(
                            $existingRule->effective_from,
                            $existingRule->effective_until,
                            $effectiveFrom,
                            $effectiveUntil
                        )) {
                            continue;
                        }

                        $oldFrom = Carbon::parse(
                            $existingRule->effective_from
                        );

                        if ($oldFrom->lt($effectiveFrom)) {
                            $existingRule->update([
                                'effective_until' => $effectiveFrom
                                    ->copy()
                                    ->subSecond(),
                                'updated_by' => auth()->id(),
                            ]);
                        } else {
                            $existingRule->update([
                                'is_active' => false,
                                'updated_by' => auth()->id(),
                            ]);
                        }
                    }
                }

                $complianceThresholdRule->update([
                    'name' => $validated['name'],
                    'basis' => $validated['basis'],
                    'limit_amount' => $validated['limit_amount'],
                    'period' => $validated['period'],
                    'effective_from' => $effectiveFrom,
                    'effective_until' => $effectiveUntil,
                    'regulation_reference' =>
                        $validated['regulation_reference'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'updated_by' => auth()->id(),
                ]);
            });
        } catch (RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors([
                    'rule' => $e->getMessage(),
                ]);
        }

        return redirect()
            ->route('settings.compliance-threshold.index')
            ->with(
                'success',
                'Rule berhasil diperbarui.'
            );
    }

    public function toggle(
        ComplianceThresholdRule $complianceThresholdRule
    ): RedirectResponse {
        $tenantId = auth()->user()->tenant_id;

        abort_unless(
            $complianceThresholdRule->tenant_id === $tenantId,
            404
        );

        $newStatus = !$complianceThresholdRule->is_active;

        try {
            DB::transaction(function () use (
                $tenantId,
                $complianceThresholdRule,
                $newStatus
            ) {
                if ($newStatus) {
                    $validationRule = new ComplianceThresholdRule([
                        'tenant_id' => $tenantId,
                        'name' => $complianceThresholdRule->name,
                        'basis' => $complianceThresholdRule->basis,
                        'limit_amount' =>
                            $complianceThresholdRule->limit_amount,
                        'period' => $complianceThresholdRule->period,
                        'effective_from' =>
                            $complianceThresholdRule->effective_from,
                        'effective_until' =>
                            $complianceThresholdRule->effective_until,
                        'is_active' => true,
                    ]);

                    $this->ruleService->validate($validationRule);

                    $existingRules = ComplianceThresholdRule::query()
                        ->where('tenant_id', $tenantId)
                        ->where('is_active', true)
                        ->where(
                            'id',
                            '!=',
                            $complianceThresholdRule->id
                        )
                        ->orderBy('effective_from')
                        ->lockForUpdate()
                        ->get();

                    foreach ($existingRules as $existingRule) {
                        if (!$this->periodsOverlap(
                            $existingRule->effective_from,
                            $existingRule->effective_until,
                            $complianceThresholdRule->effective_from,
                            $complianceThresholdRule->effective_until
                        )) {
                            continue;
                        }

                        $newFrom = Carbon::parse(
                            $complianceThresholdRule->effective_from
                        );

                        if (
                            Carbon::parse($existingRule->effective_from)
                                ->lt($newFrom)
                        ) {
                            $existingRule->update([
                                'effective_until' => $newFrom
                                    ->copy()
                                    ->subSecond(),
                                'updated_by' => auth()->id(),
                            ]);
                        } else {
                            $existingRule->update([
                                'is_active' => false,
                                'updated_by' => auth()->id(),
                            ]);
                        }
                    }
                }

                $complianceThresholdRule->update([
                    'is_active' => $newStatus,
                    'updated_by' => auth()->id(),
                ]);
            });
        } catch (RuntimeException $e) {
            return back()->withErrors([
                'rule' => $e->getMessage(),
            ]);
        }

        return back()->with(
            'success',
            $newStatus
                ? 'Rule berhasil diaktifkan.'
                : 'Rule berhasil dinonaktifkan.'
        );
    }

    protected function periodsOverlap(
        Carbon|string $firstFrom,
        Carbon|string|null $firstUntil,
        Carbon|string $secondFrom,
        Carbon|string|null $secondUntil
    ): bool {
        $firstFrom = Carbon::parse($firstFrom);
        $secondFrom = Carbon::parse($secondFrom);

        $firstUntil = $firstUntil !== null
            ? Carbon::parse($firstUntil)
            : null;

        $secondUntil = $secondUntil !== null
            ? Carbon::parse($secondUntil)
            : null;

        $firstEnd = $firstUntil
            ?? Carbon::create(9999, 12, 31, 23, 59, 59);

        $secondEnd = $secondUntil
            ?? Carbon::create(9999, 12, 31, 23, 59, 59);

        return $firstFrom->lte($secondEnd)
            && $secondFrom->lte($firstEnd);
    }
}
