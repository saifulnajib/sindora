<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sdm\StoreSdmRequest;
use App\Http\Requests\Sdm\UpdateSdmRequest;
use App\Http\Resources\SdmResource;
use App\Models\Cabor;
use App\Models\Kelurahan;
use App\Models\Klub;
use App\Models\Sdm;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SdmController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Sdm::class);

        $search = $request->string('search')->toString();
        $tipe = $request->string('tipe')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $expired = $request->string('expired')->toString();

        $query = Sdm::query()
            ->with(['cabor', 'klub'])
            ->when($search, fn ($q) => $q->where('nama', 'like', "%{$search}%"))
            ->when($tipe, fn ($q) => $q->where('tipe', $tipe))
            ->when($caborId, fn ($q) => $q->where('cabor_id', $caborId))
            ->when($expired, function ($q) use ($expired) {
                $now = Carbon::now()->startOfDay();
                if ($expired === 'expired') {
                    $q->whereNotNull('expired_at')->whereDate('expired_at', '<=', $now);
                } elseif ($expired === 'kritis') {
                    $q->whereNotNull('expired_at')
                        ->whereDate('expired_at', '>', $now)
                        ->whereDate('expired_at', '<=', $now->copy()->addDays(30));
                } elseif ($expired === 'peringatan') {
                    $q->whereNotNull('expired_at')
                        ->whereDate('expired_at', '>', $now->copy()->addDays(30))
                        ->whereDate('expired_at', '<=', $now->copy()->addDays(90));
                } elseif ($expired === 'aman') {
                    $q->where(function ($qq) use ($now) {
                        $qq->whereNull('expired_at')
                            ->orWhereDate('expired_at', '>', $now->copy()->addDays(90));
                    });
                } elseif ($expired === 'H-90') {
                    $q->whereNotNull('expired_at')
                        ->whereDate('expired_at', '>', $now)
                        ->whereDate('expired_at', '<=', $now->copy()->addDays(90));
                } elseif ($expired === 'H-30') {
                    $q->whereNotNull('expired_at')
                        ->whereDate('expired_at', '>', $now)
                        ->whereDate('expired_at', '<=', $now->copy()->addDays(30));
                }
            })
            ->orderBy('nama');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Sdm/Index', [
            'sdms' => [
                'data' => SdmResource::collection($paginator->items())->resolve(),
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
                'tipe' => $tipe,
                'cabor_id' => $caborId,
                'expired' => $expired,
            ],
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('sdm.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Sdm::class);

        return Inertia::render('Sdm/Form', [
            'sdm' => null,
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreSdmRequest $request): RedirectResponse
    {
        $this->authorize('create', Sdm::class);

        $validated = $request->validated();

        $data = collect($validated)->except(['foto', 'lisensi_nomor', 'lisensi_level', 'lisensi_terbit'])->toArray();

        // Map aliased fields to actual DB columns
        if (isset($validated['lisensi_nomor'])) {
            $data['nomor_lisensi'] = $validated['lisensi_nomor'];
        } elseif (isset($validated['nomor_lisensi'])) {
            $data['nomor_lisensi'] = $validated['nomor_lisensi'];
        }
        if (isset($validated['lisensi_level'])) {
            $data['level'] = $validated['lisensi_level'];
        } elseif (isset($validated['level'])) {
            $data['level'] = $validated['level'];
        }
        if (isset($validated['lisensi_terbit'])) {
            $data['tanggal_terbit'] = $validated['lisensi_terbit'];
        } elseif (isset($validated['tanggal_terbit'])) {
            $data['tanggal_terbit'] = $validated['tanggal_terbit'];
        }

        // Normalize empty strings to null for nullable FK
        foreach (['cabor_id', 'klub_id', 'kelurahan_id'] as $fk) {
            if (array_key_exists($fk, $data) && $data[$fk] === '') {
                $data[$fk] = null;
            }
        }

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('sdm-fotos', 'public');
        }

        Sdm::create($data);

        return redirect()->route('sdms.index')->with('success', 'SDM berhasil dibuat.');
    }

    public function show(Request $request, Sdm $sdm): Response
    {
        $this->authorize('view', $sdm);

        $sdm->load(['cabor', 'klub', 'kelurahan']);

        return Inertia::render('Sdm/Form', [
            'sdm' => (new SdmResource($sdm))->resolve(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Sdm $sdm): Response
    {
        $this->authorize('update', $sdm);

        $sdm->load(['cabor', 'klub', 'kelurahan']);

        return Inertia::render('Sdm/Form', [
            'sdm' => (new SdmResource($sdm))->resolve(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateSdmRequest $request, Sdm $sdm): RedirectResponse
    {
        $this->authorize('update', $sdm);

        $validated = $request->validated();

        $data = collect($validated)->except(['foto', 'lisensi_nomor', 'lisensi_level', 'lisensi_terbit'])->toArray();

        if (array_key_exists('lisensi_nomor', $validated)) {
            $data['nomor_lisensi'] = $validated['lisensi_nomor'];
        } elseif (array_key_exists('nomor_lisensi', $validated)) {
            $data['nomor_lisensi'] = $validated['nomor_lisensi'];
        }
        if (array_key_exists('lisensi_level', $validated)) {
            $data['level'] = $validated['lisensi_level'];
        } elseif (array_key_exists('level', $validated)) {
            $data['level'] = $validated['level'];
        }
        if (array_key_exists('lisensi_terbit', $validated)) {
            $data['tanggal_terbit'] = $validated['lisensi_terbit'];
        } elseif (array_key_exists('tanggal_terbit', $validated)) {
            $data['tanggal_terbit'] = $validated['tanggal_terbit'];
        }

        foreach (['cabor_id', 'klub_id', 'kelurahan_id'] as $fk) {
            if (array_key_exists($fk, $data) && $data[$fk] === '') {
                $data[$fk] = null;
            }
        }

        if ($request->hasFile('foto')) {
            if ($sdm->foto_path) {
                Storage::disk('public')->delete($sdm->foto_path);
            }
            $data['foto_path'] = $request->file('foto')->store('sdm-fotos', 'public');
        }

        $sdm->update($data);

        return redirect()->route('sdms.index')->with('success', 'SDM berhasil diperbarui.');
    }

    public function destroy(Request $request, Sdm $sdm): RedirectResponse
    {
        $this->authorize('delete', $sdm);

        $sdm->delete();

        return redirect()->route('sdms.index')->with('success', 'SDM berhasil dihapus.');
    }
}
