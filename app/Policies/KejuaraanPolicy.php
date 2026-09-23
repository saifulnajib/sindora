<?php

namespace App\Policies;

use App\Models\Kejuaraan;
use App\Models\User;

class KejuaraanPolicy
{
    /**
     * Sprint 5 5.1 — Kejuaraan RBAC
     * view: kejuaraan.view (super_admin + operator_organisasi + operator_klub view)
     * manage: kejuaraan.manage (super_admin + operator_organisasi manage, scoped by organisasi_id)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('kejuaraan.view') || $user->can('kejuaraan.manage')
            || $user->hasRole(['super_admin', 'operator_organisasi', 'operator_klub', 'verifikator', 'viewer']);
    }

    public function view(User $user, Kejuaraan $kejuaraan): bool
    {
        return $user->can('kejuaraan.view') || $user->can('kejuaraan.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('kejuaraan.manage');
    }

    public function update(User $user, Kejuaraan $kejuaraan): bool
    {
        if (! $user->can('kejuaraan.manage')) {
            return false;
        }

        if (method_exists($kejuaraan, 'isTerverifikasi') && $kejuaraan->isTerverifikasi()) {
            if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                return false;
            }
        }

        if ($user->hasRole('operator_organisasi') && $user->organisasi_id !== null) {
            // If kejuaraan has organisasi_id scope, enforce it; if null allow (global event)
            if ($kejuaraan->organisasi_id !== null) {
                return (int) $user->organisasi_id === (int) $kejuaraan->organisasi_id;
            }
        }

        // operator_klub should not manage kejuaraan at all (permission already denied via can)
        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Kejuaraan $kejuaraan): bool
    {
        if (! $user->can('kejuaraan.manage')) {
            return false;
        }

        if ($user->hasRole('operator_organisasi') && $user->organisasi_id !== null && $kejuaraan->organisasi_id !== null) {
            return (int) $user->organisasi_id === (int) $kejuaraan->organisasi_id;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function restore(User $user, Kejuaraan $kejuaraan): bool
    {
        return $user->can('kejuaraan.manage');
    }

    public function forceDelete(User $user, Kejuaraan $kejuaraan): bool
    {
        return $user->can('kejuaraan.manage');
    }
}
