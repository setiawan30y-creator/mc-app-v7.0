<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\User;

class BranchPolicy
{
    /**
     * User hanya dapat melihat cabang
     * yang berada di tenant miliknya.
     */
    public function view(User $user, Branch $branch): bool
    {
        return $user->tenant_id !== null
            && $branch->tenant_id === $user->tenant_id;
    }

    /**
     * User hanya dapat membuat cabang
     * di tenant miliknya.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /**
     * User hanya dapat mengubah cabang
     * di tenant miliknya.
     */
    public function update(User $user, Branch $branch): bool
    {
        return $user->tenant_id !== null
            && $branch->tenant_id === $user->tenant_id;
    }

    /**
     * Cabang operasional tidak boleh dihapus sembarangan.
     */
    public function delete(User $user, Branch $branch): bool
    {
        return false;
    }

    public function restore(User $user, Branch $branch): bool
    {
        return false;
    }

    /**
     * Force delete tidak diperbolehkan.
     */
    public function forceDelete(User $user, Branch $branch): bool
    {
        return false;
    }
}