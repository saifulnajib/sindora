<?php

namespace App\Http\Controllers;

use App\Http\Requests\Atlet\StoreAtletRequest;
use App\Http\Requests\Atlet\UpdateAtletRequest;
use App\Http\Resources\AtletResource;
use App\Models\Atlet;
use App\Models\AtletCabor;
use App\Models\Cabor;
use App\Models\Kelurahan;
use App\Models\Klub;
use App\Models\Sdm;
use App\Enums\VerificationStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AtletController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Atlet::class);

        $search = $request->string('search')->toString();
        $klubId = $request->string('klub_id')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $kategori = strtolower(trim($request->string('kategori')->toString()));
        $statusPembinaan = $request->string('status_pembinaan')->toString();
        $verificationStatus = $request->string('verification_status')->toString();

        $query = Atlet::query()
            ->with(['atletCabors.cabor', 'atletCabors.klub', 'atletCabors.pelatih', 'klub', 'cabor', 'kelurahan'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                // NIK encrypted, so search only nama + kelas_tanding or cabor nama
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('kelas_tanding', 'like', "%{$search}%")
                    ->orWhereHas('cabors', fn ($c) => $c->where('nama', 'like', "%{$search}%"));
            }))
            ->when($klubId, fn ($q) => $q->where(function ($qq) use ($klubId) {
                $qq->where('klub_id', $klubId)
                    ->orWhereHas('atletCabors', fn ($ac) => $ac->where('klub_id', $klubId));
            }))
            ->when($caborId, fn ($q) => $q->where(function ($qq) use ($caborId) {
                $qq->where('cabor_id', $caborId)
                    ->orWhereHas('cabors', fn ($c) => $c->where('cabors.id', $caborId));
            }))
            ->when($kategori, fn ($q) => $q->where(function ($qq) use ($kategori) {
                $qq->whereHas('cabor', fn ($c) => $c->where('kategori', $kategori))
                    ->orWhereHas('cabors', fn ($c) => $c->where('cabors.kategori', $kategori));
            }))
            ->when($statusPembinaan, fn ($q) => $q->where('status_pembinaan', $statusPembinaan))
            ->when($verificationStatus, fn ($q) => $q->where('verification_status', $verificationStatus))
            ->orderBy('nama');

        $user = $request->user();
        if ($user && $user->isPengkot()) {
            $assignedIds = $user->getAssignedCaborIds();
            $query->where(function ($q) use ($assignedIds) {
                $q->whereIn('cabor_id', $assignedIds)
                    ->orWhereHas('cabors', fn ($c) => $c->whereIn('cabors.id', $assignedIds));
            });
        }

        $paginator = $query->paginate(10)->withQueryString();

        $caborOptions = ($user && $user->isPengkot())
            ? Cabor::query()->whereIn('id', $user->getAssignedCaborIds())->select('id', 'nama', 'kode', 'kategori')->orderBy('nama')->get()
            : Cabor::query()->select('id', 'nama', 'kode', 'kategori')->orderBy('nama')->get();

        $klubOptions = ($user && $user->isPengkot())
            ? Klub::query()->whereIn('cabor_id', $user->getAssignedCaborIds())->select('id', 'nama', 'cabor_id')->orderBy('nama')->get()
            : Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get();

        $kategoriList = [
            ['value' => 'koni', 'label' => 'KONI (Olahraga Prestasi)'],
            ['value' => 'kormi', 'label' => 'KORMI (Olahraga Rekreasi / Masyarakat)'],
            ['value' => 'disabilitas', 'label' => 'DISABILITAS (Olahraga Disabilitas / NPCI)'],
        ];

        return Inertia::render('Atlet/Index', [
            'atlets' => [
                'data' => AtletResource::collection($paginator->items())->resolve(),
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                'links' => [
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ],
            ],
            'filters' => [
                'search' => $search,
                'klub_id' => $klubId,
                'cabor_id' => $caborId,
                'kategori' => $kategori,
                'status_pembinaan' => $statusPembinaan,
                'verification_status' => $verificationStatus,
            ],
            'klubs' => $klubOptions,
            'cabors' => $caborOptions,
            'kategoriList' => $kategoriList,
            'can' => [
                'manage' => $request->user()->can('atlet.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Atlet::class);

        $user = $request->user();
        $assignedIds = ($user && $user->isPengkot()) ? $user->getAssignedCaborIds() : null;

        $caborOptions = $assignedIds !== null
            ? Cabor::query()->whereIn('id', $assignedIds)->select('id', 'nama', 'kode')->orderBy('nama')->get()
            : Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get();

        $klubOptions = $assignedIds !== null
            ? Klub::query()->whereIn('cabor_id', $assignedIds)->select('id', 'nama', 'cabor_id')->orderBy('nama')->get()
            : Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get();

        $pelatihOptions = $assignedIds !== null
            ? Sdm::query()->whereIn('cabor_id', $assignedIds)->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get()
            : Sdm::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get();

        return Inertia::render('Atlet/Form', [
            'atlet' => null,
            'klubs' => $klubOptions,
            'cabors' => $caborOptions,
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'pelatihs' => $pelatihOptions,
            'isEdit' => false,
        ]);
    }

    public function checkNik(Request $request): JsonResponse
    {
        $this->authorize('create', Atlet::class);

        $validated = $request->validate([
            'nik' => ['required', 'string', 'digits:16'],
        ]);

        $atlet = Atlet::findByNik($validated['nik']);

        if (! $atlet) {
            return response()->json([
                'exists' => false,
                'atlet' => null,
            ]);
        }

        $atlet->load(['cabors', 'atletCabors.cabor', 'atletCabors.klub', 'atletCabors.pelatih', 'klub', 'cabor', 'pelatih']);

        $existingCabors = $atlet->cabors->map(function ($c) {
            return [
                'cabor_id' => $c->id,
                'cabor_nama' => $c->nama,
                'cabor_kode' => $c->kode,
                'klub_id' => $c->pivot?->klub_id,
                'klub_nama' => $c->klub?->nama,
                'pelatih_id' => $c->pivot?->pelatih_id,
                'pelatih_nama' => $c->pelatih?->nama,
                'kelas_tanding' => $c->pivot?->kelas_tanding,
                'status_pembinaan' => $c->pivot?->status_pembinaan ?? 'daerah',
            ];
        })->values();

        if ($atlet->atletCabors->isNotEmpty()) {
            $existingCabors = $atlet->atletCabors->map(function ($ac) {
                return [
                    'cabor_id' => $ac->cabor_id,
                    'cabor_nama' => $ac->cabor?->nama,
                    'cabor_kode' => $ac->cabor?->kode,
                    'klub_id' => $ac->klub_id,
                    'klub_nama' => $ac->klub?->nama,
                    'pelatih_id' => $ac->pelatih_id,
                    'pelatih_nama' => $ac->pelatih?->nama,
                    'kelas_tanding' => $ac->kelas_tanding,
                    'status_pembinaan' => $ac->status_pembinaan ?? 'daerah',
                ];
            })->values();
        }

        return response()->json([
            'exists' => true,
            'atlet' => [
                'id' => $atlet->id,
                'nama' => $atlet->nama,
                'nik' => $atlet->nik,
                'tempat_lahir' => $atlet->tempat_lahir,
                'tanggal_lahir' => $atlet->tanggal_lahir?->format('Y-m-d'),
                'jenis_kelamin' => $atlet->jenis_kelamin,
                'alamat' => $atlet->alamat,
                'no_hp' => $atlet->no_hp,
                'email' => $atlet->email,
                'kelurahan_id' => $atlet->kelurahan_id,
                'berat_badan' => $atlet->berat_badan,
                'tinggi_badan' => $atlet->tinggi_badan,
                'foto_url' => $atlet->foto_path ? Storage::url($atlet->foto_path) : null,
                'existing_cabors' => $existingCabors,
                'existing_cabor_ids' => $existingCabors->pluck('cabor_id')->unique()->values()->all(),
            ],
        ]);
    }

    public function store(StoreAtletRequest $request): RedirectResponse
    {
        $this->authorize('create', Atlet::class);

        $validated = $request->validated();
        $cabors = $validated['cabors'] ?? [];

        if ($request->user() && $request->user()->isPengkot()) {
            $assigned = $request->user()->getAssignedCaborIds();
            if (!empty($cabors)) {
                foreach ($cabors as $c) {
                    if (!in_array((int) $c['cabor_id'], $assigned, true)) {
                        abort(403, 'Anda tidak memiliki hak kelola untuk salah satu cabor atlet ini.');
                    }
                }
            } elseif (!empty($validated['cabor_id']) && !in_array((int) $validated['cabor_id'], $assigned, true)) {
                abort(403, 'Anda tidak memiliki hak kelola untuk cabor ini.');
            }
        }

        $existingAtlet = Atlet::findByNik($validated['nik']);
        $user = $request->user();

        // Skenario 1: Atlet sudah terdaftar (Multi-cabor: 1 Atlet HasMany AtletCabor)
        // Kita tidak membuat rekord atlet baru, melainkan menambahkan cabor baru ke atlet tersebut.
        if ($existingAtlet) {
            DB::transaction(function () use ($existingAtlet, $cabors, $user) {
                // Jika atlet sebelumnya masih draft dan diedit/ditambah cabor oleh admin, verifikasi otomatis
                if ($user && ($user->hasRole('super_admin') || $user->can('verifikasi.manage')) && $existingAtlet->verification_status !== VerificationStatus::Terverifikasi) {
                    $existingAtlet->update([
                        'verification_status' => VerificationStatus::Terverifikasi,
                        'verified_by' => $user->id,
                        'verified_at' => now(),
                        'catatan_verifikator' => 'Terverifikasi otomatis (didaftarkan oleh Administrator)',
                    ]);
                }

                foreach ($cabors as $c) {
                    AtletCabor::firstOrCreate([
                        'atlet_id' => $existingAtlet->id,
                        'cabor_id' => $c['cabor_id'],
                    ], [
                        'klub_id' => $c['klub_id'] ?? null,
                        'pelatih_id' => $c['pelatih_id'] ?? null,
                        'kelas_tanding' => $c['kelas_tanding'] ?? null,
                        'status_pembinaan' => $c['status_pembinaan'] ?? 'daerah',
                    ]);
                }
            });

            self::clearCaches();

            return redirect()->route('atlets.index')->with('success', "Cabang olahraga baru berhasil ditambahkan untuk atlet {$existingAtlet->nama}.");
        }

        // Skenario 2: Atlet baru (belum terdaftar)
        $data = collect($validated)->except(['foto', 'cabors'])->toArray();

        // Auto-verifikasi bagi Super Admin / Verifikator yang berwenang
        if ($user && ($user->hasRole('super_admin') || $user->can('verifikasi.manage'))) {
            $data['verification_status'] = VerificationStatus::Terverifikasi;
            $data['verified_by'] = $user->id;
            $data['verified_at'] = now();
            $data['catatan_verifikator'] = 'Terverifikasi otomatis (didaftarkan oleh Administrator)';
        } elseif ($user && $user->isPengkot()) {
            $data['verification_status'] = VerificationStatus::MenungguVerifikasi;
        }

        // Sync first cabor to profile columns for fallback compatibility
        if (!empty($cabors)) {
            $data['cabor_id'] = $cabors[0]['cabor_id'];
            $data['klub_id'] = $cabors[0]['klub_id'] ?? null;
            $data['pelatih_id'] = $cabors[0]['pelatih_id'] ?? null;
            $data['kelas_tanding'] = $cabors[0]['kelas_tanding'] ?? null;
            if (empty($data['status_pembinaan']) && !empty($cabors[0]['status_pembinaan'])) {
                $data['status_pembinaan'] = $cabors[0]['status_pembinaan'];
            }
        }

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('atlet-fotos', 'public');
        }

        DB::transaction(function () use ($data, $cabors) {
            $atlet = Atlet::create($data);

            foreach ($cabors as $c) {
                AtletCabor::create([
                    'atlet_id' => $atlet->id,
                    'cabor_id' => $c['cabor_id'],
                    'klub_id' => $c['klub_id'] ?? null,
                    'pelatih_id' => $c['pelatih_id'] ?? null,
                    'kelas_tanding' => $c['kelas_tanding'] ?? null,
                    'status_pembinaan' => $c['status_pembinaan'] ?? 'daerah',
                ]);
            }
        });

        self::clearCaches();

        return redirect()->route('atlets.index')->with('success', 'Atlet berhasil dibuat.');
    }

    public function show(Request $request, Atlet $atlet): Response
    {
        $this->authorize('view', $atlet);

        $user = $request->user();
        $assignedIds = ($user && $user->isPengkot()) ? $user->getAssignedCaborIds() : null;

        $caborOptions = $assignedIds !== null
            ? Cabor::query()->whereIn('id', $assignedIds)->select('id', 'nama', 'kode')->orderBy('nama')->get()
            : Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get();

        $klubOptions = $assignedIds !== null
            ? Klub::query()->whereIn('cabor_id', $assignedIds)->select('id', 'nama', 'cabor_id')->orderBy('nama')->get()
            : Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get();

        $pelatihOptions = $assignedIds !== null
            ? Sdm::query()->whereIn('cabor_id', $assignedIds)->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get()
            : Sdm::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get();

        $atlet->load(['atletCabors.cabor', 'atletCabors.klub', 'atletCabors.pelatih', 'klub', 'cabor', 'kelurahan', 'pelatih']);

        return Inertia::render('Atlet/Form', [
            'atlet' => (new AtletResource($atlet))->resolve(),
            'klubs' => $klubOptions,
            'cabors' => $caborOptions,
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'pelatihs' => $pelatihOptions,
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Atlet $atlet): Response
    {
        $this->authorize('update', $atlet);

        $user = $request->user();
        $assignedIds = ($user && $user->isPengkot()) ? $user->getAssignedCaborIds() : null;

        $caborOptions = $assignedIds !== null
            ? Cabor::query()->whereIn('id', $assignedIds)->select('id', 'nama', 'kode')->orderBy('nama')->get()
            : Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get();

        $klubOptions = $assignedIds !== null
            ? Klub::query()->whereIn('cabor_id', $assignedIds)->select('id', 'nama', 'cabor_id')->orderBy('nama')->get()
            : Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get();

        $pelatihOptions = $assignedIds !== null
            ? Sdm::query()->whereIn('cabor_id', $assignedIds)->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get()
            : Sdm::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get();

        $atlet->load(['atletCabors.cabor', 'atletCabors.klub', 'atletCabors.pelatih', 'klub', 'cabor', 'kelurahan', 'pelatih']);

        return Inertia::render('Atlet/Form', [
            'atlet' => (new AtletResource($atlet))->resolve(),
            'klubs' => $klubOptions,
            'cabors' => $caborOptions,
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'pelatihs' => $pelatihOptions,
            'isEdit' => true,
        ]);
    }

    public function update(UpdateAtletRequest $request, Atlet $atlet): RedirectResponse
    {
        $this->authorize('update', $atlet);

        $validated = $request->validated();
        $cabors = $validated['cabors'] ?? [];

        if ($request->user() && $request->user()->isPengkot()) {
            $assigned = $request->user()->getAssignedCaborIds();
            if (!empty($cabors)) {
                foreach ($cabors as $c) {
                    if (!in_array((int) $c['cabor_id'], $assigned, true)) {
                        abort(403, 'Anda tidak memiliki hak kelola untuk salah satu cabor atlet ini.');
                    }
                }
            } elseif (!empty($validated['cabor_id']) && !in_array((int) $validated['cabor_id'], $assigned, true)) {
                abort(403, 'Anda tidak memiliki hak kelola untuk cabor ini.');
            }
        }

        $data = collect($validated)->except(['foto', 'cabors'])->toArray();

        // Sync first cabor to profile columns for fallback compatibility
        if (!empty($cabors)) {
            $data['cabor_id'] = $cabors[0]['cabor_id'];
            $data['klub_id'] = $cabors[0]['klub_id'] ?? null;
            $data['pelatih_id'] = $cabors[0]['pelatih_id'] ?? null;
            $data['kelas_tanding'] = $cabors[0]['kelas_tanding'] ?? null;
            if (empty($data['status_pembinaan']) && !empty($cabors[0]['status_pembinaan'])) {
                $data['status_pembinaan'] = $cabors[0]['status_pembinaan'];
            }
        }

        if ($request->hasFile('foto')) {
            if ($atlet->foto_path) {
                Storage::disk('public')->delete($atlet->foto_path);
            }
            $data['foto_path'] = $request->file('foto')->store('atlet-fotos', 'public');
        }

        DB::transaction(function () use ($atlet, $data, $cabors) {
            $atlet->update($data);

            if (!empty($cabors)) {
                $atlet->atletCabors()->delete();

                foreach ($cabors as $c) {
                    AtletCabor::create([
                        'atlet_id' => $atlet->id,
                        'cabor_id' => $c['cabor_id'],
                        'klub_id' => $c['klub_id'] ?? null,
                        'pelatih_id' => $c['pelatih_id'] ?? null,
                        'kelas_tanding' => $c['kelas_tanding'] ?? null,
                        'status_pembinaan' => $c['status_pembinaan'] ?? 'daerah',
                    ]);
                }
            }
        });

        self::clearCaches();

        return redirect()->route('atlets.index')->with('success', 'Atlet berhasil diperbarui.');
    }

    public function destroy(Request $request, Atlet $atlet): RedirectResponse
    {
        $this->authorize('delete', $atlet);

        $atlet->delete();

        self::clearCaches();

        return redirect()->route('atlets.index')->with('success', 'Atlet berhasil dihapus.');
    }

    public static function clearCaches(): void
    {
        Cache::forget('dashboard.bidang_summary');
        Cache::forget('landing.sebaran_kategori');
        Cache::forget('landing.stats');
        Cache::forget('dashboard.widgets.all');
        Cache::forget('dashboard.widgets.koni');
        Cache::forget('dashboard.widgets.kormi');
        Cache::forget('dashboard.widgets.disabilitas');
    }
}
