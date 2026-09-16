<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * User hanya dapat melihat user
     * yang berada di tenant yang sama.
     */
    public function view(User $user, User $targetUser): bool
    {
        return $user->tenant_id !== null
            && $targetUser->tenant_id === $user->tenant_id;
    }

    /**
     * User hanya dapat membuat user
     * untuk tenant miliknya.
     */
    public function create(User $user): bool
    {
        return $user->tenant_id !== null;
    }

    /**
     * User hanya dapat mengubah user
     * dalam tenant yang sama.
     */
    public function update(User $user, User $targetUser): bool
    {
        return $user->tenant_id !== null
            && $targetUser->tenant_id === $user->tenant_id;
    }

    /**
     * User tidak boleh menghapus akun
     * melalui operasi biasa.
     */
    public function delete(User $user, User $targetUser): bool
    {
        return false;
    }

    public function restore(User $user, User $targetUser): bool
    {
        return false;
    }

    /**
     * Force delete tidak diperbolehkan.
     */
    public function forceDelete(User $user, User $targetUser): bool
    {
        return false;
    }
}