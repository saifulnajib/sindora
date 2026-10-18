<?php

namespace Tests\Feature;

use App\Enums\VerificationStatus;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Organisasi;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AtletKategoriFilterDanAutoVerifTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Cabor $caborKoni;
    private Cabor $caborKormi;

    protected function setUp(): void
    {
        parent::setUp();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Role::create(['name' => 'super_admin']);
        Permission::create(['name' => 'atlet.view']);
        Permission::create(['name' => 'atlet.manage']);
        Permission::create(['name' => 'verifikasi.manage']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('super_admin');
        $this->admin->givePermissionTo(['atlet.view', 'atlet.manage', 'verifikasi.manage']);

        $org = Organisasi::create(['nama' => 'KONI Tanjungpinang', 'singkatan' => 'KONI']);
        $this->caborKoni = Cabor::create(['nama' => 'Sepak Bola', 'kategori' => 'koni', 'organisasi_id' => $org->id]);
        $this->caborKormi = Cabor::create(['nama' => 'Senam Tera', 'kategori' => 'kormi', 'organisasi_id' => $org->id]);
    }

    public function test_superadmin_creates_atlet_with_auto_verification(): void
    {
        $response = $this->actingAs($this->admin)->post(route('atlets.store'), [
            'nama' => 'Budi Santoso',
            'nik' => '2171012345678901',
            'jenis_kelamin' => 'L',
            'cabors' => [
                ['cabor_id' => $this->caborKoni->id, 'status_pembinaan' => 'daerah'],
            ],
        ]);

        $response->assertRedirect(route('atlets.index'));

        $atlet = Atlet::where('nama', 'Budi Santoso')->first();
        $this->assertNotNull($atlet);
        $this->assertEquals(VerificationStatus::Terverifikasi, $atlet->verification_status);
        $this->assertNotNull($atlet->verified_at);
        $this->assertEquals($this->admin->id, $atlet->verified_by);
    }

    public function test_atlet_index_can_filter_by_kategori(): void
    {
        // 1 Atlet di KONI
        $atlet1 = Atlet::create([
            'nama' => 'Atlet Prestasi KONI',
            'nik' => '2171011111111111',
            'cabor_id' => $this->caborKoni->id,
            'verification_status' => VerificationStatus::Terverifikasi,
        ]);
        $atlet1->cabors()->attach($this->caborKoni->id);

        // 1 Atlet di KORMI
        $atlet2 = Atlet::create([
            'nama' => 'Atlet Rekreasi KORMI',
            'nik' => '2171022222222222',
            'cabor_id' => $this->caborKormi->id,
            'verification_status' => VerificationStatus::Terverifikasi,
        ]);
        $atlet2->cabors()->attach($this->caborKormi->id);

        // Filter KONI
        $responseKoni = $this->actingAs($this->admin)->get(route('atlets.index', ['kategori' => 'koni']));
        $responseKoni->assertOk();
        $dataKoni = $responseKoni->original->getData()['page']['props']['atlets']['data'];
        $this->assertCount(1, $dataKoni);
        $this->assertEquals('Atlet Prestasi KONI', $dataKoni[0]['nama']);

        // Filter KORMI
        $responseKormi = $this->actingAs($this->admin)->get(route('atlets.index', ['kategori' => 'kormi']));
        $responseKormi->assertOk();
        $dataKormi = $responseKormi->original->getData()['page']['props']['atlets']['data'];
        $this->assertCount(1, $dataKormi);
        $this->assertEquals('Atlet Rekreasi KORMI', $dataKormi[0]['nama']);
    }

    public function test_dashboard_and_landing_reflect_atlet_category_counts(): void
    {
        // Buat atlet KONI
        $atlet = Atlet::create([
            'nama' => 'Bintang Emas',
            'nik' => '2171099999999999',
            'cabor_id' => $this->caborKoni->id,
            'verification_status' => VerificationStatus::Terverifikasi,
        ]);
        $atlet->cabors()->attach($this->caborKoni->id);

        // Landing page check
        $landingRes = $this->get('/');
        $landingRes->assertOk();
        $sebaran = $landingRes->original->getData()['page']['props']['sebaranKategori'];
        $this->assertEquals(1, $sebaran['total_atlet']);

        $koniItem = collect($sebaran['items'])->firstWhere('key', 'koni');
        $this->assertEquals(1, $koniItem['atlet_count']);

        // Dashboard check
        $dashRes = $this->actingAs($this->admin)->get(route('dashboard'));
        $dashRes->assertOk();
        $bidang = $dashRes->original->getData()['page']['props']['bidangOlahraga'];
        $dashKoni = collect($bidang)->firstWhere('key', 'koni');
        $this->assertEquals(1, $dashKoni['atlet_count']);
    }
}
