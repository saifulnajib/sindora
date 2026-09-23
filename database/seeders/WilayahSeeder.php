<?php

namespace Database\Seeders;

use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Database\Seeder;

class WilayahSeeder extends Seeder
{
    /**
     * Real data Kota Tanjungpinang - 4 Kecamatan, ~18 Kelurahan
     * Sumber: BPS Kota Tanjungpinang / Permendagri
     */
    public function run(): void
    {
        $wilayah = [
            'Bukit Bestari' => [
                ['nama' => 'Sei Jang', 'kode' => '2171011001'],
                ['nama' => 'Tanjung Ayun Sakti', 'kode' => '2171011002'],
                ['nama' => 'Dompak', 'kode' => '2171011003'],
                ['nama' => 'Tanjungpinang Timur', 'kode' => '2171011004'],
                ['nama' => 'Kampung Bulang', 'kode' => '2171011005'],
            ],
            'Tanjungpinang Barat' => [
                ['nama' => 'Kampung Baru', 'kode' => '2171021001'],
                ['nama' => 'Kemboja', 'kode' => '2171021002'],
                ['nama' => 'Bukit Cermin', 'kode' => '2171021003'],
                ['nama' => 'Tanjungpinang Barat', 'kode' => '2171021004'],
            ],
            'Tanjungpinang Timur' => [
                ['nama' => 'Air Raja', 'kode' => '2171031001'],
                ['nama' => 'Batu IX', 'kode' => '2171031002'],
                ['nama' => 'Pinang Kencana', 'kode' => '2171031003'],
                ['nama' => 'Melayu Kota Piring', 'kode' => '2171031004'],
                ['nama' => 'Tanjung Unggat', 'kode' => '2171031005'],
            ],
            'Tanjungpinang Kota' => [
                ['nama' => 'Penyengat', 'kode' => '2171041001'],
                ['nama' => 'Senggarang', 'kode' => '2171041002'],
                ['nama' => 'Kampung Bugis', 'kode' => '2171041003'],
                ['nama' => 'Tanjungpinang Kota', 'kode' => '2171041004'],
            ],
        ];

        foreach ($wilayah as $kecamatanNama => $kelurahans) {
            $kecamatan = Kecamatan::firstOrCreate(
                ['nama' => $kecamatanNama],
                ['kode' => null]
            );

            foreach ($kelurahans as $kel) {
                Kelurahan::firstOrCreate(
                    [
                        'kecamatan_id' => $kecamatan->id,
                        'nama' => $kel['nama'],
                    ],
                    [
                        'kode' => $kel['kode'],
                    ]
                );
            }
        }
    }
}
