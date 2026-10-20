<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProductionDataSeeder extends Seeder
{
    public function run(): void
    {
        $orgFile = __DIR__ . '/organisasis_prod.php';
        $caborFile = __DIR__ . '/cabors_prod.php';

        if (! file_exists($orgFile) && file_exists('/Users/saifulnajib/Downloads/organisasis.php')) {
            copy('/Users/saifulnajib/Downloads/organisasis.php', $orgFile);
        }

        if (! file_exists($caborFile) && file_exists('/Users/saifulnajib/Downloads/cabors.php')) {
            copy('/Users/saifulnajib/Downloads/cabors.php', $caborFile);
        }

        if (! file_exists($orgFile) || ! file_exists($caborFile)) {
            $this->command?->warn('File referensi data hosting tidak ditemukan.');
            return;
        }

        require $orgFile;
        require $caborFile;

        Schema::disableForeignKeyConstraints();

        // 1. Seed Organisasis dari hosting/production
        if (isset($organisasis) && is_array($organisasis)) {
            $this->command?->info('Menyinkronkan ' . count($organisasis) . ' data induk organisasi dari hosting...');
            foreach ($organisasis as $orgData) {
                DB::table('organisasis')->updateOrInsert(
                    ['id' => $orgData['id']],
                    $orgData
                );
            }

            // Pastikan organisasi NPCI ada untuk kategori disabilitas
            DB::table('organisasis')->updateOrInsert(
                ['id' => 57],
                [
                    'id' => 57,
                    'nama' => 'National Paralympic Committee Indonesia Kota Tanjungpinang',
                    'singkatan' => 'NPCI',
                    'jenis' => 'DISABILITAS',
                    'alamat' => 'Kota Tanjungpinang',
                    'ketua' => 'Pengurus NPCI Tanjungpinang',
                    'kontak_hp' => '081270000057',
                    'kontak_email' => 'npci.tanjungpinang@gmail.com',
                    'logo_path' => null,
                    'deskripsi' => 'Induk organisasi olahraga disabilitas Kota Tanjungpinang',
                    'verification_status' => 'terverifikasi',
                    'verified_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // 2. Seed Cabors dari hosting/production
        if (isset($cabors) && is_array($cabors)) {
            $this->command?->info('Menyinkronkan ' . count($cabors) . ' data cabang olahraga dari hosting...');
            foreach ($cabors as $caborData) {
                if (! empty($caborData['kategori'])) {
                    $caborData['kategori'] = strtolower($caborData['kategori']);
                }
                DB::table('cabors')->updateOrInsert(
                    ['id' => $caborData['id']],
                    $caborData
                );
            }

            // Tambahkan cabor disabilitas resmi di bawah naungan NPCI
            $disabilitasCabors = [
                ['id' => 56, 'organisasi_id' => 57, 'nama' => 'Para Atletik', 'kode' => 'PARA-ATLETIK', 'kategori' => 'disabilitas'],
                ['id' => 57, 'organisasi_id' => 57, 'nama' => 'Para Bulutangkis', 'kode' => 'PARA-PBSI', 'kategori' => 'disabilitas'],
                ['id' => 58, 'organisasi_id' => 57, 'nama' => 'Para Renang', 'kode' => 'PARA-RENANG', 'kategori' => 'disabilitas'],
            ];

            foreach ($disabilitasCabors as $dc) {
                DB::table('cabors')->updateOrInsert(
                    ['id' => $dc['id']],
                    array_merge($dc, [
                        'verification_status' => 'terverifikasi',
                        'verified_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                );
            }
        }

        Schema::enableForeignKeyConstraints();
        $this->command?->info('Sinkronisasi data cabor dan organisasi berhasil!');
    }
}
