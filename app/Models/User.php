<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
    'name',
    'email',
    'password',
    'tenant_id',
    'branch_id',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Tenant tempat user berada.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Cabang tempat user bekerja.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Role yang dimiliki user.
     *
     * Menggunakan pivot user_roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'user_roles',
            'user_id',
            'role_id'
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
     * Memeriksa apakah user memiliki role tertentu
     * dalam scope tenant dan cabangnya.
     */
    public function hasRole(string $roleCode): bool
    {
        $query = $this->roles()
            ->where('roles.code', $roleCode);

        /*
         * Scope tenant.
         */
        if ($this->tenant_id !== null) {
            $query->where(function ($q) {
                $q->where('user_roles.tenant_id', $this->tenant_id)
                    ->orWhereNull('user_roles.tenant_id');
            });

            $query->where(function ($q) {
                $q->where('roles.tenant_id', $this->tenant_id)
                    ->orWhereNull('roles.tenant_id');
            });
        }

        /*
         * Scope branch.
         */
        if ($this->branch_id !== null) {
            $query->where(function ($q) {
                $q->where('user_roles.branch_id', $this->branch_id)
                    ->orWhereNull('user_roles.branch_id');
            });
        }

        return $query->exists();
    }

    /**
     * Memeriksa apakah user memiliki permission tertentu
     * melalui role yang berlaku pada tenant dan cabangnya.
     */
    public function hasPermission(string $permissionCode): bool
    {
        return $this->roles()
            ->where(function ($roleQuery) {
                /*
                 * Role harus berasal dari tenant user
                 * atau merupakan role global.
                 */
                if ($this->tenant_id !== null) {
                    $roleQuery->where(function ($q) {
                        $q->where('roles.tenant_id', $this->tenant_id)
                            ->orWhereNull('roles.tenant_id');
                    });
                }
            })
            ->whereHas('permissions', function ($permissionQuery) use ($permissionCode) {
                $permissionQuery
                    ->where('permissions.code', $permissionCode)

                    /*
                     * Scope tenant pada role_permissions.
                     */
                    ->where(function ($q) {
                        if ($this->tenant_id !== null) {
                            $q->where(
                                'role_permissions.tenant_id',
                                $this->tenant_id
                            )
                                ->orWhereNull('role_permissions.tenant_id');
                        }
                    })

                    /*
                     * Scope branch pada role_permissions.
                     */
                    ->where(function ($q) {
                        if ($this->branch_id !== null) {
                            $q->where(
                                'role_permissions.branch_id',
                                $this->branch_id
                            )
                                ->orWhereNull('role_permissions.branch_id');
                        }
                    });
            })
            ->exists();
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}