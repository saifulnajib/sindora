<?php

namespace App\Http\Controllers;

use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Kejuaraan;
use App\Models\Klub;
use App\Models\Pembinaan;
use App\Models\Prestasi;
use App\Models\Sarpras;
use App\Models\Sdm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $rawBidang = strtolower(trim($request->string('bidang')->toString()));
        $activeBidang = in_array($rawBidang, ['koni', 'kormi', 'disabilitas'], true) ? $rawBidang : 'all';

        $user = $request->user();
        $isPengkot = $user && $user->isPengkot();
        $assignedIds = $isPengkot ? $user->getAssignedCaborIds() : null;

        $caborIds = null;
        if ($isPengkot) {
            if ($activeBidang !== 'all') {
                $caborIds = Cabor::where('kategori', $activeBidang)->whereIn('id', $assignedIds)->pluck('id')->toArray();
            } else {
                $caborIds = $assignedIds;
            }
        } else {
            if ($activeBidang !== 'all') {
                $caborIds = Cabor::where('kategori', $activeBidang)->pluck('id')->toArray();
            }
        }

        // Summary for each bidang olahraga (KONI, KORMI, DISABILITAS)
        $cacheKeyBidang = $isPengkot ? 'dashboard.bidang.user.' . $user->id : 'dashboard.bidang_summary';
        $bidangOlahraga = Cache::remember($cacheKeyBidang, 60, function () use ($assignedIds) {
            return $this->getBidangOlahragaSummary($assignedIds);
        });

        // 6.1 Widgets — cached 60 seconds per active bidang, counts only terverifikasi
        $cacheSuffix = $isPengkot ? 'user.' . $user->id . '.' . $activeBidang : $activeBidang;
        $cacheKeyWidgets = 'dashboard.widgets.' . $cacheSuffix;
        $widgets = Cache::remember($cacheKeyWidgets, 60, function () use ($caborIds) {
            if ($caborIds !== null) {
                $cIds = $caborIds;

                return [
                    'klub' => Klub::terverifikasi()->whereIn('cabor_id', $cIds)->count(),
                    'atlet' => Atlet::terverifikasi()->where(function ($q) use ($cIds) {
                        $q->whereIn('cabor_id', $cIds)
                            ->orWhereHas('cabors', fn ($sq) => $sq->whereIn('cabors.id', $cIds))
                            ->orWhereHas('atletCabors', fn ($sq) => $sq->whereIn('cabor_id', $cIds));
                    })->count(),
                    'pelatih' => Sdm::terverifikasi()->where('tipe', 'pelatih')->whereIn('cabor_id', $cIds)->count(),
                    'wasit' => Sdm::terverifikasi()->where('tipe', 'wasit')->whereIn('cabor_id', $cIds)->count(),
                    'tenaga' => Sdm::terverifikasi()->where('tipe', 'tenaga')->whereIn('cabor_id', $cIds)->count(),
                    'sdm_total' => Sdm::terverifikasi()->whereIn('cabor_id', $cIds)->count(),
                    'sarpras' => Sarpras::terverifikasi()->whereIn('cabor_id', $cIds)->count(),
                    'kejuaraan' => Kejuaraan::terverifikasi()->whereIn('cabor_id', $cIds)->count(),
                    'prestasi' => Prestasi::terverifikasi()->whereIn('cabor_id', $cIds)->count(),
                    'pembinaan' => Pembinaan::terverifikasi()->whereIn('cabor_id', $cIds)->count(),
                    'cabor' => count($cIds),
                ];
            }

            return [
                'klub' => Klub::terverifikasi()->count(),
                'atlet' => Atlet::terverifikasi()->count(),
                'pelatih' => Sdm::terverifikasi()->where('tipe', 'pelatih')->count(),
                'wasit' => Sdm::terverifikasi()->where('tipe', 'wasit')->count(),
                'tenaga' => Sdm::terverifikasi()->where('tipe', 'tenaga')->count(),
                'sdm_total' => Sdm::terverifikasi()->count(),
                'sarpras' => Sarpras::terverifikasi()->count(),
                'kejuaraan' => Kejuaraan::terverifikasi()->count(),
                'prestasi' => Prestasi::terverifikasi()->count(),
                'pembinaan' => Pembinaan::terverifikasi()->count(),
                'cabor' => Cabor::count(),
            ];
        });

        $cacheKeyPending = 'dashboard.pending.' . $cacheSuffix;
        $pendingVerifikasi = Cache::remember($cacheKeyPending, 60, function () use ($caborIds) {
            if ($caborIds !== null) {
                $cIds = $caborIds;

                $k = Klub::menungguVerifikasi()->whereIn('cabor_id', $cIds)->count();
                $a = Atlet::menungguVerifikasi()->where(function ($q) use ($cIds) {
                    $q->whereIn('cabor_id', $cIds)->orWhereHas('cabors', fn ($sq) => $sq->whereIn('cabor_id', $cIds));
                })->count();
                $s = Sdm::menungguVerifikasi()->whereIn('cabor_id', $cIds)->count();
                $sp = Sarpras::menungguVerifikasi()->whereIn('cabor_id', $cIds)->count();
                $kj = Kejuaraan::menungguVerifikasi()->whereIn('cabor_id', $cIds)->count();
                $pr = Prestasi::menungguVerifikasi()->whereIn('cabor_id', $cIds)->count();
                $pb = Pembinaan::menungguVerifikasi()->whereIn('cabor_id', $cIds)->count();

                return [
                    'klub' => $k,
                    'atlet' => $a,
                    'sdm' => $s,
                    'sarpras' => $sp,
                    'kejuaraan' => $kj,
                    'prestasi' => $pr,
                    'pembinaan' => $pb,
                    'total' => $k + $a + $s + $sp + $kj + $pr + $pb,
                ];
            }

            return [
                'klub' => Klub::menungguVerifikasi()->count(),
                'atlet' => Atlet::menungguVerifikasi()->count(),
                'sdm' => Sdm::menungguVerifikasi()->count(),
                'sarpras' => Sarpras::menungguVerifikasi()->count(),
                'kejuaraan' => Kejuaraan::menungguVerifikasi()->count(),
                'prestasi' => Prestasi::menungguVerifikasi()->count(),
                'pembinaan' => Pembinaan::menungguVerifikasi()->count(),
                'total' => Klub::menungguVerifikasi()->count()
                    + Atlet::menungguVerifikasi()->count()
                    + Sdm::menungguVerifikasi()->count()
                    + Sarpras::menungguVerifikasi()->count()
                    + Kejuaraan::menungguVerifikasi()->count()
                    + Prestasi::menungguVerifikasi()->count()
                    + Pembinaan::menungguVerifikasi()->count(),
            ];
        });

        // 6.2 Tren Medali — group by YEAR(tanggal) or YEAR(created_at)
        $trenMedali = $this->getTrenMedali($caborIds);

        // 6.3 Rasio Pelatih:Atlet per Cabor
        $rasioPelatihAtlet = $this->getRasioPelatihAtlet($caborIds);

        // 6.4 Distribusi Atlet per Kecamatan / Cabor
        [$distribusiKecamatan, $distribusiCabor] = $this->getDistribusiAtlet($caborIds);

        // 6.5 Cabor Unggulan
        $caborUnggulan = $this->getCaborUnggulan($caborIds);

        // 6.6 Lisensi Kadaluarsa
        $lisensi = $this->getLisensiKadaluarsa($caborIds);

        return Inertia::render('Dashboard', [
            'widgets' => $widgets,
            'pendingVerifikasi' => $pendingVerifikasi,
            'bidangOlahraga' => $bidangOlahraga,
            'activeBidang' => $activeBidang,
            'trenMedali' => $trenMedali,
            'rasioPelatihAtlet' => $rasioPelatihAtlet,
            'distribusiKecamatan' => $distribusiKecamatan,
            'distribusiCabor' => $distribusiCabor,
            'caborUnggulan' => $caborUnggulan,
            'lisensi' => $lisensi,
        ]);
    }

    private function getBidangOlahragaSummary(?array $scopedCaborIds = null): array
    {
        $categories = [
            'koni' => [
                'key' => 'koni',
                'nama' => 'KONI',
                'subnama' => 'Olahraga Prestasi',
                'deskripsi' => 'Komite Olahraga Nasional Indonesia',
                'theme' => 'red',
                'badge' => 'Prestasi',
            ],
            'kormi' => [
                'key' => 'kormi',
                'nama' => 'KORMI',
                'subnama' => 'Olahraga Rekreasi & Masyarakat',
                'deskripsi' => 'Komite Olahraga Masyarakat Indonesia',
                'theme' => 'amber',
                'badge' => 'Rekreasi / Masyarakat',
            ],
            'disabilitas' => [
                'key' => 'disabilitas',
                'nama' => 'DISABILITAS',
                'subnama' => 'Olahraga Disabilitas (NPCI)',
                'deskripsi' => 'National Paralympic Committee Indonesia / Olahraga Khusus',
                'theme' => 'blue',
                'badge' => 'Disabilitas / NPCI',
            ],
        ];

        $summary = [];

        foreach ($categories as $key => $meta) {
            $cQuery = Cabor::where('kategori', $key);
            if ($scopedCaborIds !== null) {
                $cQuery->whereIn('id', $scopedCaborIds);
            }
            $caborIds = $cQuery->pluck('id')->toArray();
            $caborCount = count($caborIds);

            $atletCount = 0;
            $klubCount = 0;
            $pelatihCount = 0;
            $prestasiCount = 0;
            $sarprasCount = 0;
            $kejuaraanCount = 0;

            if ($caborCount > 0) {
                $atletCount = Atlet::terverifikasi()
                    ->where(function ($q) use ($caborIds) {
                        $q->whereIn('cabor_id', $caborIds)
                            ->orWhereHas('cabors', fn ($sq) => $sq->whereIn('cabors.id', $caborIds))
                            ->orWhereHas('atletCabors', fn ($sq) => $sq->whereIn('cabor_id', $caborIds));
                    })
                    ->count();

                $atletPendingCount = Atlet::where('verification_status', '!=', 'terverifikasi')
                    ->where(function ($q) use ($caborIds) {
                        $q->whereIn('cabor_id', $caborIds)
                            ->orWhereHas('cabors', fn ($sq) => $sq->whereIn('cabors.id', $caborIds))
                            ->orWhereHas('atletCabors', fn ($sq) => $sq->whereIn('cabor_id', $caborIds));
                    })
                    ->count();

                $klubCount = Klub::terverifikasi()->whereIn('cabor_id', $caborIds)->count();
                $pelatihCount = Sdm::terverifikasi()->where('tipe', 'pelatih')->whereIn('cabor_id', $caborIds)->count();
                $prestasiCount = Prestasi::terverifikasi()->whereIn('cabor_id', $caborIds)->count();
                $sarprasCount = Sarpras::terverifikasi()->whereIn('cabor_id', $caborIds)->count();
                $kejuaraanCount = Kejuaraan::terverifikasi()->whereIn('cabor_id', $caborIds)->count();
            }

            $summary[] = array_merge($meta, [
                'cabor_count' => $caborCount,
                'atlet_count' => $atletCount,
                'atlet_pending_count' => $atletPendingCount ?? 0,
                'klub_count' => $klubCount,
                'pelatih_count' => $pelatihCount,
                'prestasi_count' => $prestasiCount,
                'sarpras_count' => $sarprasCount,
                'kejuaraan_count' => $kejuaraanCount,
            ]);
        }

        return $summary;
    }

    private function getTrenMedali(?array $caborIds = null): array
    {
        // Only terverifikasi, medali not null, focus on emas/perak/perunggu but include others if present
        $query = Prestasi::terverifikasi()
            ->whereNotNull('medali')
            ->when($caborIds !== null, fn ($q) => $q->whereIn('cabor_id', $caborIds));

        $driver = DB::connection()->getDriverName();
        $yearCol = $driver === 'sqlite'
            ? "strftime('%Y', COALESCE(tanggal, created_at))"
            : "YEAR(COALESCE(tanggal, created_at))";

        $rows = (clone $query)
            ->selectRaw("{$yearCol} as tahun, medali, COUNT(*) as cnt")
            ->groupBy(DB::raw($yearCol), 'medali')
            ->orderBy('tahun')
            ->get();

        // Alternative fallback for MySQL strict: use DB::raw alias tahun can't group by alias without raw
        if ($rows->isEmpty()) {
            $yearColSimple = $driver === 'sqlite' ? "strftime('%Y', tanggal)" : "YEAR(tanggal)";
            $rows = (clone $query)
                ->selectRaw("{$yearColSimple} as tahun, medali, COUNT(*) as cnt")
                ->whereNotNull('tanggal')
                ->groupBy(DB::raw($yearColSimple), 'medali')
                ->orderBy('tahun')
                ->get();
        }

        $grouped = [];
        foreach ($rows as $r) {
            $tahun = (int) $r->tahun;
            if ($tahun === 0) {
                continue;
            }
            if (! isset($grouped[$tahun])) {
                $grouped[$tahun] = ['tahun' => $tahun, 'emas' => 0, 'perak' => 0, 'perunggu' => 0, 'total' => 0];
            }
            $medali = strtolower((string) $r->medali);
            $cnt = (int) $r->cnt;
            if (in_array($medali, ['emas', 'perak', 'perunggu'], true)) {
                $grouped[$tahun][$medali] += $cnt;
            }
            $grouped[$tahun]['total'] += $cnt;
        }

        // Ensure sorted by tahun ascending
        ksort($grouped);

        return array_values($grouped);
    }

    private function getRasioPelatihAtlet(?array $caborIds = null): array
    {
        $cabors = Cabor::query()
            ->select('id', 'nama', 'kode')
            ->when($caborIds !== null, fn ($q) => $q->whereIn('id', $caborIds))
            ->withCount([
                'atlets as atlet_count' => function ($q) {
                    $q->terverifikasi();
                },
                'sdms as pelatih_count' => function ($q) {
                    $q->terverifikasi()->where('tipe', 'pelatih');
                },
            ])
            ->orderBy('nama')
            ->get();

        return $cabors->map(function ($c) {
            $atlet = (int) $c->atlet_count;
            $pelatih = (int) $c->pelatih_count;
            $ratio = null;
            $ratioLabel = '-';
            $ratioPercent = null;
            if ($pelatih > 0) {
                $ratio = $atlet > 0 ? round($atlet / $pelatih, 2) : 0;
                // 1:X format (1 pelatih : X atlet)
                $ratioLabel = $atlet > 0 ? "1:{$ratio}" : '1:0';
                $ratioPercent = $atlet > 0 ? round(($pelatih / $atlet) * 100, 1) : 0;
            } elseif ($atlet > 0) {
                $ratioLabel = '0 pelatih';
            }

            return [
                'cabor_id' => $c->id,
                'cabor_nama' => $c->nama,
                'cabor_kode' => $c->kode,
                'atlet_count' => $atlet,
                'pelatih_count' => $pelatih,
                'ratio' => $ratio, // atlet per pelatih
                'ratio_label' => $ratioLabel,
                'ratio_percent' => $ratioPercent, // pelatih per 100 atlet
            ];
        })->toArray();
    }

    private function getDistribusiAtlet(?array $caborIds = null): array
    {
        // Distribusi per Kecamatan (via kelurahan->kecamatan)
        $kecQuery = DB::table('atlets')
            ->join('kelurahans', 'kelurahans.id', '=', 'atlets.kelurahan_id')
            ->join('kecamatans', 'kecamatans.id', '=', 'kelurahans.kecamatan_id')
            ->where('atlets.verification_status', 'terverifikasi')
            ->whereNull('atlets.deleted_at');

        if ($caborIds !== null) {
            $kecQuery->where(function ($q) use ($caborIds) {
                $q->whereIn('atlets.cabor_id', $caborIds)
                    ->orWhereExists(function ($sub) use ($caborIds) {
                        $sub->select(DB::raw(1))
                            ->from('atlet_cabor')
                            ->whereColumn('atlet_cabor.atlet_id', 'atlets.id')
                            ->whereIn('atlet_cabor.cabor_id', $caborIds);
                    });
            });
        }

        $perKecamatan = $kecQuery
            ->groupBy('kecamatans.id', 'kecamatans.nama')
            ->select('kecamatans.nama as nama', DB::raw('COUNT(*) as count'))
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['nama' => $r->nama, 'count' => (int) $r->count])
            ->toArray();

        // Distribusi per Cabor (via atlet_cabor pivot)
        $caborQuery = DB::table('atlet_cabor')
            ->join('atlets', 'atlets.id', '=', 'atlet_cabor.atlet_id')
            ->join('cabors', 'cabors.id', '=', 'atlet_cabor.cabor_id')
            ->where('atlets.verification_status', 'terverifikasi')
            ->whereNull('atlets.deleted_at');

        if ($caborIds !== null) {
            $caborQuery->whereIn('cabors.id', $caborIds);
        }

        $perCabor = $caborQuery
            ->groupBy('cabors.id', 'cabors.nama')
            ->select('cabors.nama as nama', DB::raw('COUNT(DISTINCT atlets.id) as count'))
            ->orderByDesc('count')
            ->get()
            ->map(fn ($r) => ['nama' => $r->nama, 'count' => (int) $r->count])
            ->toArray();

        return [$perKecamatan, $perCabor];
    }

    private function getCaborUnggulan(?array $caborIds = null): array
    {
        $bobot = config('sindora.bobot_cabor_unggulan', [
            'prestasi_nasional' => 40,
            'prestasi_provinsi' => 25,
            'pembinaan_aktif' => 20,
            'sarpras_dukung' => 10,
            'sdm_bersertifikat' => 5,
        ]);

        $cabors = Cabor::select('id', 'nama', 'kode')
            ->when($caborIds !== null, fn ($q) => $q->whereIn('id', $caborIds))
            ->orderBy('nama')
            ->get();

        $ranking = [];
        foreach ($cabors as $cabor) {
            $caborId = $cabor->id;

            // prestasi_nasional: medali where kejuaraan tingkat nasional/internasional
            $prestasiNasional = Prestasi::terverifikasi()
                ->where('cabor_id', $caborId)
                ->whereNotNull('medali')
                ->whereHas('kejuaraan', function ($q) {
                    $q->whereIn('tingkat', ['nasional', 'internasional']);
                })
                ->count();

            // prestasi_provinsi: provinsi / kabupaten_kota (and optionally kecamatan)
            $prestasiProvinsi = Prestasi::terverifikasi()
                ->where('cabor_id', $caborId)
                ->whereNotNull('medali')
                ->whereHas('kejuaraan', function ($q) {
                    $q->whereIn('tingkat', ['provinsi', 'kabupaten_kota', 'kecamatan']);
                })
                ->count();

            // pembinaan_aktif: jumlah atlet terverifikasi per cabor
            $pembinaanAktif = Atlet::terverifikasi()->where('cabor_id', $caborId)->count();

            // sarpras_dukung: jumlah sarpras per cabor (terverifikasi)
            $sarprasDukung = Sarpras::terverifikasi()->where('cabor_id', $caborId)->count();

            // sdm_bersertifikat: pelatih/wasit dengan lisensi tidak expired (expired_at > now) per cabor
            $sdmBersertifikat = Sdm::terverifikasi()
                ->where('cabor_id', $caborId)
                ->whereIn('tipe', ['pelatih', 'wasit'])
                ->whereNotNull('expired_at')
                ->whereDate('expired_at', '>', Carbon::now()->toDateString())
                ->count();

            $score = ($prestasiNasional * ($bobot['prestasi_nasional'] ?? 40))
                + ($prestasiProvinsi * ($bobot['prestasi_provinsi'] ?? 25))
                + ($pembinaanAktif * ($bobot['pembinaan_aktif'] ?? 20))
                + ($sarprasDukung * ($bobot['sarpras_dukung'] ?? 10))
                + ($sdmBersertifikat * ($bobot['sdm_bersertifikat'] ?? 5));

            $ranking[] = [
                'cabor_id' => $caborId,
                'cabor_nama' => $cabor->nama,
                'cabor_kode' => $cabor->kode,
                'score' => $score,
                'breakdown' => [
                    'prestasi_nasional' => $prestasiNasional,
                    'prestasi_provinsi' => $prestasiProvinsi,
                    'pembinaan_aktif' => $pembinaanAktif,
                    'sarpras_dukung' => $sarprasDukung,
                    'sdm_bersertifikat' => $sdmBersertifikat,
                ],
            ];
        }

        // Sort descending by score
        usort($ranking, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $ranking;
    }

    private function getLisensiKadaluarsa(?array $caborIds = null): array
    {
        $thresholds = config('sindora.license_h.thresholds', [
            'aman' => ['min' => 91, 'label' => 'Aman', 'color' => 'green', 'badge' => 'bg-green-100 text-green-800'],
            'peringatan' => ['min' => 31, 'max' => 90, 'label' => 'Perlu Perpanjangan', 'color' => 'yellow', 'badge' => 'bg-yellow-100 text-yellow-800'],
            'kritis' => ['min' => 1, 'max' => 30, 'label' => 'Segera Perpanjang', 'color' => 'orange', 'badge' => 'bg-orange-100 text-orange-800'],
            'expired' => ['max' => 0, 'label' => 'Kadaluarsa', 'color' => 'red', 'badge' => 'bg-red-100 text-red-800'],
        ]);

        $sdms = Sdm::query()
            ->whereNotNull('expired_at')
            ->when($caborIds !== null, fn ($q) => $q->whereIn('cabor_id', $caborIds))
            ->select('id', 'nama', 'tipe', 'expired_at', 'nomor_lisensi', 'cabor_id')
            ->with('cabor:id,nama')
            ->get();

        $counts = ['aman' => 0, 'peringatan' => 0, 'kritis' => 0, 'expired' => 0, 'total' => $sdms->count()];
        $lists = ['kritis' => [], 'expired' => [], 'peringatan' => []];
        $allWithMeta = [];

        $now = Carbon::now()->startOfDay();

        foreach ($sdms as $sdm) {
            $exp = $sdm->expired_at instanceof Carbon ? $sdm->expired_at->copy()->startOfDay() : Carbon::parse($sdm->expired_at)->startOfDay();
            $days = (int) $now->diffInDays($exp, false);

            if ($days <= 0) {
                $status = 'expired';
                $label = $thresholds['expired']['label'] ?? 'Kadaluarsa';
                $badge = $thresholds['expired']['badge'] ?? 'bg-red-100 text-red-800';
                $color = $thresholds['expired']['color'] ?? 'red';
            } elseif ($days >= 1 && $days <= 30) {
                $status = 'kritis';
                $label = $thresholds['kritis']['label'] ?? 'Segera Perpanjang';
                $badge = $thresholds['kritis']['badge'] ?? 'bg-orange-100 text-orange-800';
                $color = $thresholds['kritis']['color'] ?? 'orange';
            } elseif ($days >= 31 && $days <= 90) {
                $status = 'peringatan';
                $label = $thresholds['peringatan']['label'] ?? 'Perlu Perpanjangan';
                $badge = $thresholds['peringatan']['badge'] ?? 'bg-yellow-100 text-yellow-800';
                $color = $thresholds['peringatan']['color'] ?? 'yellow';
            } else {
                $status = 'aman';
                $label = $thresholds['aman']['label'] ?? 'Aman';
                $badge = $thresholds['aman']['badge'] ?? 'bg-green-100 text-green-800';
                $color = $thresholds['aman']['color'] ?? 'green';
            }

            $counts[$status]++;

            $item = [
                'id' => $sdm->id,
                'nama' => $sdm->nama,
                'tipe' => $sdm->tipe instanceof \BackedEnum ? $sdm->tipe->value : (string) $sdm->tipe,
                'expired_at' => $exp->format('Y-m-d'),
                'days_until_expired' => $days,
                'status' => $status,
                'label' => $label,
                'badge' => $badge,
                'color' => $color,
                'nomor_lisensi' => $sdm->nomor_lisensi,
                'cabor' => $sdm->cabor ? $sdm->cabor->nama : null,
            ];

            $allWithMeta[] = $item;
            if (in_array($status, ['kritis', 'expired', 'peringatan'], true) && count($lists[$status]) < 5) {
                $lists[$status][] = $item;
            }
        }

        // Sort critical/expired by most urgent (days ascending)
        foreach (['kritis', 'expired', 'peringatan'] as $k) {
            usort($lists[$k], fn ($a, $b) => $a['days_until_expired'] <=> $b['days_until_expired']);
        }

        // Also provide full sorted list for potential frontend use (limit 20 most urgent)
        usort($allWithMeta, fn ($a, $b) => $a['days_until_expired'] <=> $b['days_until_expired']);
        $topUrgent = array_slice(array_filter($allWithMeta, fn ($i) => $i['status'] !== 'aman'), 0, 10);

        return [
            'counts' => $counts,
            'thresholds' => $thresholds,
            'kritis' => $lists['kritis'],
            'expired' => $lists['expired'],
            'peringatan' => $lists['peringatan'],
            'top_urgent' => $topUrgent,
        ];
    }
}
