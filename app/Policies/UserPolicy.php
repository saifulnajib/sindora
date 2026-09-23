<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Sprint 2 1.3 — User RBAC
     * viewAny/view: user.view (super_admin only per seeder, but permission check is source of truth)
     * manage (create/update/delete/restore/forceDelete): user.manage (super_admin only)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('user.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('user.view');
    }

    public function create(User $user): bool
    {
        return $user->can('user.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('user.manage');
    }

    public function delete(User $user, User $model): bool
    {
        // Prevent self-deletion via policy (also handle in controller)
        if ((int) $user->id === (int) $model->id) {
            return false;
        }

        return $user->can('user.manage');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('user.manage');
    }

    public function forceDelete(User $user, User $model): bool
    {
        if ((int) $user->id === (int) $model->id) {
            return false;
        }

        return $user->can('user.manage');
    }
}
