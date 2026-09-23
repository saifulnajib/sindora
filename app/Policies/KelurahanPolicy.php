<?php

namespace App\Policies;

use App\Models\Kelurahan;
use App\Models\User;

class KelurahanPolicy
{
    /**
     * Sprint 2 1.3 — Wilayah (Kelurahan) RBAC
     * viewAny/view: requires wilayah.view
     * manage: requires wilayah.manage (super_admin only)
     */
    public function viewAny(User $user): bool
    {
        return $user->can('wilayah.view');
    }

    public function view(User $user, Kelurahan $kelurahan): bool
    {
        return $user->can('wilayah.view');
    }

    public function create(User $user): bool
    {
        return $user->can('wilayah.manage');
    }

    public function update(User $user, Kelurahan $kelurahan): bool
    {
        return $user->can('wilayah.manage');
    }

    public function delete(User $user, Kelurahan $kelurahan): bool
    {
        return $user->can('wilayah.manage');
    }

    public function restore(User $user, Kelurahan $kelurahan): bool
    {
        return $user->can('wilayah.manage');
    }

    public function forceDelete(User $user, Kelurahan $kelurahan): bool
    {
        return $user->can('wilayah.manage');
    }
}
