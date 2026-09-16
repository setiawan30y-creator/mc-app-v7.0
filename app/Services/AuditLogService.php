<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    public function record(
        User $user,
        string $action,
        string $module,
        ?string $referenceType = null,
        ?string $referenceId = null,
        ?array $beforeData = null,
        ?array $afterData = null,
        ?string $reason = null,
        ?Request $request = null,
    ): AuditLog {
        $changedFields = [];

        if ($beforeData !== null && $afterData !== null) {
            $keys = array_unique([
                ...array_keys($beforeData),
                ...array_keys($afterData),
            ]);

            foreach ($keys as $key) {
                $before = $beforeData[$key] ?? null;
                $after = $afterData[$key] ?? null;

                if ($before !== $after) {
                    $changedFields[] = $key;
                }
            }
        }

        return AuditLog::create([
            'tenant_id' => $user->tenant_id,
            'branch_id' => $user->branch_id,
            'user_id' => $user->id,

            'action' => $action,
            'module' => $module,

            'reference_type' => $referenceType,
            'reference_id' => $referenceId,

            'before_data' => $beforeData,
            'after_data' => $afterData,
            'changed_fields' => $changedFields,

            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),

            'reason' => $reason,
        ]);
    }
}