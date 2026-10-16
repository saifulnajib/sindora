<?php

namespace Tests\Feature;

use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Klub;
use App\Models\Organisasi;
use App\Models\Sdm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AtletMultiCaborTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Cabor $cabor1;
    protected Cabor $cabor2;
    protected Klub $klub1;
    protected Klub $klub2;
    protected Sdm $pelatih1;
    protected Sdm $pelatih2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permission
        Permission::create(['name' => 'atlet.manage']);
        Permission::create(['name' => 'atlet.view']);

        $role = Role::create(['name' => 'super_admin']);
        $role->givePermissionTo(['atlet.manage', 'atlet.view']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');

        $org = Organisasi::create([
            'nama' => 'KONI Tanjungpinang',
            'singkatan' => 'KONI',
            'tipe' => 'koni',
        ]);

        $this->cabor1 = Cabor::create([
            'organisasi_id' => $org->id,
            'nama' => 'Renang',
            'kode' => 'RNG',
        ]);

        $this->cabor2 = Cabor::create([
            'organisasi_id' => $org->id,
            'nama' => 'Atletik',
            'kode' => 'ATL',
        ]);

        $this->klub1 = Klub::create([
            'cabor_id' => $this->cabor1->id,
            'nama' => 'Tirta Jaya Club',
        ]);

        $this->klub2 = Klub::create([
            'cabor_id' => $this->cabor2->id,
            'nama' => 'Sprint Runner Club',
        ]);

        $this->pelatih1 = Sdm::create([
            'nama' => 'Pelatih Renang Budi',
            'nik' => '1234567890123456',
            'tipe' => 'pelatih',
            'cabor_id' => $this->cabor1->id,
        ]);

        $this->pelatih2 = Sdm::create([
            'nama' => 'Pelatih Atletik Joko',
            'nik' => '6543210987654321',
            'tipe' => 'pelatih',
            'cabor_id' => $this->cabor2->id,
        ]);
    }

    public function test_can_create_atlet_with_multiple_cabors(): void
    {
        $payload = [
            'nama' => 'Bintang Pratama',
            'nik' => '2171012345678901',
            'jenis_kelamin' => 'L',
            'status_pembinaan' => 'daerah',
            'cabors' => [
                [
                    'cabor_id' => $this->cabor1->id,
                    'klub_id' => $this->klub1->id,
                    'pelatih_id' => $this->pelatih1->id,
                    'kelas_tanding' => 'Gaya Bebas 100m',
                    'status_pembinaan' => 'provinsi',
                ],
                [
                    'cabor_id' => $this->cabor2->id,
                    'klub_id' => $this->klub2->id,
                    'pelatih_id' => $this->pelatih2->id,
                    'kelas_tanding' => 'Lari 100m',
                    'status_pembinaan' => 'daerah',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->post(route('atlets.store'), $payload);

        $response->assertRedirect(route('atlets.index'));
        $response->assertSessionHas('success');

        $atlet = Atlet::where('nama', 'Bintang Pratama')->first();
        $this->assertNotNull($atlet);

        // Verify atlet_cabor pivot records
        $this->assertDatabaseHas('atlet_cabor', [
            'atlet_id' => $atlet->id,
            'cabor_id' => $this->cabor1->id,
            'klub_id' => $this->klub1->id,
            'pelatih_id' => $this->pelatih1->id,
            'kelas_tanding' => 'Gaya Bebas 100m',
        ]);

        $this->assertDatabaseHas('atlet_cabor', [
            'atlet_id' => $atlet->id,
            'cabor_id' => $this->cabor2->id,
            'klub_id' => $this->klub2->id,
            'pelatih_id' => $this->pelatih2->id,
            'kelas_tanding' => 'Lari 100m',
        ]);

        $this->assertCount(2, $atlet->cabors);
    }

    public function test_can_filter_atlet_by_secondary_cabor(): void
    {
        $atlet = Atlet::create([
            'nama' => 'Multi Sport Athlete',
            'nik' => '2171099999999999',
            'cabor_id' => $this->cabor1->id,
            'verification_status' => 'terverifikasi',
        ]);

        $atlet->cabors()->attach([
            $this->cabor1->id => ['klub_id' => $this->klub1->id],
            $this->cabor2->id => ['klub_id' => $this->klub2->id],
        ]);

        // Filter by cabor2 (Atletik)
        $response = $this->actingAs($this->admin)->get(route('atlets.index', ['cabor_id' => $this->cabor2->id]));

        $response->assertOk();
        $response->assertSee('Multi Sport Athlete');
    }

    public function test_can_update_atlet_cabors(): void
    {
        $atlet = Atlet::create([
            'nama' => 'Perenang yang beralih ke Atletik',
            'nik' => '2171088888888888',
            'cabor_id' => $this->cabor1->id,
        ]);

        $atlet->cabors()->attach($this->cabor1->id);

        $updatePayload = [
            'nama' => 'Perenang yang beralih ke Atletik (Updated)',
            'nik' => '2171088888888888',
            'cabors' => [
                [
                    'cabor_id' => $this->cabor2->id,
                    'klub_id' => $this->klub2->id,
                    'pelatih_id' => $this->pelatih2->id,
                    'kelas_tanding' => 'Lari 200m',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->put(route('atlets.update', $atlet->id), $updatePayload);

        $response->assertRedirect(route('atlets.index'));

        $this->assertDatabaseMissing('atlet_cabor', [
            'atlet_id' => $atlet->id,
            'cabor_id' => $this->cabor1->id,
        ]);

        $this->assertDatabaseHas('atlet_cabor', [
            'atlet_id' => $atlet->id,
            'cabor_id' => $this->cabor2->id,
            'kelas_tanding' => 'Lari 200m',
        ]);
    }
}
