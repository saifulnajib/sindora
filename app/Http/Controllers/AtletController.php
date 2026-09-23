<?php

namespace App\Http\Controllers;

use App\Http\Requests\Atlet\StoreAtletRequest;
use App\Http\Requests\Atlet\UpdateAtletRequest;
use App\Http\Resources\AtletResource;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Kelurahan;
use App\Models\Klub;
use App\Models\Sdm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $statusPembinaan = $request->string('status_pembinaan')->toString();

        $query = Atlet::query()
            ->with(['klub', 'cabor', 'kelurahan'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                // NIK encrypted, so search only nama + kelas_tanding for now
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('kelas_tanding', 'like', "%{$search}%");
            }))
            ->when($klubId, fn ($q) => $q->where('klub_id', $klubId))
            ->when($caborId, fn ($q) => $q->where('cabor_id', $caborId))
            ->when($statusPembinaan, fn ($q) => $q->where('status_pembinaan', $statusPembinaan))
            ->orderBy('nama');

        $paginator = $query->paginate(10)->withQueryString();

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
                'status_pembinaan' => $statusPembinaan,
            ],
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('atlet.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Atlet::class);

        return Inertia::render('Atlet/Form', [
            'atlet' => null,
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'pelatihs' => Sdm::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreAtletRequest $request): RedirectResponse
    {
        $this->authorize('create', Atlet::class);

        $validated = $request->validated();

        $data = collect($validated)->except(['foto'])->toArray();

        if ($request->hasFile('foto')) {
            $data['foto_path'] = $request->file('foto')->store('atlet-fotos', 'public');
        }

        Atlet::create($data);

        return redirect()->route('atlets.index')->with('success', 'Atlet berhasil dibuat.');
    }

    public function show(Request $request, Atlet $atlet): Response
    {
        $this->authorize('view', $atlet);

        $atlet->load(['klub', 'cabor', 'kelurahan', 'pelatih']);

        return Inertia::render('Atlet/Form', [
            'atlet' => (new AtletResource($atlet))->resolve(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'pelatihs' => Sdm::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Atlet $atlet): Response
    {
        $this->authorize('update', $atlet);

        $atlet->load(['klub', 'cabor', 'kelurahan', 'pelatih']);

        return Inertia::render('Atlet/Form', [
            'atlet' => (new AtletResource($atlet))->resolve(),
            'klubs' => Klub::query()->select('id', 'nama', 'cabor_id')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kelurahans' => Kelurahan::query()->select('id', 'nama', 'kecamatan_id')->orderBy('nama')->get(),
            'pelatihs' => Sdm::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateAtletRequest $request, Atlet $atlet): RedirectResponse
    {
        $this->authorize('update', $atlet);

        $validated = $request->validated();

        $data = collect($validated)->except(['foto'])->toArray();

        if ($request->hasFile('foto')) {
            if ($atlet->foto_path) {
                Storage::disk('public')->delete($atlet->foto_path);
            }
            $data['foto_path'] = $request->file('foto')->store('atlet-fotos', 'public');
        }

        $atlet->update($data);

        return redirect()->route('atlets.index')->with('success', 'Atlet berhasil diperbarui.');
    }

    public function destroy(Request $request, Atlet $atlet): RedirectResponse
    {
        $this->authorize('delete', $atlet);

        $atlet->delete();

        return redirect()->route('atlets.index')->with('success', 'Atlet berhasil dihapus.');
    }
}
