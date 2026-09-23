<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kelurahan\StoreKelurahanRequest;
use App\Http\Requests\Kelurahan\UpdateKelurahanRequest;
use App\Http\Resources\KelurahanResource;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KelurahanController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Kelurahan::class);

        $search = $request->string('search')->toString();
        $kecamatanId = $request->string('kecamatan_id')->toString();

        $query = Kelurahan::query()
            ->with(['kecamatan'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            }))
            ->when($kecamatanId, fn ($q) => $q->where('kecamatan_id', $kecamatanId))
            ->orderBy('nama');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Kelurahan/Index', [
            'kelurahans' => [
                'data' => KelurahanResource::collection($paginator->items())->resolve(),
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
                'kecamatan_id' => $kecamatanId,
            ],
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('wilayah.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Kelurahan::class);

        return Inertia::render('Kelurahan/Form', [
            'kelurahan' => null,
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreKelurahanRequest $request): RedirectResponse
    {
        $this->authorize('create', Kelurahan::class);

        Kelurahan::create($request->validated());

        return redirect()->route('kelurahans.index')->with('success', 'Kelurahan berhasil dibuat.');
    }

    public function show(Request $request, Kelurahan $kelurahan): Response
    {
        $this->authorize('view', $kelurahan);

        $kelurahan->load('kecamatan');

        return Inertia::render('Kelurahan/Form', [
            'kelurahan' => (new KelurahanResource($kelurahan))->resolve(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Kelurahan $kelurahan): Response
    {
        $this->authorize('update', $kelurahan);

        return Inertia::render('Kelurahan/Form', [
            'kelurahan' => (new KelurahanResource($kelurahan))->resolve(),
            'kecamatans' => Kecamatan::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateKelurahanRequest $request, Kelurahan $kelurahan): RedirectResponse
    {
        $this->authorize('update', $kelurahan);

        $kelurahan->update($request->validated());

        return redirect()->route('kelurahans.index')->with('success', 'Kelurahan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kelurahan $kelurahan): RedirectResponse
    {
        $this->authorize('delete', $kelurahan);

        $kelurahan->delete();

        return redirect()->route('kelurahans.index')->with('success', 'Kelurahan berhasil dihapus.');
    }
}
