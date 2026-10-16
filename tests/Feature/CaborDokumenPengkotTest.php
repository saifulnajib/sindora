<?php

namespace Tests\Feature;

use App\Models\Cabor;
use App\Models\Organisasi;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CaborDokumenPengkotTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $pengkotUser;
    protected Organisasi $organisasi;
    protected Cabor $caborAssigned;
    protected Cabor $caborOther;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        Storage::fake('public');

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole('super_admin');

        $this->organisasi = Organisasi::create([
            'nama' => 'KONI Kota Tanjungpinang',
            'singkatan' => 'KONI',
            'jenis' => 'KONI',
        ]);

        $this->caborAssigned = Cabor::create([
            'organisasi_id' => $this->organisasi->id,
            'nama' => 'Bulu Tangkis (PBSI)',
            'kode' => 'PBSI',
            'kategori' => 'koni',
        ]);

        $this->caborOther = Cabor::create([
            'organisasi_id' => $this->organisasi->id,
            'nama' => 'Sepak Bola (PSSI)',
            'kode' => 'PSSI',
            'kategori' => 'koni',
        ]);

        $this->pengkotUser = User::factory()->create();
        $this->pengkotUser->assignRole('pengurus_cabor');
        $this->pengkotUser->cabors()->sync([$this->caborAssigned->id]);
    }

    public function test_pengkot_can_upload_logo_and_sk_document_to_assigned_cabor(): void
    {
        $logoFile = UploadedFile::fake()->image('pbsi-logo.png', 200, 200);
        $skFile = UploadedFile::fake()->create('sk-pengkot-pbsi-2026.pdf', 1500, 'application/pdf');

        $payload = [
            'organisasi_id' => $this->organisasi->id,
            'nama' => 'Bulu Tangkis Tanjungpinang (PBSI)',
            'kode' => 'PBSI',
            'kategori' => 'koni',
            'deskripsi' => 'Pengkot PBSI Tanjungpinang masa bakti 2026-2029',
            'sk_nomor' => 'SK-014/PENGPROV-PBSI/2026',
            'sk_tgl_mulai' => '2026-01-01',
            'sk_tgl_selesai' => '2029-01-01',
            'nama_ketua' => 'H. Rahmat Hidayat',
            'kontak_pengurus' => '0812-9876-5432',
            'logo' => $logoFile,
            'sk_file' => $skFile,
        ];

        $response = $this->actingAs($this->pengkotUser)
            ->put(route('cabors.update', $this->caborAssigned->id), $payload);

        $response->assertRedirect(route('cabors.index'));
        $response->assertSessionHas('success');

        $this->caborAssigned->refresh();

        $this->assertEquals('SK-014/PENGPROV-PBSI/2026', $this->caborAssigned->sk_nomor);
        $this->assertEquals('2026-01-01', $this->caborAssigned->sk_tgl_mulai->format('Y-m-d'));
        $this->assertEquals('2029-01-01', $this->caborAssigned->sk_tgl_selesai->format('Y-m-d'));
        $this->assertEquals('H. Rahmat Hidayat', $this->caborAssigned->nama_ketua);
        $this->assertEquals('0812-9876-5432', $this->caborAssigned->kontak_pengurus);
        $this->assertEquals('aktif', $this->caborAssigned->sk_status);

        $this->assertNotNull($this->caborAssigned->logo_path);
        $this->assertNotNull($this->caborAssigned->sk_file_path);

        Storage::disk('public')->assertExists($this->caborAssigned->logo_path);
        Storage::disk('public')->assertExists($this->caborAssigned->sk_file_path);
    }

    public function test_pengkot_cannot_update_unassigned_cabor(): void
    {
        $payload = [
            'organisasi_id' => $this->organisasi->id,
            'nama' => 'Hacked Cabor',
            'sk_nomor' => 'ILLEGAL-SK',
        ];

        $response = $this->actingAs($this->pengkotUser)
            ->put(route('cabors.update', $this->caborOther->id), $payload);

        $response->assertForbidden();
    }

    public function test_pengkot_cannot_modify_core_cabor_information(): void
    {
        $originalNama = $this->caborAssigned->nama;
        $originalKode = $this->caborAssigned->kode;
        $originalOrganisasiId = $this->caborAssigned->organisasi_id;

        $payload = [
            'nama' => 'Nama Yang Diubah Pengkot',
            'kode' => 'KODEBARU',
            'sk_nomor' => 'SK-BARU-2026',
            'nama_ketua' => 'Ketua Baru',
        ];

        $response = $this->actingAs($this->pengkotUser)
            ->put(route('cabors.update', $this->caborAssigned->id), $payload);

        $response->assertRedirect(route('cabors.index'));
        $this->caborAssigned->refresh();

        // Core fields MUST NOT change
        $this->assertEquals($originalNama, $this->caborAssigned->nama);
        $this->assertEquals($originalKode, $this->caborAssigned->kode);
        $this->assertEquals($originalOrganisasiId, $this->caborAssigned->organisasi_id);

        // Legalitas & kepengurusan ARE updated
        $this->assertEquals('SK-BARU-2026', $this->caborAssigned->sk_nomor);
        $this->assertEquals('Ketua Baru', $this->caborAssigned->nama_ketua);
    }

    public function test_cabor_resource_and_show_page_includes_sk_urls_and_status(): void
    {
        $this->caborAssigned->update([
            'sk_nomor' => 'SK-88/KONI/2026',
            'sk_tgl_mulai' => '2026-01-01',
            'sk_tgl_selesai' => '2028-12-31',
            'sk_file_path' => 'cabor-sk/dummy-sk.pdf',
            'logo_path' => 'cabor-logos/dummy-logo.png',
        ]);

        $response = $this->actingAs($this->pengkotUser)->get(route('cabors.show', $this->caborAssigned->id));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Cabor/Form')
            ->where('cabor.sk_nomor', 'SK-88/KONI/2026')
            ->where('cabor.sk_status', 'aktif')
            ->where('cabor.sk_file_url', fn ($val) => ! empty($val))
            ->where('cabor.logo_url', fn ($val) => ! empty($val))
        );
    }

    public function test_cabor_detects_expired_and_warning_sk_status(): void
    {
        // Expired SK
        $expiredCabor = Cabor::create([
            'organisasi_id' => $this->organisasi->id,
            'nama' => 'Cabor Expired',
            'sk_nomor' => 'SK-OLD-2020',
            'sk_tgl_mulai' => '2020-01-01',
            'sk_tgl_selesai' => '2022-01-01',
        ]);

        $this->assertEquals('kadaluarsa', $expiredCabor->sk_status);
        $this->assertStringContainsString('Kedaluwarsa', $expiredCabor->sk_status_label);

        // Warning SK (expires in 10 days)
        $warningCabor = Cabor::create([
            'organisasi_id' => $this->organisasi->id,
            'nama' => 'Cabor Warning',
            'sk_nomor' => 'SK-WARN-2026',
            'sk_tgl_mulai' => now()->subYear()->format('Y-m-d'),
            'sk_tgl_selesai' => now()->addDays(15)->format('Y-m-d'),
        ]);

        $this->assertEquals('segera_berakhir', $warningCabor->sk_status);
        $this->assertStringContainsString('Segera Berakhir', $warningCabor->sk_status_label);
    }
}
