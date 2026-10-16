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

class CaborKategoriDanDashboardBidangTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Organisasi $org;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin.bidang@example.com',
        ]);
        $this->admin->assignRole('super_admin');

        $this->org = Organisasi::create([
            'nama' => 'KONI Tanjungpinang',
            'singkatan' => 'KONI',
            'jenis' => 'KONI',
        ]);
    }

    public function test_cabor_supports_koni_kormi_and_disabilitas_categories(): void
    {
        $caborKoni = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Pencak Silat',
            'kode' => 'SILAT',
            'kategori' => 'koni',
        ]);

        $caborKormi = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Senam Tera',
            'kode' => 'TERA',
            'kategori' => 'kormi',
        ]);

        $caborDisabilitas = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Goalball',
            'kode' => 'GOAL',
            'kategori' => 'disabilitas',
        ]);

        $this->assertEquals('koni', $caborKoni->kategori);
        $this->assertEquals('KONI', $caborKoni->kategori_label);

        $this->assertEquals('kormi', $caborKormi->kategori);
        $this->assertEquals('KORMI', $caborKormi->kategori_label);

        $this->assertEquals('disabilitas', $caborDisabilitas->kategori);
        $this->assertEquals('DISABILITAS', $caborDisabilitas->kategori_label);
    }

    public function test_cabor_normalizes_legacy_categories(): void
    {
        $caborLegacyPrestasi = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Renang',
            'kode' => 'RNG',
            'kategori' => 'prestasi',
        ]);

        $caborLegacyMassal = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Jalan Sehat',
            'kode' => 'JLN',
            'kategori' => 'massal',
        ]);

        $this->assertEquals('koni', $caborLegacyPrestasi->kategori);
        $this->assertEquals('kormi', $caborLegacyMassal->kategori);
    }

    public function test_cabor_index_can_filter_by_kategori(): void
    {
        Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Sepak Bola',
            'kode' => 'BOLA',
            'kategori' => 'koni',
        ]);

        Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'E-Sport Rekreasi',
            'kode' => 'ESPORT',
            'kategori' => 'kormi',
        ]);

        Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Para Atletik',
            'kode' => 'PATL',
            'kategori' => 'disabilitas',
        ]);

        // Filter koni
        $response = $this->actingAs($this->admin)->get(route('cabors.index', ['kategori' => 'koni']));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Cabor/Index')
            ->has('cabors.data', 1)
            ->where('cabors.data.0.nama', 'Sepak Bola')
            ->where('filters.kategori', 'koni')
        );

        // Filter kormi
        $responseKormi = $this->actingAs($this->admin)->get(route('cabors.index', ['kategori' => 'kormi']));
        $responseKormi->assertOk();
        $responseKormi->assertInertia(fn ($page) => $page
            ->component('Cabor/Index')
            ->has('cabors.data', 1)
            ->where('cabors.data.0.nama', 'E-Sport Rekreasi')
        );

        // Filter disabilitas
        $responseDisabilitas = $this->actingAs($this->admin)->get(route('cabors.index', ['kategori' => 'disabilitas']));
        $responseDisabilitas->assertOk();
        $responseDisabilitas->assertInertia(fn ($page) => $page
            ->component('Cabor/Index')
            ->has('cabors.data', 1)
            ->where('cabors.data.0.nama', 'Para Atletik')
        );
    }

    public function test_dashboard_renders_bidang_olahraga_summary_and_supports_filtering(): void
    {
        $caborKoni = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Karate',
            'kode' => 'KRT',
            'kategori' => 'koni',
        ]);

        $caborKormi = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Barongsai',
            'kode' => 'BRG',
            'kategori' => 'kormi',
        ]);

        $caborDisabilitas = Cabor::create([
            'organisasi_id' => $this->org->id,
            'nama' => 'Boccia',
            'kode' => 'BOC',
            'kategori' => 'disabilitas',
        ]);

        // Create verified klub for KONI
        Klub::create([
            'cabor_id' => $caborKoni->id,
            'nama' => 'Dojo Karate Rajawali',
            'verification_status' => 'terverifikasi',
        ]);

        // General dashboard view
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('bidangOlahraga', 3)
            ->where('activeBidang', 'all')
            ->where('bidangOlahraga.0.key', 'koni')
            ->where('bidangOlahraga.0.cabor_count', 1)
            ->where('bidangOlahraga.0.klub_count', 1)
            ->where('bidangOlahraga.1.key', 'kormi')
            ->where('bidangOlahraga.1.cabor_count', 1)
            ->where('bidangOlahraga.2.key', 'disabilitas')
            ->where('bidangOlahraga.2.cabor_count', 1)
        );

        // Filtered dashboard view for KONI
        $responseKoni = $this->actingAs($this->admin)->get(route('dashboard', ['bidang' => 'koni']));
        $responseKoni->assertOk();
        $responseKoni->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->where('activeBidang', 'koni')
            ->where('widgets.cabor', 1)
            ->where('widgets.klub', 1)
        );
    }
}
