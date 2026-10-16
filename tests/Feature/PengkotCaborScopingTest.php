<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Klub;
use App\Models\Organisasi;
use App\Models\Prestasi;
use App\Models\Sdm;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengkotCaborScopingTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $pengkotUser;
    protected Organisasi $orgKoni;
    protected Cabor $caborSepakBola;
    protected Cabor $caborBasket;
    protected Klub $klubSepakBola;
    protected Klub $klubBasket;
    protected Atlet $atletSepakBola;
    protected Atlet $atletBasket;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->superAdmin = User::factory()->create([
            'email' => 'superadmin.test@example.com',
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->orgKoni = Organisasi::create([
            'nama' => 'KONI Kota Tanjungpinang',
            'singkatan' => 'KONI',
            'jenis' => 'KONI',
        ]);

        $this->caborSepakBola = Cabor::create([
            'organisasi_id' => $this->orgKoni->id,
            'nama' => 'Sepak Bola',
            'kode' => 'SB',
            'kategori' => 'koni',
        ]);

        $this->caborBasket = Cabor::create([
            'organisasi_id' => $this->orgKoni->id,
            'nama' => 'Bola Basket',
            'kode' => 'BB',
            'kategori' => 'koni',
        ]);

        $this->klubSepakBola = Klub::create([
            'cabor_id' => $this->caborSepakBola->id,
            'nama' => 'PS Tanjungpinang',
            'verification_status' => 'terverifikasi',
        ]);

        $this->klubBasket = Klub::create([
            'cabor_id' => $this->caborBasket->id,
            'nama' => 'Tanjungpinang Hoops',
            'verification_status' => 'terverifikasi',
        ]);

        $this->atletSepakBola = Atlet::create([
            'cabor_id' => $this->caborSepakBola->id,
            'klub_id' => $this->klubSepakBola->id,
            'nama' => 'Bambang Pamungkas',
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'L',
            'status_pembinaan' => 'daerah',
            'verification_status' => 'terverifikasi',
        ]);

        $this->atletBasket = Atlet::create([
            'cabor_id' => $this->caborBasket->id,
            'klub_id' => $this->klubBasket->id,
            'nama' => 'Mario Wuysang',
            'nik' => '6543210987654321',
            'jenis_kelamin' => 'L',
            'status_pembinaan' => 'daerah',
            'verification_status' => 'terverifikasi',
        ]);

        // Pengkot user assigned only to Sepak Bola
        $this->pengkotUser = User::factory()->create([
            'email' => 'pengkot.pssi@example.com',
            'name' => 'Pengkot PSSI',
        ]);
        $this->pengkotUser->assignRole('pengurus_cabor');
        $this->pengkotUser->cabors()->attach($this->caborSepakBola->id);
    }

    public function test_superadmin_can_assign_single_and_multiple_cabors_to_pengkot_user(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('users.store'), [
            'name' => 'Pengurus Multi Cabor',
            'email' => 'multi.cabor@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pengurus_cabor',
            'cabor_ids' => [$this->caborSepakBola->id, $this->caborBasket->id],
        ]);

        $response->assertRedirect(route('users.index'));

        $newUser = User::where('email', 'multi.cabor@example.com')->firstOrFail();
        $this->assertTrue($newUser->isPengkot());
        $this->assertEqualsCanonicalizing(
            [$this->caborSepakBola->id, $this->caborBasket->id],
            $newUser->getAssignedCaborIds()
        );
        $this->assertTrue($newUser->managesCabor($this->caborSepakBola->id));
        $this->assertTrue($newUser->managesCabor($this->caborBasket->id));
    }

    public function test_pengkot_user_can_only_see_assigned_cabors_in_index(): void
    {
        $response = $this->actingAs($this->pengkotUser)->get(route('cabors.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Cabor/Index')
            ->has('cabors.data', 1)
            ->where('cabors.data.0.id', $this->caborSepakBola->id)
        );
    }

    public function test_pengkot_user_cannot_view_unassigned_cabor_detail(): void
    {
        // Allowed: assigned cabor
        $this->actingAs($this->pengkotUser)
            ->get(route('cabors.show', $this->caborSepakBola->id))
            ->assertOk();

        // Forbidden: other cabor
        $this->actingAs($this->pengkotUser)
            ->get(route('cabors.show', $this->caborBasket->id))
            ->assertForbidden();
    }

    public function test_pengkot_user_cannot_create_or_delete_cabors(): void
    {
        // Pengkot cannot create cabors (superadmin provisions cabors)
        $this->actingAs($this->pengkotUser)
            ->post(route('cabors.store'), [
                'organisasi_id' => $this->orgKoni->id,
                'nama' => 'Cabor Baru',
                'kode' => 'CB',
                'kategori' => 'koni',
            ])
            ->assertForbidden();

        // Pengkot cannot delete cabors
        $this->actingAs($this->pengkotUser)
            ->delete(route('cabors.destroy', $this->caborSepakBola->id))
            ->assertForbidden();
    }

    public function test_pengkot_user_can_only_see_and_manage_klubs_in_assigned_cabor(): void
    {
        // Index: only see klub in Sepak Bola
        $response = $this->actingAs($this->pengkotUser)->get(route('klubs.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Klub/Index')
            ->has('klubs.data', 1)
            ->where('klubs.data.0.id', $this->klubSepakBola->id)
        );

        // Show: can view assigned cabor's klub
        $this->actingAs($this->pengkotUser)
            ->get(route('klubs.show', $this->klubSepakBola->id))
            ->assertOk();

        // Show: cannot view other cabor's klub
        $this->actingAs($this->pengkotUser)
            ->get(route('klubs.show', $this->klubBasket->id))
            ->assertForbidden();

        // Store: cannot create klub under unassigned cabor
        $this->actingAs($this->pengkotUser)
            ->post(route('klubs.store'), [
                'cabor_id' => $this->caborBasket->id,
                'nama' => 'Klub Basket Liar',
            ])
            ->assertForbidden();

        // Store: can create klub under assigned cabor
        $this->actingAs($this->pengkotUser)
            ->post(route('klubs.store'), [
                'cabor_id' => $this->caborSepakBola->id,
                'nama' => 'Klub Bola Baru',
            ])
            ->assertRedirect(route('klubs.index'));
    }

    public function test_pengkot_user_can_only_see_and_manage_atlets_in_assigned_cabor(): void
    {
        // Index: only see atlet in Sepak Bola
        $response = $this->actingAs($this->pengkotUser)->get(route('atlets.index'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Atlet/Index')
            ->has('atlets.data', 1)
            ->where('atlets.data.0.id', $this->atletSepakBola->id)
        );

        // Show: can view assigned cabor's atlet
        $this->actingAs($this->pengkotUser)
            ->get(route('atlets.show', $this->atletSepakBola->id))
            ->assertOk();

        // Show: cannot view other cabor's atlet
        $this->actingAs($this->pengkotUser)
            ->get(route('atlets.show', $this->atletBasket->id))
            ->assertForbidden();

        // Store: cannot create atlet for unassigned cabor
        $this->actingAs($this->pengkotUser)
            ->post(route('atlets.store'), [
                'nama' => 'Kobe Bryant',
                'nik' => '9998887776665554',
                'jenis_kelamin' => 'L',
                'cabors' => [
                    ['cabor_id' => $this->caborBasket->id, 'status_pembinaan' => 'daerah'],
                ],
            ])
            ->assertForbidden();

        // Store: can create atlet for assigned cabor
        $this->actingAs($this->pengkotUser)
            ->post(route('atlets.store'), [
                'nama' => 'Evan Dimas',
                'nik' => '9998887776665553',
                'jenis_kelamin' => 'L',
                'cabors' => [
                    ['cabor_id' => $this->caborSepakBola->id, 'status_pembinaan' => 'daerah'],
                ],
            ])
            ->assertRedirect(route('atlets.index'));
    }

    public function test_pengkot_dashboard_is_isolated_to_assigned_cabors(): void
    {
        $response = $this->actingAs($this->pengkotUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            // Widgets count for pengkot: only 1 cabor (Sepak Bola), 1 klub, 1 atlet
            ->where('widgets.cabor', 1)
            ->where('widgets.klub', 1)
            ->where('widgets.atlet', 1)
        );
    }

    public function test_pimpinan_and_verifikator_have_monitoring_access_across_all_cabors(): void
    {
        $pimpinan = User::factory()->create(['email' => 'pimpinan.test@example.com']);
        $pimpinan->assignRole('pimpinan');

        $verifikator = User::factory()->create(['email' => 'verifikator.test@example.com']);
        $verifikator->assignRole('verifikator');

        // Pimpinan can see all cabors in index
        $this->actingAs($pimpinan)
            ->get(route('cabors.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('cabors.data', 2));

        // Verifikator can see all cabors in index
        $this->actingAs($verifikator)
            ->get(route('cabors.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->has('cabors.data', 2));
    }
}
