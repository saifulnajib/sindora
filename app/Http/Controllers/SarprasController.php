<?php

namespace App\Http\Controllers;

use App\Http\Requests\Sarpras\StoreSarprasRequest;
use App\Http\Requests\Sarpras\UpdateSarprasRequest;
use App\Http\Resources\SarprasResource;
use App\Models\Cabor;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Klub;
use App\Models\Sarpras;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SarprasController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Sarpras::class);

        $search = $request->string('search')->toString();
        $kondisi = $request->string('kondisi')->toString();
        $kelurahanId = $request->string('kelurahan_id')->toString();
        $kecamatanId = $request->string('kecamatan_id')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $jenis = $request->string('jenis')->toString();

        $query = Sarpras::query()
            ->with(['kelurahan', 'kecamatan', 'klub', 'cabor'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%");
            }))
            ->when($kondisi, fn ($q) => $q->where('kondisi', $kondisi))
            ->when($kelurahanId, fn ($q) => $q->where('kelurahan_id', $kelurahanId))
            ->when($kecamatanId, fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->when($caborId, fn ($q) => $q->where('cabor_id', $caborId))
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->orderBy('nama');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Sarpras/Index', [
            'sarpras' => [
                'data' => SarprasResource::collection($paginator->items())->resolve(),
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
                'kondisi' => $kondisi,
                'kelurahan_id' => $kelurahanId,
                'kecamatan_id' => $kecamatanId,
                'cabor_id' => $caborId,
                'jenis' => $jenis,
            ],
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('sarpras.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Sarpras::class);

        return Inertia::render('Sarpras/Form', [
            'sarpras' => null,
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->with('kecamatan:id,nama')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreSarprasRequest $request): RedirectResponse
    {
        $this->authorize('create', Sarpras::class);

        $validated = $request->validated();

        $data = collect($validated)->except(['foto'])->toArray();

        foreach (['kelurahan_id', 'kecamatan_id', 'klub_id', 'cabor_id'] as $fk) {
            if (array_key_exists($fk, $data) && $data[$fk] === '') {
                $data[$fk] = null;
            }
        }

        // Sync kecamatan_id from kelurahan if not provided
        if (empty($data['kecamatan_id']) && ! empty($data['kelurahan_id'])) {
            $kel = Kelurahan::find($data['kelurahan_id']);
            if ($kel) {
                $data['kecamatan_id'] = $kel->kecamatan_id;
            }
        }

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('sarpras-fotos', 'public');
        }

        if (empty($data['kondisi'])) {
            $data['kondisi'] = 'baik';
        }

        Sarpras::create($data);

        return redirect()->route('sarpras.index')->with('success', 'Sarpras berhasil dibuat.');
    }

    public function show(Request $request, Sarpras $sarpras): Response
    {
        $this->authorize('view', $sarpras);

        $sarpras->load(['kelurahan', 'kecamatan', 'klub', 'cabor']);

        return Inertia::render('Sarpras/Form', [
            'sarpras' => (new SarprasResource($sarpras))->resolve(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->with('kecamatan:id,nama')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Sarpras $sarpras): Response
    {
        $this->authorize('update', $sarpras);

        $sarpras->load(['kelurahan', 'kecamatan', 'klub', 'cabor']);

        return Inertia::render('Sarpras/Form', [
            'sarpras' => (new SarprasResource($sarpras))->resolve(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->with('kecamatan:id,nama')->orderBy('nama')->get(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateSarprasRequest $request, Sarpras $sarpras): RedirectResponse
    {
        $this->authorize('update', $sarpras);

        $validated = $request->validated();

        $data = collect($validated)->except(['foto'])->toArray();

        foreach (['kelurahan_id', 'kecamatan_id', 'klub_id', 'cabor_id'] as $fk) {
            if (array_key_exists($fk, $data) && $data[$fk] === '') {
                $data[$fk] = null;
            }
        }

        if (empty($data['kecamatan_id']) && ! empty($data['kelurahan_id'])) {
            $kel = Kelurahan::find($data['kelurahan_id']);
            if ($kel) {
                $data['kecamatan_id'] = $kel->kecamatan_id;
            }
        }

        if ($request->hasFile('foto')) {
            if ($sarpras->foto_path) {
                Storage::disk('public')->delete($sarpras->foto_path);
            }
            $data['foto_path'] = $request->file('foto')->store('sarpras-fotos', 'public');
        }

        $sarpras->update($data);

        return redirect()->route('sarpras.index')->with('success', 'Sarpras berhasil diperbarui.');
    }

    public function destroy(Request $request, Sarpras $sarpras): RedirectResponse
    {
        $this->authorize('delete', $sarpras);

        $sarpras->delete();

        return redirect()->route('sarpras.index')->with('success', 'Sarpras berhasil dihapus.');
    }
}
