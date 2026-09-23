<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles/permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Wilayah & Master
            'wilayah.view',
            'wilayah.manage',

            // Organisasi / Cabor / Klub
            'organisasi.view',
            'organisasi.manage',
            'cabor.view',
            'cabor.manage',
            'klub.view',
            'klub.manage',

            // Data Inti
            'atlet.view',
            'atlet.manage',
            'sdm.view',
            'sdm.manage',
            'sarpras.view',
            'sarpras.manage',
            'kejuaraan.view',
            'kejuaraan.manage',
            'prestasi.view',
            'prestasi.manage',
            'pembinaan.view',
            'pembinaan.manage',

            // Verifikasi workflow
            'verifikasi.view',
            'verifikasi.manage',

            // Sistem
            'dashboard.view',
            'laporan.view',
            'gis.view',
            'user.view',
            'user.manage',
            'audit.view',
            'role.manage',

            // Legacy aliases required by spec
            'organisasi.manage',
            'cabor.manage',
            'klub.manage',
            'atlet.manage',
            'sdm.manage',
            'sarpras.manage',
            'kejuaraan.manage',
            'prestasi.manage',
            'pembinaan.manage',
            'verifikasi.manage',
            'dashboard.view',
            'laporan.view',
            'gis.view',
            'user.manage',
        ];

        // Ensure unique
        $permissions = array_unique($permissions);

        foreach ($permissions as $perm) {
            Permission::firstOrCreate([
                'name' => $perm,
                'guard_name' => 'web',
            ]);
        }

        $roles = [
            'super_admin',
            'verifikator',
            'operator_organisasi',
            'operator_klub',
            'viewer',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions
        $superAdmin = Role::where('name', 'super_admin')->first();
        $superAdmin->syncPermissions(Permission::all());

        $verifikator = Role::where('name', 'verifikator')->first();
        $verifikator->syncPermissions([
            'wilayah.view',
            'organisasi.view',
            'cabor.view',
            'klub.view',
            'atlet.view',
            'sdm.view',
            'sarpras.view',
            'kejuaraan.view',
            'prestasi.view',
            'pembinaan.view',
            'verifikasi.view',
            'verifikasi.manage',
            'dashboard.view',
            'laporan.view',
            'gis.view',
            'audit.view',
        ]);

        $operatorOrganisasi = Role::where('name', 'operator_organisasi')->first();
        $operatorOrganisasi->syncPermissions([
            'wilayah.view',
            'organisasi.view',
            'organisasi.manage',
            'cabor.view',
            'cabor.manage',
            'klub.view',
            'klub.manage',
            'atlet.view',
            'atlet.manage',
            'sdm.view',
            'sdm.manage',
            'sarpras.view',
            'sarpras.manage',
            'kejuaraan.view',
            'kejuaraan.manage',
            'prestasi.view',
            'prestasi.manage',
            'pembinaan.view',
            'pembinaan.manage',
            'dashboard.view',
            'laporan.view',
            'gis.view',
        ]);

        $operatorKlub = Role::where('name', 'operator_klub')->first();
        $operatorKlub->syncPermissions([
            'wilayah.view',
            'cabor.view',
            'klub.view',
            'klub.manage',
            'atlet.view',
            'atlet.manage',
            'sdm.view',
            'sdm.manage',
            'sarpras.view',
            'kejuaraan.view',
            'prestasi.view',
            'prestasi.manage',
            'pembinaan.view',
            'dashboard.view',
            'gis.view',
        ]);

        $viewer = Role::where('name', 'viewer')->first();
        $viewer->syncPermissions([
            'wilayah.view',
            'organisasi.view',
            'cabor.view',
            'klub.view',
            'atlet.view',
            'sdm.view',
            'sarpras.view',
            'kejuaraan.view',
            'prestasi.view',
            'pembinaan.view',
            'dashboard.view',
            'laporan.view',
            'gis.view',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
