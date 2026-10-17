<?php

namespace Tests\Feature;

use App\Http\Controllers\LandingController;
use App\Http\Resources\AtletResource;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Kejuaraan;
use App\Models\Klub;
use App\Models\Organisasi;
use App\Models\Prestasi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPublicPrivacyAndCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_mask_nama_helper_censors_names_properly(): void
    {
        $this->assertEquals('B**i S*****o', LandingController::maskNama('Budi Santoso'));
        $this->assertEquals('S****l N***b', LandingController::maskNama('Saiful Najib'));
        $this->assertEquals('A*i', LandingController::maskNama('Ali'));
        $this->assertEquals('J*', LandingController::maskNama('Jo'));
        $this->assertEquals('Tim / Kontingen', LandingController::maskNama('Tim / Kontingen'));
        $this->assertEquals('-', LandingController::maskNama(null));

        $this->assertEquals('B**i S*****o', AtletResource::maskNama('Budi Santoso'));
    }

    public function test_landing_page_masks_athlete_names_and_provides_category_distribution(): void
    {
        $org = Organisasi::create([
            'nama' => 'KONI Tanjungpinang',
            'singkatan' => 'KONI',
            'tipe' => 'koni',
        ]);

        $caborKoni = Cabor::create([
            'organisasi_id' => $org->id,
            'nama' => 'Renang Prestasi',
            'kode' => 'RNG',
            'kategori' => 'koni',
            'verification_status' => 'terverifikasi',
        ]);

        $caborKormi = Cabor::create([
            'organisasi_id' => $org->id,
            'nama' => 'Senam Kebugaran',
            'kode' => 'SNM',
            'kategori' => 'kormi',
            'verification_status' => 'terverifikasi',
        ]);

        $caborDisabilitas = Cabor::create([
            'organisasi_id' => $org->id,
            'nama' => 'Para Atletik',
            'kode' => 'PAT',
            'kategori' => 'disabilitas',
            'verification_status' => 'terverifikasi',
        ]);

        // Create verified athletes
        $atlet1 = Atlet::create([
            'nama' => 'Bintang Pratama',
            'nik' => '2171012345678901',
            'jenis_kelamin' => 'L',
            'cabor_id' => $caborKoni->id,
            'verification_status' => 'terverifikasi',
        ]);

        $atlet2 = Atlet::create([
            'nama' => 'Siti Nurhaliza',
            'nik' => '2171012345678902',
            'jenis_kelamin' => 'P',
            'cabor_id' => $caborKormi->id,
            'verification_status' => 'terverifikasi',
        ]);

        $atlet3 = Atlet::create([
            'nama' => 'Ahmad Paralympic',
            'nik' => '2171012345678903',
            'jenis_kelamin' => 'L',
            'cabor_id' => $caborDisabilitas->id,
            'verification_status' => 'terverifikasi',
        ]);

        // Create a kejuaraan and prestasi for atlet1
        $kejuaraan = Kejuaraan::create([
            'nama' => 'Kejuaraan Nasional Renang 2026',
            'tingkat' => 'nasional',
            'tahun' => 2026,
            'verification_status' => 'terverifikasi',
        ]);

        Prestasi::create([
            'atlet_id' => $atlet1->id,
            'cabor_id' => $caborKoni->id,
            'kejuaraan_id' => $kejuaraan->id,
            'medali' => 'emas',
            'verification_status' => 'terverifikasi',
        ]);

        $response = $this->get('/');
        $response->assertOk();

        // Ensure raw full names are NOT exposed in page props
        $response->assertDontSee('Bintang Pratama');
        $response->assertDontSee('Siti Nurhaliza');
        $response->assertDontSee('Ahmad Paralympic');

        // Ensure masked names ARE present
        $response->assertSee('B*****g P*****a');
        $response->assertSee('S**i N*******a');
        $response->assertSee('A***d P********c');

        // Ensure sebaranKategori prop contains all 3 categories with appropriate counts
        $response->assertInertia(fn ($page) => $page
            ->component('Welcome')
            ->has('sebaranKategori.items', 3)
            ->where('sebaranKategori.total_atlet', 3)
            ->where('sebaranKategori.items.0.key', 'koni')
            ->where('sebaranKategori.items.0.atlet_count', 1)
            ->where('sebaranKategori.items.1.key', 'kormi')
            ->where('sebaranKategori.items.1.atlet_count', 1)
            ->where('sebaranKategori.items.2.key', 'disabilitas')
            ->where('sebaranKategori.items.2.atlet_count', 1)
        );
    }
}
