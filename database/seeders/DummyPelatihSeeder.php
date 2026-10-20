<?php

namespace Database\Seeders;

use App\Enums\TipeSdm;
use App\Models\Cabor;
use App\Models\Kelurahan;
use App\Models\Sdm;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DummyPelatihSeeder extends Seeder
{
    public function run(): void
    {
        $targetTotal = 424;

        $this->command?->info("Memulai pembuatan {$targetTotal} data dummy pelatih dengan nama Indonesia...");

        // Pastikan data cabor sudah ada
        $cabors = Cabor::whereNull('deleted_at')->orderBy('id')->get();
        if ($cabors->isEmpty()) {
            $this->call(ProductionDataSeeder::class);
            $cabors = Cabor::whereNull('deleted_at')->orderBy('id')->get();
        }

        $kelurahans = Kelurahan::all();
        $kelurahanIds = $kelurahans->isNotEmpty() ? $kelurahans->pluck('id')->toArray() : range(1, 18);

        // Alokasi proporsional pelatih ke setiap cabor agar total pas 424
        $weights = [];
        foreach ($cabors as $c) {
            if ($c->kategori === 'koni') {
                if (in_array($c->nama, ['Sepak Bola', 'Futsal', 'Bulutangkis', 'Atletik', 'Pencak Silat', 'Bola Basket', 'Bola Voli', 'Renang'])) {
                    $weights[$c->id] = 15;
                } elseif (in_array($c->nama, ['Karate', 'Taekwondo', 'Sepak Takraw', 'Menembak', 'Panahan', 'Biliar', 'Dayung', 'Panjat Tebing'])) {
                    $weights[$c->id] = 10;
                } else {
                    $weights[$c->id] = 6;
                }
            } elseif ($c->kategori === 'kormi') {
                $weights[$c->id] = 5;
            } else {
                $weights[$c->id] = 5;
            }
        }

        $totalWeight = array_sum($weights);
        $allocations = [];
        $currentSum = 0;
        foreach ($weights as $id => $w) {
            $cnt = (int) floor(($w / $totalWeight) * $targetTotal);
            $allocations[$id] = max(2, $cnt);
            $currentSum += $allocations[$id];
        }

        // Koreksi sisa agar pas 424
        $rem = $targetTotal - $currentSum;
        $caborIds = array_keys($allocations);
        $idx = 0;
        while ($rem != 0) {
            $cid = $caborIds[$idx % count($caborIds)];
            if ($rem > 0) {
                $allocations[$cid]++;
                $rem--;
            } else {
                if ($allocations[$cid] > 2) {
                    $allocations[$cid]--;
                    $rem++;
                }
            }
            $idx++;
        }

        // Hapus data dummy pelatih lama agar bersih dan tidak duplikat
        Schema::disableForeignKeyConstraints();
        Sdm::where('tipe', 'pelatih')->forceDelete();
        Schema::enableForeignKeyConstraints();

        $levels = ['daerah', 'provinsi', 'nasional', 'internasional'];
        $spesialisasis = [
            'Pelatih Kepala', 'Pelatih Fisik & Kebugaran', 'Pelatih Teknik Dasar',
            'Pelatih Taktik & Strategi', 'Asisten Pelatih', 'Pembinaan Usia Dini',
            'Kondisi Fisik Atlet', 'Pelatih Mental & Motivasi'
        ];

        $now = Carbon::now();
        $createdCount = 0;

        DB::beginTransaction();
        try {
            foreach ($cabors as $cabor) {
                $quota = $allocations[$cabor->id] ?? 0;
                $caborCode = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $cabor->kode ?: $cabor->nama), 0, 4));

                for ($i = 0; $i < $quota; $i++) {
                    $createdCount++;
                    $gender = rand(1, 100) <= 75 ? 'L' : 'P';
                    $kelurahanId = $kelurahanIds[array_rand($kelurahanIds)];

                    $person = IndonesianPersonData::generatePerson($gender, 28, 62, $kelurahanId, true);

                    // Distribusi tanggal expired lisensi untuk dashboard monitoring
                    // 8% expired, 5% kritis (1-30 hari), 10% peringatan (31-90 hari), 77% aman (> 90 hari)
                    $dice = rand(1, 100);
                    if ($dice <= 8) {
                        $expiredAt = (clone $now)->subDays(rand(5, 120));
                    } elseif ($dice <= 13) {
                        $expiredAt = (clone $now)->addDays(rand(2, 28));
                    } elseif ($dice <= 23) {
                        $expiredAt = (clone $now)->addDays(rand(35, 85));
                    } else {
                        $expiredAt = (clone $now)->addDays(rand(120, 1100));
                    }

                    $terbitAt = (clone $expiredAt)->subYears(rand(2, 4))->subMonths(rand(1, 6));

                    Sdm::create([
                        'nama' => $person['nama'],
                        'tipe' => TipeSdm::Pelatih,
                        'nik' => $person['nik'],
                        'tempat_lahir' => $person['tempat_lahir'],
                        'tanggal_lahir' => $person['tanggal_lahir'],
                        'jenis_kelamin' => $person['jenis_kelamin'],
                        'alamat' => $person['alamat'],
                        'no_hp' => $person['no_hp'],
                        'email' => $person['email'],
                        'cabor_id' => $cabor->id,
                        'kelurahan_id' => $person['kelurahan_id'],
                        'nomor_lisensi' => "LP-{$caborCode}-" . sprintf('%04d', rand(100, 9999)),
                        'level' => $levels[array_rand($levels)],
                        'spesialisasi' => $spesialisasis[array_rand($spesialisasis)],
                        'tanggal_terbit' => $terbitAt->format('Y-m-d'),
                        'expired_at' => $expiredAt->format('Y-m-d'),
                        'verification_status' => 'terverifikasi',
                        'verified_at' => $now,
                        'verified_by' => 1,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->command?->info("Berhasil membuat {$createdCount} data pelatih (SDM tipe pelatih) terverifikasi.");
    }
}
