<?php

namespace App\Policies;

use App\Models\Cabor;
use App\Models\User;

class CaborPolicy
{
    /**
     * Sprint 2 1.3 — Cabor RBAC
     * view: cabor.view OR organisasi.view (operator_organisasi manages cabors under its organisasi)
     * manage: cabor.manage (super_admin + operator_organisasi). Scope check (organisasi_id) can be enforced in controller/query level.
     * For now generic can('cabor.manage') — future: verify $user->organisasi_id === $cabor->organisasi_id for operator_organisasi.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('cabor.view') || $user->can('organisasi.view');
    }

    public function view(User $user, Cabor $cabor): bool
    {
        return $user->can('cabor.view') || $user->can('organisasi.view');
    }

    public function create(User $user): bool
    {
        return $user->can('cabor.manage');
    }

    public function update(User $user, Cabor $cabor): bool
    {
        if (! $user->can('cabor.manage')) {
            return false;
        }

        // Optional scope: operator_organisasi should only manage cabors within its organisasi
        if ($user->hasRole('operator_organisasi') && $user->organisasi_id !== null) {
            return (int) $user->organisasi_id === (int) $cabor->organisasi_id;
        }

        return true;
    }

    public function delete(User $user, Cabor $cabor): bool
    {
        if (! $user->can('cabor.manage')) {
            return false;
        }

        if ($user->hasRole('operator_organisasi') && $user->organisasi_id !== null) {
            return (int) $user->organisasi_id === (int) $cabor->organisasi_id;
        }

        return true;
    }

    public function restore(User $user, Cabor $cabor): bool
    {
        return $user->can('cabor.manage');
    }

    public function forceDelete(User $user, Cabor $cabor): bool
    {
        return $user->can('cabor.manage');
    }
}
