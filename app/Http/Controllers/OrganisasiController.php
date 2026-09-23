<?php

namespace App\Http\Controllers;

use App\Http\Requests\Organisasi\StoreOrganisasiRequest;
use App\Http\Requests\Organisasi\UpdateOrganisasiRequest;
use App\Http\Resources\OrganisasiResource;
use App\Models\Organisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganisasiController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Organisasi::class);

        $search = $request->string('search')->toString();
        $jenis = $request->string('jenis')->toString();

        $query = Organisasi::query()
            ->withCount(['cabors'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('singkatan', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%");
            }))
            ->when($jenis, fn ($q) => $q->where('jenis', $jenis))
            ->latest('id');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Organisasi/Index', [
            'organisasis' => [
                'data' => OrganisasiResource::collection($paginator->items())->resolve(),
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
                'jenis' => $jenis,
            ],
            'can' => [
                'manage' => $request->user()->can('organisasi.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Organisasi::class);

        return Inertia::render('Organisasi/Form', [
            'organisasi' => null,
            'isEdit' => false,
        ]);
    }

    public function store(StoreOrganisasiRequest $request): RedirectResponse
    {
        $this->authorize('create', Organisasi::class);

        Organisasi::create($request->validated());

        return redirect()->route('organisasis.index')->with('success', 'Organisasi berhasil dibuat.');
    }

    public function show(Request $request, Organisasi $organisasi): Response
    {
        $this->authorize('view', $organisasi);

        $organisasi->loadCount(['cabors']);

        return Inertia::render('Organisasi/Form', [
            'organisasi' => (new OrganisasiResource($organisasi))->resolve(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Organisasi $organisasi): Response
    {
        $this->authorize('update', $organisasi);

        return Inertia::render('Organisasi/Form', [
            'organisasi' => (new OrganisasiResource($organisasi))->resolve(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateOrganisasiRequest $request, Organisasi $organisasi): RedirectResponse
    {
        $this->authorize('update', $organisasi);

        $organisasi->update($request->validated());

        return redirect()->route('organisasis.index')->with('success', 'Organisasi berhasil diperbarui.');
    }

    public function destroy(Request $request, Organisasi $organisasi): RedirectResponse
    {
        $this->authorize('delete', $organisasi);

        $organisasi->delete();

        return redirect()->route('organisasis.index')->with('success', 'Organisasi berhasil dihapus.');
    }
}
