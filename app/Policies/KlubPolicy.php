<?php

namespace App\Policies;

use App\Models\Klub;
use App\Models\User;

class KlubPolicy
{
    /**
     * Sprint 2 1.3 — Klub RBAC
     * view: klub.view (all viewers/operators)
     * manage: klub.manage + scope check (operator_klub only own klub where user.klub_id === klub.id)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('klub.view');
    }

    public function view(User $user, Klub $klub): bool
    {
        return $user->can('klub.view');
    }

    public function create(User $user): bool
    {
        return $user->can('klub.manage');
    }

    public function update(User $user, Klub $klub): bool
    {
        if (! $user->can('klub.manage')) {
            return false;
        }

        // Sprint 4 4.7 — Terverifikasi guard: data terverifikasi tidak bisa diedit tanpa re-verifikasi
        if (method_exists($klub, 'isTerverifikasi') && $klub->isTerverifikasi()) {
            if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                return false;
            }
        }

        // Scoped ownership for operator_klub
        if ($user->hasRole('operator_klub')) {
            if ($user->klub_id === null) {
                return false;
            }

            return (int) $user->klub_id === (int) $klub->id;
        }

        return true;
    }

    public function delete(User $user, Klub $klub): bool
    {
        if (! $user->can('klub.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            if ($user->klub_id === null) {
                return false;
            }

            return (int) $user->klub_id === (int) $klub->id;
        }

        return true;
    }

    public function restore(User $user, Klub $klub): bool
    {
        if (! $user->can('klub.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            if ($user->klub_id === null) {
                return false;
            }

            return (int) $user->klub_id === (int) $klub->id;
        }

        return true;
    }

    public function forceDelete(User $user, Klub $klub): bool
    {
        if (! $user->can('klub.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            if ($user->klub_id === null) {
                return false;
            }

            return (int) $user->klub_id === (int) $klub->id;
        }

        // Typically only super_admin should forceDelete; permission check already covers it
        return true;
    }
}
