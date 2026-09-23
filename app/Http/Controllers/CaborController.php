<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cabor\StoreCaborRequest;
use App\Http\Requests\Cabor\UpdateCaborRequest;
use App\Http\Resources\CaborResource;
use App\Models\Cabor;
use App\Models\Organisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CaborController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Cabor::class);

        $search = $request->string('search')->toString();
        $organisasiId = $request->string('organisasi_id')->toString();

        $query = Cabor::query()
            ->with(['organisasi'])
            ->withCount(['klubs', 'atlets'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%");
            }))
            ->when($organisasiId, fn ($q) => $q->where('organisasi_id', $organisasiId))
            ->orderBy('nama');

        // Scope for operator_organisasi: optionally filter to own organisasi if needed (still allow viewing all but manage scoped in policy)
        // For viewer simplicity show all; policy already restricts update/delete.

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Cabor/Index', [
            'cabors' => [
                'data' => CaborResource::collection($paginator->items())->resolve(),
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
                'organisasi_id' => $organisasiId,
            ],
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('cabor.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Cabor::class);

        return Inertia::render('Cabor/Form', [
            'cabor' => null,
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreCaborRequest $request): RedirectResponse
    {
        $this->authorize('create', Cabor::class);

        Cabor::create($request->validated());

        return redirect()->route('cabors.index')->with('success', 'Cabor berhasil dibuat.');
    }

    public function show(Request $request, Cabor $cabor): Response
    {
        $this->authorize('view', $cabor);

        $cabor->load(['organisasi'])->loadCount(['klubs', 'atlets']);

        return Inertia::render('Cabor/Form', [
            'cabor' => (new CaborResource($cabor))->resolve(),
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Cabor $cabor): Response
    {
        $this->authorize('update', $cabor);

        return Inertia::render('Cabor/Form', [
            'cabor' => (new CaborResource($cabor))->resolve(),
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateCaborRequest $request, Cabor $cabor): RedirectResponse
    {
        $this->authorize('update', $cabor);

        $cabor->update($request->validated());

        return redirect()->route('cabors.index')->with('success', 'Cabor berhasil diperbarui.');
    }

    public function destroy(Request $request, Cabor $cabor): RedirectResponse
    {
        $this->authorize('delete', $cabor);

        $cabor->delete();

        return redirect()->route('cabors.index')->with('success', 'Cabor berhasil dihapus.');
    }
}
