<?php

namespace App\Policies;

use App\Models\Organisasi;
use App\Models\User;

class OrganisasiPolicy
{
    /**
     * Sprint 2 1.3 — Organisasi RBAC
     * view: wilayah.view OR organisasi.view (viewer + all)
     * manage: organisasi.manage (super_admin + operator_organisasi per seeder)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('organisasi.view') || $user->can('wilayah.view');
    }

    public function view(User $user, Organisasi $organisasi): bool
    {
        return $user->can('organisasi.view') || $user->can('wilayah.view');
    }

    public function create(User $user): bool
    {
        return $user->can('organisasi.manage');
    }

    public function update(User $user, Organisasi $organisasi): bool
    {
        return $user->can('organisasi.manage');
    }

    public function delete(User $user, Organisasi $organisasi): bool
    {
        return $user->can('organisasi.manage');
    }

    public function restore(User $user, Organisasi $organisasi): bool
    {
        return $user->can('organisasi.manage');
    }

    public function forceDelete(User $user, Organisasi $organisasi): bool
    {
        return $user->can('organisasi.manage');
    }
}
