<?php

namespace App\Policies;

use App\Models\Pembinaan;
use App\Models\User;

class PembinaanPolicy
{
    /**
     * Sprint 5 5.3/5.4 — Pembinaan RBAC
     * view: pembinaan.view (super_admin + operator_organisasi + operator_klub view)
     * manage: pembinaan.manage (super_admin + operator_organisasi scoped by organisasi_id or cabor_id, operator_klub cannot manage — only view)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('pembinaan.view') || $user->can('pembinaan.manage')
            || $user->hasRole(['super_admin', 'operator_organisasi', 'operator_klub', 'verifikator', 'viewer']);
    }

    public function view(User $user, Pembinaan $pembinaan): bool
    {
        return $user->can('pembinaan.view') || $user->can('pembinaan.manage');
    }

    public function create(User $user): bool
    {
        // operator_klub explicitly cannot manage pembinaan
        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return $user->can('pembinaan.manage');
    }

    public function update(User $user, Pembinaan $pembinaan): bool
    {
        if (! $user->can('pembinaan.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        if (method_exists($pembinaan, 'isTerverifikasi') && $pembinaan->isTerverifikasi()) {
            if (! $user->hasRole('super_admin') && ! $user->hasRole('verifikator') && ! $user->can('verifikasi.manage')) {
                return false;
            }
        }

        if ($user->hasRole('operator_organisasi') && ($user->organisasi_id !== null || $user->cabor_id !== null)) {
            // Scoped by organisasi_id or cabor_id: if pembinaan has scoped value it must match one of user's scopes
            $orgMatch = $user->organisasi_id !== null && $pembinaan->organisasi_id !== null
                ? (int) $user->organisasi_id === (int) $pembinaan->organisasi_id
                : false;
            $caborMatch = $user->cabor_id !== null && $pembinaan->cabor_id !== null
                ? (int) $user->cabor_id === (int) $pembinaan->cabor_id
                : false;

            // If pembinaan has neither organisasi nor cabor, allow (global program)
            if ($pembinaan->organisasi_id === null && $pembinaan->cabor_id === null) {
                return true;
            }

            // If pembinaan has scope, require at least one match
            if ($pembinaan->organisasi_id !== null || $pembinaan->cabor_id !== null) {
                return $orgMatch || $caborMatch;
            }
        }

        return true;
    }

    public function delete(User $user, Pembinaan $pembinaan): bool
    {
        if (! $user->can('pembinaan.manage')) {
            return false;
        }

        if ($user->hasRole('operator_klub')) {
            return false;
        }

        if ($user->hasRole('operator_organisasi') && ($user->organisasi_id !== null || $user->cabor_id !== null)) {
            $orgMatch = $user->organisasi_id !== null && $pembinaan->organisasi_id !== null
                ? (int) $user->organisasi_id === (int) $pembinaan->organisasi_id
                : false;
            $caborMatch = $user->cabor_id !== null && $pembinaan->cabor_id !== null
                ? (int) $user->cabor_id === (int) $pembinaan->cabor_id
                : false;

            if ($pembinaan->organisasi_id === null && $pembinaan->cabor_id === null) {
                return true;
            }

            if ($pembinaan->organisasi_id !== null || $pembinaan->cabor_id !== null) {
                return $orgMatch || $caborMatch;
            }
        }

        return true;
    }

    public function restore(User $user, Pembinaan $pembinaan): bool
    {
        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return $user->can('pembinaan.manage');
    }

    public function forceDelete(User $user, Pembinaan $pembinaan): bool
    {
        if ($user->hasRole('operator_klub')) {
            return false;
        }

        return $user->can('pembinaan.manage');
    }
}
