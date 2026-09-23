<?php

namespace App\Policies;

use App\Models\User;

/**
 * Generic Wilayah policy helper for Kecamatan/Kelurahan shared logic.
 * Used when authorizing without specific model instance.
 * Real Eloquent policies are KecamatanPolicy & KelurahanPolicy.
 */
class WilayahPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('wilayah.view');
    }

    public function view(User $user): bool
    {
        return $user->can('wilayah.view');
    }

    public function create(User $user): bool
    {
        return $user->can('wilayah.manage');
    }

    public function update(User $user): bool
    {
        return $user->can('wilayah.manage');
    }

    public function delete(User $user): bool
    {
        return $user->can('wilayah.manage');
    }

    public function restore(User $user): bool
    {
        return $user->can('wilayah.manage');
    }

    public function forceDelete(User $user): bool
    {
        return $user->can('wilayah.manage');
    }
}
