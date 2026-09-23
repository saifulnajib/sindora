<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kecamatan\StoreKecamatanRequest;
use App\Http\Requests\Kecamatan\UpdateKecamatanRequest;
use App\Http\Resources\KecamatanResource;
use App\Models\Kecamatan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KecamatanController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Kecamatan::class);

        $search = $request->string('search')->toString();

        $query = Kecamatan::query()
            ->withCount(['kelurahans', 'klubs'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%");
            }))
            ->orderBy('nama');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Kecamatan/Index', [
            'kecamatans' => [
                'data' => KecamatanResource::collection($paginator->items())->resolve(),
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
            'filters' => ['search' => $search],
            'can' => [
                'manage' => $request->user()->can('wilayah.manage'),
                'view' => $request->user()->can('wilayah.view'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Kecamatan::class);

        return Inertia::render('Kecamatan/Form', [
            'kecamatan' => null,
            'isEdit' => false,
        ]);
    }

    public function store(StoreKecamatanRequest $request): RedirectResponse
    {
        $this->authorize('create', Kecamatan::class);

        Kecamatan::create($request->validated());

        return redirect()->route('kecamatans.index')->with('success', 'Kecamatan berhasil dibuat.');
    }

    public function show(Request $request, Kecamatan $kecamatan): Response
    {
        $this->authorize('view', $kecamatan);

        $kecamatan->loadCount(['kelurahans', 'klubs']);

        return Inertia::render('Kecamatan/Form', [
            'kecamatan' => (new KecamatanResource($kecamatan))->resolve(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Kecamatan $kecamatan): Response
    {
        $this->authorize('update', $kecamatan);

        return Inertia::render('Kecamatan/Form', [
            'kecamatan' => (new KecamatanResource($kecamatan))->resolve(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateKecamatanRequest $request, Kecamatan $kecamatan): RedirectResponse
    {
        $this->authorize('update', $kecamatan);

        $kecamatan->update($request->validated());

        return redirect()->route('kecamatans.index')->with('success', 'Kecamatan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kecamatan $kecamatan): RedirectResponse
    {
        $this->authorize('delete', $kecamatan);

        $kecamatan->delete();

        return redirect()->route('kecamatans.index')->with('success', 'Kecamatan berhasil dihapus.');
    }
}
