<?php

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function view(User $user, Tenant $tenant): bool
    {
        return $user->tenant_id !== null
            && $user->tenant_id === $tenant->id;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->tenant_id !== null
            && $user->tenant_id === $tenant->id;
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return false;
    }

    public function restore(User $user, Tenant $tenant): bool
    {
        return false;
    }

    public function forceDelete(User $user, Tenant $tenant): bool
    {
        return false;
    }
}