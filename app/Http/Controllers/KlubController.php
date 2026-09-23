<?php

namespace App\Http\Controllers;

use App\Http\Requests\Klub\StoreKlubRequest;
use App\Http\Requests\Klub\UpdateKlubRequest;
use App\Http\Resources\KlubResource;
use App\Models\Cabor;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Klub;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class KlubController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Klub::class);

        $search = $request->string('search')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $kelurahanId = $request->string('kelurahan_id')->toString();
        $kecamatanId = $request->string('kecamatan_id')->toString();

        $query = Klub::query()
            ->with(['cabor', 'kelurahan', 'kecamatan'])
            ->withCount(['atlets'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('ketua', 'like', "%{$search}%");
            }))
            ->when($caborId, fn ($q) => $q->where('cabor_id', $caborId))
            ->when($kelurahanId, fn ($q) => $q->where('kelurahan_id', $kelurahanId))
            ->when($kecamatanId, fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->orderBy('nama');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Klub/Index', [
            'klubs' => [
                'data' => KlubResource::collection($paginator->items())->resolve(),
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
                'cabor_id' => $caborId,
                'kelurahan_id' => $kelurahanId,
                'kecamatan_id' => $kecamatanId,
            ],
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('klub.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Klub::class);

        return Inertia::render('Klub/Form', [
            'klub' => null,
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->with('kecamatan:id,nama')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreKlubRequest $request): RedirectResponse
    {
        $this->authorize('create', Klub::class);

        $validated = $request->validated();

        $data = collect($validated)->except(['logo', 'dokumen_legalitas'])->toArray();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('klub-logos', 'public');
        }

        if ($request->hasFile('dokumen_legalitas')) {
            $data['dokumen_legalitas_path'] = $request->file('dokumen_legalitas')->store('klub-dokumens', 'public');
        }

        // Sync kecamatan_id from kelurahan if not provided
        if (empty($data['kecamatan_id']) && ! empty($data['kelurahan_id'])) {
            $kel = Kelurahan::find($data['kelurahan_id']);
            if ($kel) {
                $data['kecamatan_id'] = $kel->kecamatan_id;
            }
        }

        Klub::create($data);

        return redirect()->route('klubs.index')->with('success', 'Klub berhasil dibuat.');
    }

    public function show(Request $request, Klub $klub): Response
    {
        $this->authorize('view', $klub);

        $klub->load(['cabor', 'kelurahan', 'kecamatan'])->loadCount(['atlets']);

        return Inertia::render('Klub/Form', [
            'klub' => (new KlubResource($klub))->resolve(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->with('kecamatan:id,nama')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Klub $klub): Response
    {
        $this->authorize('update', $klub);

        $klub->load(['cabor', 'kelurahan', 'kecamatan']);

        return Inertia::render('Klub/Form', [
            'klub' => (new KlubResource($klub))->resolve(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->with('kecamatan:id,nama')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateKlubRequest $request, Klub $klub): RedirectResponse
    {
        $this->authorize('update', $klub);

        $validated = $request->validated();

        $data = collect($validated)->except(['logo', 'dokumen_legalitas'])->toArray();

        if ($request->hasFile('logo')) {
            if ($klub->logo_path) {
                Storage::disk('public')->delete($klub->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('klub-logos', 'public');
        }

        if ($request->hasFile('dokumen_legalitas')) {
            if ($klub->dokumen_legalitas_path) {
                Storage::disk('public')->delete($klub->dokumen_legalitas_path);
            }
            $data['dokumen_legalitas_path'] = $request->file('dokumen_legalitas')->store('klub-dokumens', 'public');
        }

        if (empty($data['kecamatan_id']) && ! empty($data['kelurahan_id'])) {
            $kel = Kelurahan::find($data['kelurahan_id']);
            if ($kel) {
                $data['kecamatan_id'] = $kel->kecamatan_id;
            }
        }

        $klub->update($data);

        return redirect()->route('klubs.index')->with('success', 'Klub berhasil diperbarui.');
    }

    public function destroy(Request $request, Klub $klub): RedirectResponse
    {
        $this->authorize('delete', $klub);

        $klub->delete();

        return redirect()->route('klubs.index')->with('success', 'Klub berhasil dihapus.');
    }
}
