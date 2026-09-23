<?php

namespace App\Policies;

use App\Models\Kecamatan;
use App\Models\User;

class KecamatanPolicy
{
    /**
     * Sprint 2 1.3 — Wilayah (Kecamatan) RBAC
     * viewAny/view: requires wilayah.view (all authenticated roles except maybe none)
     * manage (create/update/delete/restore/forceDelete): requires wilayah.manage (super_admin only per seeder)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('wilayah.view');
    }

    public function view(User $user, Kecamatan $kecamatan): bool
    {
        return $user->can('wilayah.view');
    }

    public function create(User $user): bool
    {
        return $user->can('wilayah.manage');
    }

    public function update(User $user, Kecamatan $kecamatan): bool
    {
        return $user->can('wilayah.manage');
    }

    public function delete(User $user, Kecamatan $kecamatan): bool
    {
        return $user->can('wilayah.manage');
    }

    public function restore(User $user, Kecamatan $kecamatan): bool
    {
        return $user->can('wilayah.manage');
    }

    public function forceDelete(User $user, Kecamatan $kecamatan): bool
    {
        return $user->can('wilayah.manage');
    }
}
