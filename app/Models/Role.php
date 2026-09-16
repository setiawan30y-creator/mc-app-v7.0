<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasUlids;

    protected $fillable = [
        'tenant_id',
        'name',
        'code',
        'description',
        'is_system',
        'status',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    /**
     * Tenant pemilik role.
     *
     * Role dengan tenant_id null dianggap sebagai
     * role global/system.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * User yang memiliki role ini.
     *
     * Menggunakan pivot user_roles.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_roles',
            'role_id',
            'user_id'
        )
            ->using(UserRole::class)
            ->withPivot([
                'id',
                'tenant_id',
                'branch_id',
            ])
            ->withTimestamps();
    }

    /**
     * Permission yang dimiliki role.
     *
     * Menggunakan pivot role_permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_permissions',
            'role_id',
            'permission_id'
        )
            ->using(RolePermission::class)
            ->withPivot([
                'id',
                'tenant_id',
                'branch_id',
            ])
            ->withTimestamps();
    }

    /**
     * Memeriksa apakah role memiliki permission tertentu
     * dalam scope tenant dan cabang.
     *
     * Catatan:
     * Method ini digunakan ketika konteks tenant/cabang
     * diketahui dari pivot role_permissions.
     */
    public function hasPermission(
        string $permissionCode,
        ?string $tenantId = null,
        ?string $branchId = null
    ): bool {
        $query = $this->permissions()
            ->where('permissions.code', $permissionCode);

        /*
         * Role harus sesuai dengan tenant yang diminta,
         * atau merupakan role global.
         */
        if ($tenantId !== null) {
            $query->where(function ($q) use ($tenantId) {
                $q->where('roles.tenant_id', $tenantId)
                    ->orWhereNull('roles.tenant_id');
            });

            /*
             * Permission assignment harus berada
             * pada tenant yang sama atau global.
             */
            $query->where(function ($q) use ($tenantId) {
                $q->where('role_permissions.tenant_id', $tenantId)
                    ->orWhereNull('role_permissions.tenant_id');
            });
        }

        /*
         * Permission assignment harus berada
         * pada cabang yang sama atau global.
         */
        if ($branchId !== null) {
            $query->where(function ($q) use ($branchId) {
                $q->where('role_permissions.branch_id', $branchId)
                    ->orWhereNull('role_permissions.branch_id');
            });
        }

        return $query->exists();
    }
}