<?php

namespace App\Http\Controllers;

use App\Http\Requests\Pembinaan\StorePembinaanRequest;
use App\Http\Requests\Pembinaan\UpdatePembinaanRequest;
use App\Http\Resources\PembinaanResource;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Klub;
use App\Models\Organisasi;
use App\Models\Pembinaan;
use App\Models\Sdm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PembinaanController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Pembinaan::class);

        $search = $request->string('search')->toString();
        $tahun = $request->string('tahun_anggaran')->toString();
        $status = $request->string('status')->toString();
        $organisasiId = $request->string('organisasi_id')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $verification = $request->string('verification_status')->toString();

        $query = Pembinaan::query()
            ->with(['organisasi', 'cabor'])
            ->withCount(['atlets', 'klubs', 'sdms'])
            ->when($search, fn ($q) => $q->where('nama_program', 'like', "%{$search}%"))
            ->when($tahun, fn ($q) => $q->where('tahun_anggaran', $tahun))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($organisasiId, fn ($q) => $q->where('organisasi_id', $organisasiId))
            ->when($caborId, fn ($q) => $q->where('cabor_id', $caborId))
            ->when($verification, fn ($q) => $q->where('verification_status', $verification))
            ->orderByDesc('periode_mulai')
            ->orderBy('nama_program');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Pembinaan/Index', [
            'pembinaans' => [
                'data' => PembinaanResource::collection($paginator->items())->resolve(),
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
                'tahun_anggaran' => $tahun,
                'status' => $status,
                'organisasi_id' => $organisasiId,
                'cabor_id' => $caborId,
                'verification_status' => $verification,
            ],
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('pembinaan.manage'),
                'view' => $request->user()->can('pembinaan.view'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Pembinaan::class);

        return Inertia::render('Pembinaan/Form', [
            'pembinaan' => null,
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'atlets' => Atlet::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'sdms' => Sdm::query()->select('id', 'nama', 'tipe', 'klub_id', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StorePembinaanRequest $request): RedirectResponse
    {
        $this->authorize('create', Pembinaan::class);

        $validated = $request->validated();

        $pesertaAtletIds = $validated['peserta_atlet_ids'] ?? [];
        $pesertaKlubIds = $validated['peserta_klub_ids'] ?? [];
        $pesertaSdmIds = $validated['peserta_sdm_ids'] ?? [];

        $data = collect($validated)->except(['peserta_atlet_ids', 'peserta_klub_ids', 'peserta_sdm_ids'])->toArray();

        // verification_status not input — default draft via DB/model
        $pembinaan = Pembinaan::create($data);

        $this->syncPesertaRecords($pembinaan, $pesertaAtletIds, $pesertaKlubIds, $pesertaSdmIds);

        return redirect()->route('pembinaans.index')->with('success', 'Pembinaan berhasil dibuat.');
    }

    public function show(Request $request, Pembinaan $pembinaan): Response
    {
        $this->authorize('view', $pembinaan);

        $pembinaan->load(['organisasi', 'cabor', 'peserta.peserta'])->loadCount(['atlets', 'klubs', 'sdms']);

        return Inertia::render('Pembinaan/Form', [
            'pembinaan' => (new PembinaanResource($pembinaan))->resolve(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'atlets' => Atlet::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'sdms' => Sdm::query()->select('id', 'nama', 'tipe', 'klub_id', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Pembinaan $pembinaan): Response
    {
        $this->authorize('update', $pembinaan);

        $pembinaan->load(['organisasi', 'cabor', 'peserta.peserta'])->loadCount(['atlets', 'klubs', 'sdms']);

        return Inertia::render('Pembinaan/Form', [
            'pembinaan' => (new PembinaanResource($pembinaan))->resolve(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'atlets' => Atlet::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'sdms' => Sdm::query()->select('id', 'nama', 'tipe', 'klub_id', 'cabor_id')->orderBy('nama')->limit(500)->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdatePembinaanRequest $request, Pembinaan $pembinaan): RedirectResponse
    {
        $this->authorize('update', $pembinaan);

        $validated = $request->validated();

        $pesertaAtletIds = $validated['peserta_atlet_ids'] ?? [];
        $pesertaKlubIds = $validated['peserta_klub_ids'] ?? [];
        $pesertaSdmIds = $validated['peserta_sdm_ids'] ?? [];

        $data = collect($validated)->except(['peserta_atlet_ids', 'peserta_klub_ids', 'peserta_sdm_ids'])->toArray();

        $pembinaan->update($data);

        // Sync peserta: delete then re-create
        $this->syncPesertaRecords($pembinaan, $pesertaAtletIds, $pesertaKlubIds, $pesertaSdmIds);

        return redirect()->route('pembinaans.index')->with('success', 'Pembinaan berhasil diperbarui.');
    }

    public function destroy(Request $request, Pembinaan $pembinaan): RedirectResponse
    {
        $this->authorize('delete', $pembinaan);

        $pembinaan->delete();

        return redirect()->route('pembinaans.index')->with('success', 'Pembinaan berhasil dihapus.');
    }

    /**
     * Sprint 5 minimal: separate peserta sync endpoint (also used internally).
     */
    public function syncPeserta(Request $request, Pembinaan $pembinaan): RedirectResponse
    {
        $this->authorize('update', $pembinaan);

        $validated = $request->validate([
            'peserta_atlet_ids' => ['nullable', 'array'],
            'peserta_atlet_ids.*' => ['integer', 'exists:atlets,id'],
            'peserta_klub_ids' => ['nullable', 'array'],
            'peserta_klub_ids.*' => ['integer', 'exists:klubs,id'],
            'peserta_sdm_ids' => ['nullable', 'array'],
            'peserta_sdm_ids.*' => ['integer', 'exists:sdms,id'],
        ]);

        $this->syncPesertaRecords(
            $pembinaan,
            $validated['peserta_atlet_ids'] ?? [],
            $validated['peserta_klub_ids'] ?? [],
            $validated['peserta_sdm_ids'] ?? []
        );

        return redirect()->back()->with('success', 'Peserta pembinaan berhasil diperbarui.');
    }

    private function syncPesertaRecords(Pembinaan $pembinaan, array $atletIds, array $klubIds, array $sdmIds): void
    {
        // Delete existing peserta then re-create
        $pembinaan->peserta()->delete();

        foreach (array_unique($atletIds) as $id) {
            $pembinaan->peserta()->create([
                'peserta_type' => Atlet::class,
                'peserta_id' => $id,
            ]);
        }

        foreach (array_unique($klubIds) as $id) {
            $pembinaan->peserta()->create([
                'peserta_type' => Klub::class,
                'peserta_id' => $id,
            ]);
        }

        foreach (array_unique($sdmIds) as $id) {
            $pembinaan->peserta()->create([
                'peserta_type' => Sdm::class,
                'peserta_id' => $id,
            ]);
        }
    }
}
