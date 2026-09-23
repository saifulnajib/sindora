<?php

namespace App\Policies;

use App\Models\Sarpras;
use App\Models\User;

class SarprasPolicy
{
    /**
     * Sprint 2 1.3 — Sarpras RBAC
     * view: sarpras.view
     * manage: sarpras.manage (+ scoped for operator_klub to own klub's sarpras)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('sarpras.view');
    }

    public function view(User $user, Sarpras $sarpras): bool
    {
        return $user->can('sarpras.view');
    }

    public function create(User $user): bool
    {
        return $user->can('sarpras.manage');
    }

    public function update(User $user, Sarpras $sarpras): bool
    {
        if (! $user->can('sarpras.manage')) {
            return false;
        }

        // Sprint 4 4.7 — Terverifikasi guard
        if (method_exists($sarpras, 'isTerverifikasi') && $sarpras->isTerverifikasi()) {
            if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                return false;
            }
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            // sarpras may be linked via klub_id; if null, allow? enforce strict: must match klub_id
            if ($sarpras->klub_id !== null) {
                return (int) $user->klub_id === (int) $sarpras->klub_id;
            }

            // If sarpras not linked to a klub, operator_klub should not manage it
            return false;
        }

        return true;
    }

    public function delete(User $user, Sarpras $sarpras): bool
    {
        if (! $user->can('sarpras.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            if ($sarpras->klub_id !== null) {
                return (int) $user->klub_id === (int) $sarpras->klub_id;
            }

            return false;
        }

        // operator_klub with null klub_id should not delete
        if ($user->hasRole('operator_klub') && $user->klub_id === null) {
            return false;
        }

        return true;
    }

    public function restore(User $user, Sarpras $sarpras): bool
    {
        if (! $user->can('sarpras.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            if ($sarpras->klub_id !== null) {
                return (int) $user->klub_id === (int) $sarpras->klub_id;
            }

            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function forceDelete(User $user, Sarpras $sarpras): bool
    {
        if (! $user->can('sarpras.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false; // forceDelete reserved for super_admin/operator_organisasi
        }

        return true;
    }
}
