<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RolePermissionMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_superadmin_can_view_role_permission_matrix(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        $response = $this->actingAs($admin)->get(route('roles.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Roles/Index')
            ->has('roles')
            ->has('modules')
            ->has('allPermissions')
        );
    }

    public function test_non_admin_cannot_view_role_permission_matrix(): void
    {
        // Pengkot user without role.manage
        $pengkot = User::factory()->create();
        $pengkot->assignRole('pengurus_cabor');

        $response = $this->actingAs($pengkot)->get(route('roles.index'));
        $response->assertForbidden();

        // Pimpinan user without role.manage
        $pimpinan = User::factory()->create();
        $pimpinan->assignRole('pimpinan');

        $response = $this->actingAs($pimpinan)->get(route('roles.index'));
        $response->assertForbidden();
    }

    public function test_superadmin_can_update_role_permission_matrix(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // Let's grant verifikator some new permission or modify pengurus_cabor
        $payload = [
            'matrix' => [
                'super_admin' => ['dashboard.view'], // Notice: user.manage & role.manage will be preserved by guardrail
                'verifikator' => ['cabor.view', 'klub.view', 'atlet.view', 'verifikasi.view'],
                'pimpinan' => ['dashboard.view', 'laporan.view'],
                'pengurus_cabor' => ['cabor.view', 'atlet.view'],
            ],
        ];

        $response = $this->actingAs($admin)->put(route('roles.update'), $payload);

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('success');

        $verifikatorRole = Role::findByName('verifikator', 'web');
        $this->assertTrue($verifikatorRole->hasPermissionTo('cabor.view'));
        $this->assertTrue($verifikatorRole->hasPermissionTo('verifikasi.view'));
        $this->assertFalse($verifikatorRole->hasPermissionTo('wilayah.view')); // removed

        // Guardrail: Super admin keeps role.manage and user.manage
        $superAdminRole = Role::findByName('super_admin', 'web');
        $this->assertTrue($superAdminRole->hasPermissionTo('role.manage'));
        $this->assertTrue($superAdminRole->hasPermissionTo('user.manage'));

        // Alias sync check: pengkot alias is also synced
        $pengkotRole = Role::findByName('pengkot', 'web');
        $this->assertTrue($pengkotRole->hasPermissionTo('atlet.view'));
        $this->assertFalse($pengkotRole->hasPermissionTo('sarpras.manage'));
    }

    public function test_superadmin_can_reset_roles_to_standard_default(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super_admin');

        // First strip all permissions from verifikator
        $verifikatorRole = Role::findByName('verifikator', 'web');
        $verifikatorRole->syncPermissions([]);
        $this->assertEmpty($verifikatorRole->permissions);

        // Call reset default
        $response = $this->actingAs($admin)->post(route('roles.reset-default'));

        $response->assertRedirect(route('roles.index'));
        $response->assertSessionHas('success');

        // Verify verifikator restored default permissions
        $verifikatorRole->refresh();
        $this->assertTrue($verifikatorRole->hasPermissionTo('verifikasi.view'));
        $this->assertTrue($verifikatorRole->hasPermissionTo('verifikasi.manage'));
        $this->assertTrue($verifikatorRole->hasPermissionTo('dashboard.view'));
    }
}
