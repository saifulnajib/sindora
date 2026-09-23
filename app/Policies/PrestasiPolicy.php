<?php

namespace App\Policies;

use App\Models\Prestasi;
use App\Models\User;

class PrestasiPolicy
{
    /**
     * Sprint 5 5.2 — Prestasi RBAC (transaksi linking Atlet-Cabor-Kejuaraan-Medali)
     * view: prestasi.view
     * manage: prestasi.manage (+ scoped for operator_klub to own atlet's klub)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('prestasi.view') || $user->can('prestasi.manage');
    }

    public function view(User $user, Prestasi $prestasi): bool
    {
        return $user->can('prestasi.view') || $user->can('prestasi.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('prestasi.manage');
    }

    public function update(User $user, Prestasi $prestasi): bool
    {
        if (! $user->can('prestasi.manage')) {
            return false;
        }

        if (method_exists($prestasi, 'isTerverifikasi') && $prestasi->isTerverifikasi()) {
            if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                return false;
            }
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            // Prestasi belongs to Atlet, check atlet's klub_id
            $prestasi->loadMissing('atlet');
            if ($prestasi->atlet) {
                return (int) $user->klub_id === (int) $prestasi->atlet->klub_id;
            }

            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id === null) {
            return false;
        }

        return true;
    }

    public function delete(User $user, Prestasi $prestasi): bool
    {
        if (! $user->can('prestasi.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $prestasi->loadMissing('atlet');
            if ($prestasi->atlet) {
                return (int) $user->klub_id === (int) $prestasi->atlet->klub_id;
            }

            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function restore(User $user, Prestasi $prestasi): bool
    {
        if (! $user->can('prestasi.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $prestasi->loadMissing('atlet');
            if ($prestasi->atlet) {
                return (int) $user->klub_id === (int) $prestasi->atlet->klub_id;
            }

            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }

    public function forceDelete(User $user, Prestasi $prestasi): bool
    {
        if (! $user->can('prestasi.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return true;
    }
}
