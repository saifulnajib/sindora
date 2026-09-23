<?php

namespace App\Policies;

use App\Models\Atlet;
use App\Models\Sdm;
use App\Models\User;

class AtletPolicy
{
    /**
     * Sprint 2 1.3 — Atlet RBAC (standard resource methods + masking)
     * viewAny/view: atlet.view
     * create/update/delete/restore/forceDelete: atlet.manage (+ scoped for operator_klub to own klub)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('atlet.view');
    }

    public function view(User $user, Atlet $atlet): bool
    {
        return $user->can('atlet.view');
    }

    public function create(User $user): bool
    {
        return $user->can('atlet.manage');
    }

    public function update(User $user, Atlet $atlet): bool
    {
        if (! $user->can('atlet.manage')) {
            return false;
        }

        // Sprint 4 4.7 — Terverifikasi guard
        if (method_exists($atlet, 'isTerverifikasi') && $atlet->isTerverifikasi()) {
            if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                return false;
            }
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            return (int) $user->klub_id === (int) $atlet->klub_id;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id === null) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Atlet $atlet): bool
    {
        if (! $user->can('atlet.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            return (int) $user->klub_id === (int) $atlet->klub_id;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function restore(User $user, Atlet $atlet): bool
    {
        if (! $user->can('atlet.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            return (int) $user->klub_id === (int) $atlet->klub_id;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function forceDelete(User $user, Atlet $atlet): bool
    {
        if (! $user->can('atlet.manage')) {
            return false;
        }

        // Reserve forceDelete for super_admin / operator_organisasi; operator_klub cannot force
        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can view sensitive (unmasked) data of the atlet.
     *
     * Rules (Sprint 1 spec 0.7):
     * - super_admin may always view full data
     * - operator_klub may view full data iff atlet->klub_id === user->klub_id
     * - all other roles -> masked
     */
    public function viewSensitive(User $user, Atlet $atlet): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null && (int) $user->klub_id === (int) $atlet->klub_id) {
            return true;
        }

        return false;
    }

    /**
     * Alias used by Gate::allows('view-sensitive', $sdm) - kept for backward compat.
     */
    public function viewSensitiveSdm(User $user, Sdm $sdm): bool
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
