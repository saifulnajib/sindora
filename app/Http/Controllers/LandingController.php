<?php

namespace App\Http\Controllers;

use App\Enums\Medali;
use App\Enums\TipeSdm;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Kejuaraan;
use App\Models\Prestasi;
use App\Models\Sdm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    public function index(Request $request): Response
    {
        // 1. Overall Statistics (Cached 60s)
        $stats = Cache::remember('landing.stats', 60, function () {
            $totalAtlet = Atlet::terverifikasi()->count();
            $atletPutra = Atlet::terverifikasi()->where('jenis_kelamin', 'L')->count();
            $atletPutri = Atlet::terverifikasi()->where('jenis_kelamin', 'P')->count();

            $totalPelatih = Sdm::terverifikasi()->where('tipe', TipeSdm::Pelatih)->count();
            $pelatihBersertifikat = Sdm::terverifikasi()
                ->where('tipe', TipeSdm::Pelatih)
                ->whereNotNull('nomor_lisensi')
                ->count();

            $totalPrestasi = Prestasi::terverifikasi()->count();
            $medaliEmas = Prestasi::terverifikasi()->where('medali', Medali::Emas)->count();
            $medaliPerak = Prestasi::terverifikasi()->where('medali', Medali::Perak)->count();
            $medaliPerunggu = Prestasi::terverifikasi()->where('medali', Medali::Perunggu)->count();

            $totalCabor = Cabor::count();

            return [
                'atlet_total' => $totalAtlet,
                'atlet_putra' => $atletPutra,
                'atlet_putri' => $atletPutri,
                'pelatih_total' => $totalPelatih,
                'pelatih_bersertifikat' => $pelatihBersertifikat,
                'prestasi_total' => $totalPrestasi,
                'medali_emas' => $medaliEmas,
                'medali_perak' => $medaliPerak,
                'medali_perunggu' => $medaliPerunggu,
                'cabor_total' => $totalCabor,
            ];
        });

        // 2. Statistik per Cabor (Atlet, Pelatih, Prestasi)
        $caborStats = Cache::remember('landing.cabor_stats', 60, function () {
            return Cabor::query()
                ->select('id', 'nama', 'kode')
                ->withCount([
                    'atlets as atlet_count' => fn ($q) => $q->terverifikasi(),
                    'sdms as pelatih_count' => fn ($q) => $q->terverifikasi()->where('tipe', TipeSdm::Pelatih),
                    'prestasis as prestasi_count' => fn ($q) => $q->terverifikasi(),
                    'prestasis as emas_count' => fn ($q) => $q->terverifikasi()->where('medali', Medali::Emas),
                    'prestasis as perak_count' => fn ($q) => $q->terverifikasi()->where('medali', Medali::Perak),
                    'prestasis as perunggu_count' => fn ($q) => $q->terverifikasi()->where('medali', Medali::Perunggu),
                ])
                ->orderByDesc('prestasi_count')
                ->orderBy('nama')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'nama' => $c->nama,
                    'kode' => $c->kode,
                    'atlet_count' => (int) $c->atlet_count,
                    'pelatih_count' => (int) $c->pelatih_count,
                    'prestasi_count' => (int) $c->prestasi_count,
                    'emas_count' => (int) $c->emas_count,
                    'perak_count' => (int) $c->perak_count,
                    'perunggu_count' => (int) $c->perunggu_count,
                ]);
        });

        // 3. Tren Medali Tahunan
        $trenMedali = Cache::remember('landing.tren_medali', 60, function () {
            $rows = Prestasi::terverifikasi()
                ->whereNotNull('medali')
                ->selectRaw('YEAR(COALESCE(tanggal, created_at)) as tahun, medali, COUNT(*) as cnt')
                ->groupBy(DB::raw('YEAR(COALESCE(tanggal, created_at))'), 'medali')
                ->orderBy('tahun')
                ->get();

            $grouped = [];
            foreach ($rows as $r) {
                $tahun = (int) $r->tahun;
                if ($tahun === 0) continue;
                if (!isset($grouped[$tahun])) {
                    $grouped[$tahun] = ['tahun' => $tahun, 'emas' => 0, 'perak' => 0, 'perunggu' => 0, 'total' => 0];
                }
                $medali = is_object($r->medali) ? $r->medali->value : strtolower((string) $r->medali);
                $cnt = (int) $r->cnt;
                if (in_array($medali, ['emas', 'perak', 'perunggu'], true)) {
                    $grouped[$tahun][$medali] += $cnt;
                }
                $grouped[$tahun]['total'] += $cnt;
            }
            ksort($grouped);
            return array_values($grouped);
        });

        // 4. Prestasi Berdasarkan Tingkat Kejuaraan
        $prestasiByTingkat = Cache::remember('landing.prestasi_tingkat', 60, function () {
            $levels = ['internasional', 'nasional', 'provinsi', 'kabupaten_kota', 'kecamatan'];
            $result = [];

            foreach ($levels as $lvl) {
                $count = Prestasi::terverifikasi()
                    ->whereHas('kejuaraan', fn ($q) => $q->where('tingkat', $lvl))
                    ->count();

                $emas = Prestasi::terverifikasi()
                    ->where('medali', Medali::Emas)
                    ->whereHas('kejuaraan', fn ($q) => $q->where('tingkat', $lvl))
                    ->count();

                $label = match ($lvl) {
                    'internasional' => 'Internasional',
                    'nasional' => 'Nasional',
                    'provinsi' => 'Provinsi',
                    'kabupaten_kota' => 'Kabupaten / Kota',
                    'kecamatan' => 'Kecamatan',
                };

                $result[] = [
                    'tingkat' => $lvl,
                    'label' => $label,
                    'total' => $count,
                    'emas' => $emas,
                ];
            }

            return $result;
        });

        // 5. Daftar Prestasi Terbaru
        $recentPrestasis = Prestasi::terverifikasi()
            ->with([
                'atlet:id,nama,foto_path,jenis_kelamin',
                'cabor:id,nama,kode',
                'kejuaraan:id,nama,tingkat,lokasi,tanggal_mulai',
            ])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->take(12)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'atlet_nama' => $p->atlet?->nama ?? 'Tim / Kontingen',
                'atlet_foto' => $p->atlet?->foto_path ? asset('storage/' . $p->atlet->foto_path) : null,
                'cabor_nama' => $p->cabor?->nama ?? '-',
                'cabor_kode' => $p->cabor?->kode ?? '-',
                'kejuaraan_nama' => $p->kejuaraan?->nama ?? '-',
                'kejuaraan_tingkat' => $p->kejuaraan?->tingkat ?? '-',
                'kategori_kelas' => $p->kategori_kelas,
                'medali' => $p->medali instanceof Medali ? $p->medali->value : (string) $p->medali,
                'medali_label' => $p->medali instanceof Medali ? $p->medali->label() : ucfirst((string) $p->medali),
                'peringkat' => $p->peringkat,
                'tanggal' => $p->tanggal ? Carbon::parse($p->tanggal)->translatedFormat('d M Y') : '-',
                'nomor_sertifikat' => $p->nomor_sertifikat,
            ]);

        // 6. Daftar Atlet Unggulan / Terdaftar (Public-Safe, tanpa NIK/No HP)
        $atlets = Atlet::terverifikasi()
            ->with([
                'cabor:id,nama,kode',
                'klub:id,nama',
                'kelurahan.kecamatan:id,nama',
            ])
            ->withCount(['prestasis as prestasi_count' => fn ($q) => $q->terverifikasi()])
            ->orderByDesc('prestasi_count')
            ->orderBy('nama')
            ->take(12)
            ->get()
            ->map(fn ($a) => [
                'id' => $a->id,
                'nama' => $a->nama,
                'jenis_kelamin' => $a->jenis_kelamin,
                'cabor_nama' => $a->cabor?->nama ?? '-',
                'klub_nama' => $a->klub?->nama ?? '-',
                'kecamatan_nama' => $a->kelurahan?->kecamatan?->nama ?? '-',
                'kelas_tanding' => $a->kelas_tanding,
                'status_pembinaan' => $a->status_pembinaan?->value ?? (string) $a->status_pembinaan,
                'prestasi_count' => (int) $a->prestasi_count,
                'foto_url' => $a->foto_path ? asset('storage/' . $a->foto_path) : null,
            ]);

        // 7. Daftar Pelatih Terverifikasi (Public-Safe)
        $pelatihs = Sdm::terverifikasi()
            ->where('tipe', TipeSdm::Pelatih)
            ->with(['cabor:id,nama,kode', 'klub:id,nama'])
            ->withCount(['atlets as binaan_count' => fn ($q) => $q->terverifikasi()])
            ->orderByDesc('binaan_count')
            ->orderBy('nama')
            ->take(12)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'nama' => $s->nama,
                'jenis_kelamin' => $s->jenis_kelamin,
                'cabor_nama' => $s->cabor?->nama ?? '-',
                'klub_nama' => $s->klub?->nama ?? '-',
                'level' => $s->level,
                'spesialisasi' => $s->spesialisasi,
                'nomor_lisensi' => $s->nomor_lisensi ? substr($s->nomor_lisensi, 0, 4) . '****' : null,
                'has_license' => !empty($s->nomor_lisensi),
                'binaan_count' => (int) $s->binaan_count,
                'foto_url' => $s->foto_path ? asset('storage/' . $s->foto_path) : null,
            ]);

        // 8. Cabor list for instant filter
        $caborsList = Cabor::select('id', 'nama', 'kode')->orderBy('nama')->get();

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'stats' => $stats,
            'caborStats' => $caborStats,
            'trenMedali' => $trenMedali,
            'prestasiByTingkat' => $prestasiByTingkat,
            'recentPrestasis' => $recentPrestasis,
            'atlets' => $atlets,
            'pelatihs' => $pelatihs,
            'caborsList' => $caborsList,
        ]);
    }
}
