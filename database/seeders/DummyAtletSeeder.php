<?php

namespace Database\Seeders;

use App\Models\Atlet;
use App\Models\AtletCabor;
use App\Models\Cabor;
use App\Models\Kelurahan;
use App\Models\Sdm;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DummyAtletSeeder extends Seeder
{
    public function run(): void
    {
        $targetTotal = 1702;

        $this->command?->info("Memulai pembuatan {$targetTotal} data dummy atlet (prioritas cabor KONI)...");

        // Pastikan cabor & pelatih sudah ada
        $cabors = Cabor::whereNull('deleted_at')->orderBy('id')->get();
        if ($cabors->isEmpty()) {
            $this->call(ProductionDataSeeder::class);
            $cabors = Cabor::whereNull('deleted_at')->orderBy('id')->get();
        }

        $pelatihs = Sdm::where('tipe', 'pelatih')->whereNull('deleted_at')->get(['id', 'cabor_id']);
        if ($pelatihs->isEmpty()) {
            $this->call(DummyPelatihSeeder::class);
            $pelatihs = Sdm::where('tipe', 'pelatih')->whereNull('deleted_at')->get(['id', 'cabor_id']);
        }

        // Kelompokkan pelatih per cabor
        $pelatihsByCabor = [];
        foreach ($pelatihs as $p) {
            $pelatihsByCabor[$p->cabor_id][] = $p->id;
        }

        $kelurahans = Kelurahan::all();
        $kelurahanIds = $kelurahans->isNotEmpty() ? $kelurahans->pluck('id')->toArray() : range(1, 18);

        // Alokasi atlet per cabor (Dominan KONI: ~1.406 atlet, KORMI: ~242 atlet, DISABILITAS: ~54 atlet)
        $weights = [];
        foreach ($cabors as $c) {
            if ($c->kategori === 'koni') {
                if (in_array($c->nama, ['Sepak Bola', 'Futsal', 'Bulutangkis', 'Atletik', 'Pencak Silat', 'Bola Basket', 'Bola Voli', 'Renang'])) {
                    $weights[$c->id] = 75;
                } elseif (in_array($c->nama, ['Karate', 'Taekwondo', 'Sepak Takraw', 'Menembak', 'Panahan', 'Biliar', 'Dayung', 'Panjat Tebing', 'Catur', 'Tenis Meja'])) {
                    $weights[$c->id] = 38;
                } else {
                    $weights[$c->id] = 20;
                }
            } elseif ($c->kategori === 'kormi') {
                $weights[$c->id] = in_array($c->nama, ['Senam Jantung Sehat', 'Hadang Belon', 'Dansa Berbaris', 'Skateboard']) ? 22 : 14;
            } else { // disabilitas
                $weights[$c->id] = 18;
            }
        }

        $totalWeight = array_sum($weights);
        $allocations = [];
        $currentSum = 0;
        foreach ($weights as $id => $w) {
            $cnt = (int) floor(($w / $totalWeight) * $targetTotal);
            $allocations[$id] = max(5, $cnt);
            $currentSum += $allocations[$id];
        }

        // Koreksi sisa agar pas 1702
        $rem = $targetTotal - $currentSum;
        $caborIds = array_keys($allocations);
        $idx = 0;
        while ($rem != 0) {
            $cid = $caborIds[$idx % count($caborIds)];
            if ($rem > 0) {
                $allocations[$cid]++;
                $rem--;
            } else {
                if ($allocations[$cid] > 5) {
                    $allocations[$cid]--;
                    $rem++;
                }
            }
            $idx++;
        }

        // Hapus data atlet lama
        Schema::disableForeignKeyConstraints();
        AtletCabor::truncate();
        Atlet::truncate();
        Schema::enableForeignKeyConstraints();

        $statuses = ['daerah', 'daerah', 'daerah', 'daerah', 'provinsi', 'nasional'];
        $now = Carbon::now();

        $createdCount = 0;
        $atletCaborRows = [];

        DB::beginTransaction();
        try {
            foreach ($cabors as $cabor) {
                $quota = $allocations[$cabor->id] ?? 0;
                $caborPelatihList = $pelatihsByCabor[$cabor->id] ?? null;

                for ($i = 0; $i < $quota; $i++) {
                    $createdCount++;
                    $gender = rand(1, 100) <= 62 ? 'L' : 'P';
                    $kelurahanId = $kelurahanIds[array_rand($kelurahanIds)];

                    $person = IndonesianPersonData::generatePerson($gender, 13, 29, $kelurahanId, false);

                    $pelatihId = (! empty($caborPelatihList))
                        ? $caborPelatihList[array_rand($caborPelatihList)]
                        : null;

                    $kelasTanding = $this->getKelasTanding($cabor->nama, $gender);
                    $statusPembinaan = $statuses[array_rand($statuses)];
                    $beratBadan = round(rand(460, 880) / 10, 2);
                    $tinggiBadan = round(rand(1550, 1890) / 10, 2);

                    $atlet = Atlet::create([
                        'nama' => $person['nama'],
                        'nik' => $person['nik'],
                        'tempat_lahir' => $person['tempat_lahir'],
                        'tanggal_lahir' => $person['tanggal_lahir'],
                        'jenis_kelamin' => $person['jenis_kelamin'],
                        'alamat' => $person['alamat'],
                        'no_hp' => $person['no_hp'],
                        'email' => $person['email'],
                        'cabor_id' => $cabor->id,
                        'pelatih_id' => $pelatihId,
                        'kelurahan_id' => $person['kelurahan_id'],
                        'kelas_tanding' => $kelasTanding,
                        'status_pembinaan' => $statusPembinaan,
                        'berat_badan' => $beratBadan,
                        'tinggi_badan' => $tinggiBadan,
                        'verification_status' => 'terverifikasi',
                        'verified_at' => $now,
                        'verified_by' => 1,
                    ]);

                    // Pivot atlet_cabor primer
                    $atletCaborRows[] = [
                        'atlet_id' => $atlet->id,
                        'cabor_id' => $cabor->id,
                        'klub_id' => null,
                        'pelatih_id' => $pelatihId,
                        'kelas_tanding' => $kelasTanding,
                        'status_pembinaan' => $statusPembinaan,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    // Tambahkan multi-cabor sekunder untuk sebagian atlet (~60 atlet)
                    if ($createdCount <= 60) {
                        $secondaryCabor = $this->getSecondaryCabor($cabor->id, $cabors);
                        if ($secondaryCabor) {
                            $secPelatih = ! empty($pelatihsByCabor[$secondaryCabor->id])
                                ? $pelatihsByCabor[$secondaryCabor->id][array_rand($pelatihsByCabor[$secondaryCabor->id])]
                                : null;

                            $atletCaborRows[] = [
                                'atlet_id' => $atlet->id,
                                'cabor_id' => $secondaryCabor->id,
                                'klub_id' => null,
                                'pelatih_id' => $secPelatih,
                                'kelas_tanding' => $this->getKelasTanding($secondaryCabor->nama, $gender),
                                'status_pembinaan' => 'daerah',
                                'created_at' => $now,
                                'updated_at' => $now,
                            ];
                        }
                    }
                }
            }

            // Bulk insert pivot atlet_cabor
            foreach (array_chunk($atletCaborRows, 500) as $chunk) {
                DB::table('atlet_cabor')->insert($chunk);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        // Flush cache dashboard & landing agar statistik real-time
        Cache::flush();

        $koniCount = Atlet::whereHas('cabor', fn ($q) => $q->where('kategori', 'koni'))->count();
        $kormiCount = Atlet::whereHas('cabor', fn ($q) => $q->where('kategori', 'kormi'))->count();
        $disabCount = Atlet::whereHas('cabor', fn ($q) => $q->where('kategori', 'disabilitas'))->count();

        $this->command?->info("Berhasil membuat {$createdCount} data atlet terverifikasi.");
        $this->command?->info("Rincian Kategori: KONI={$koniCount}, KORMI={$kormiCount}, DISABILITAS={$disabCount}.");
    }

    protected function getKelasTanding(string $caborNama, string $gender): string
    {
        $suffix = $gender === 'L' ? 'Putra' : 'Putri';

        if (stripos($caborNama, 'Bulu') !== false) {
            $opts = ['Tunggal ' . $suffix, 'Ganda ' . $suffix, 'Ganda Campuran'];
            return $opts[array_rand($opts)];
        }

        if (stripos($caborNama, 'Sepak') !== false || stripos($caborNama, 'Futsal') !== false) {
            $opts = ['Tim Utama ' . $suffix, 'Tim U-19 ' . $suffix, 'Tim U-23 ' . $suffix, 'Divisi Utama'];
            return $opts[array_rand($opts)];
        }

        if (stripos($caborNama, 'Silat') !== false || stripos($caborNama, 'Karate') !== false || stripos($caborNama, 'Taekwondo') !== false || stripos($caborNama, 'Tarung') !== false || stripos($caborNama, 'Muaythai') !== false || stripos($caborNama, 'Gulat') !== false || stripos($caborNama, 'Judo') !== false) {
            $opts = [
                'Tanding Kelas A (45-50 kg) ' . $suffix,
                'Tanding Kelas B (50-55 kg) ' . $suffix,
                'Tanding Kelas C (55-60 kg) ' . $suffix,
                'Tanding Kelas D (60-65 kg) ' . $suffix,
                'Seni Tunggal ' . $suffix,
                'Seni Ganda ' . $suffix,
            ];
            return $opts[array_rand($opts)];
        }

        if (stripos($caborNama, 'Atletik') !== false) {
            $opts = ['Lari 100m ' . $suffix, 'Lari 200m ' . $suffix, 'Lari 400m ' . $suffix, 'Lari 800m ' . $suffix, 'Lompat Jauh ' . $suffix, 'Tolak Peluru ' . $suffix];
            return $opts[array_rand($opts)];
        }

        if (stripos($caborNama, 'Renang') !== false) {
            $opts = ['50m Gaya Bebas ' . $suffix, '100m Gaya Bebas ' . $suffix, '50m Gaya Dada ' . $suffix, '100m Gaya Dada ' . $suffix, '50m Gaya Punggung ' . $suffix];
            return $opts[array_rand($opts)];
        }

        if (stripos($caborNama, 'Catur') !== false) {
            $opts = ['Catur Standar ' . $suffix, 'Catur Cepat ' . $suffix, 'Catur Kilat ' . $suffix];
            return $opts[array_rand($opts)];
        }

        if (stripos($caborNama, 'Panahan') !== false || stripos($caborNama, 'Menembak') !== false) {
            $opts = ['Divisi Recurve 70m ' . $suffix, 'Divisi Compound 50m ' . $suffix, 'Divisi Standar Bow 30m ' . $suffix, 'Air Rifle 10m ' . $suffix];
            return $opts[array_rand($opts)];
        }

        $generalOpts = ['Nomor Perorangan ' . $suffix, 'Nomor Beregu ' . $suffix, 'Kategori Terbuka ' . $suffix];
        return $generalOpts[array_rand($generalOpts)];
    }

    protected function getSecondaryCabor(int $currentCaborId, $cabors)
    {
        $filtered = $cabors->where('id', '!=', $currentCaborId);
        return $filtered->isNotEmpty() ? $filtered->random() : null;
    }
}
