<?php

namespace App\Policies;

use App\Models\Sdm;
use App\Models\User;

class SdmPolicy
{
    /**
     * Sprint 2 1.3 — SDM RBAC (standard resource methods + masking)
     * viewAny/view: sdm.view
     * create/update/delete/restore/forceDelete: sdm.manage (+ scoped for operator_klub)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('sdm.view');
    }

    public function view(User $user, Sdm $sdm): bool
    {
        return $user->can('sdm.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sdm.manage');
    }

    public function update(User $user, Sdm $sdm): bool
    {
        if (! $user->can('sdm.manage')) {
            return false;
        }

        // Sprint 4 4.7 — Terverifikasi guard
        if (method_exists($sdm, 'isTerverifikasi') && $sdm->isTerverifikasi()) {
            if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                return false;
            }
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            return (int) $user->klub_id === (int) $sdm->klub_id;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Sdm $sdm): bool
    {
        if (! $user->can('sdm.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            return (int) $user->klub_id === (int) $sdm->klub_id;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function restore(User $user, Sdm $sdm): bool
    {
        if (! $user->can('sdm.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            return (int) $user->klub_id === (int) $sdm->klub_id;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function forceDelete(User $user, Sdm $sdm): bool
    {
        if (! $user->can('sdm.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view sensitive data of the SDM.
     * Mirrors AtletPolicy: super_admin full, operator_klub only if same klub_id.
     */
    public function viewSensitive(User $user, Sdm $sdm): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null && (int) $user->klub_id === (int) $sdm->klub_id) {
            return true;
        }

        return false;
    }
}
