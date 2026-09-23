<?php

namespace App\Http\Controllers;

use App\Http\Requests\Kejuaraan\StoreKejuaraanRequest;
use App\Http\Requests\Kejuaraan\UpdateKejuaraanRequest;
use App\Http\Resources\KejuaraanResource;
use App\Models\Cabor;
use App\Models\Kejuaraan;
use App\Models\Organisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class KejuaraanController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Kejuaraan::class);

        $search = $request->string('search')->toString();
        $tingkat = $request->string('tingkat')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $organisasiId = $request->string('organisasi_id')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();
        $status = $request->string('verification_status')->toString();

        $query = Kejuaraan::query()
            ->with(['organisasi', 'cabor'])
            ->withCount(['prestasis'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('penyelenggara', 'like', "%{$search}%");
            }))
            ->when($tingkat, fn ($q) => $q->where('tingkat', $tingkat))
            ->when($caborId, fn ($q) => $q->where('cabor_id', $caborId))
            ->when($organisasiId, fn ($q) => $q->where('organisasi_id', $organisasiId))
            ->when($status, fn ($q) => $q->where('verification_status', $status))
            ->when($dateFrom, fn ($q) => $q->where('tanggal_mulai', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('tanggal_selesai', '<=', $dateTo))
            ->orderByDesc('tanggal_mulai')
            ->orderBy('nama');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Kejuaraan/Index', [
            'kejuaraans' => [
                'data' => KejuaraanResource::collection($paginator->items())->resolve(),
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
                'tingkat' => $tingkat,
                'cabor_id' => $caborId,
                'organisasi_id' => $organisasiId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'verification_status' => $status,
            ],
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('kejuaraan.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Kejuaraan::class);

        return Inertia::render('Kejuaraan/Form', [
            'kejuaraan' => null,
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreKejuaraanRequest $request): RedirectResponse
    {
        $this->authorize('create', Kejuaraan::class);

        $validated = $request->validated();

        $data = collect($validated)->except(['poster'])->toArray();

        if ($request->hasFile('poster')) {
            $data['poster_path'] = $request->file('poster')->store('kejuaraan-posters', 'public');
        }

        Kejuaraan::create($data);

        return redirect()->route('kejuaraans.index')->with('success', 'Kejuaraan berhasil dibuat.');
    }

    public function show(Request $request, Kejuaraan $kejuaraan): Response
    {
        $this->authorize('view', $kejuaraan);

        $kejuaraan->load(['organisasi', 'cabor'])->loadCount(['prestasis']);

        return Inertia::render('Kejuaraan/Show', [
            'kejuaraan' => (new KejuaraanResource($kejuaraan))->resolve(),
            'can' => [
                'manage' => $request->user()->can('kejuaraan.manage'),
            ],
        ]);
    }

    public function edit(Request $request, Kejuaraan $kejuaraan): Response
    {
        $this->authorize('update', $kejuaraan);

        $kejuaraan->load(['organisasi', 'cabor']);

        return Inertia::render('Kejuaraan/Form', [
            'kejuaraan' => (new KejuaraanResource($kejuaraan))->resolve(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateKejuaraanRequest $request, Kejuaraan $kejuaraan): RedirectResponse
    {
        $this->authorize('update', $kejuaraan);

        $validated = $request->validated();

        $data = collect($validated)->except(['poster'])->toArray();

        if ($request->hasFile('poster')) {
            if ($kejuaraan->poster_path) {
                Storage::disk('public')->delete($kejuaraan->poster_path);
            }
            $data['poster_path'] = $request->file('poster')->store('kejuaraan-posters', 'public');
        }

        $kejuaraan->update($data);

        return redirect()->route('kejuaraans.index')->with('success', 'Kejuaraan berhasil diperbarui.');
    }

    public function destroy(Request $request, Kejuaraan $kejuaraan): RedirectResponse
    {
        $this->authorize('delete', $kejuaraan);

        if ($kejuaraan->poster_path) {
            Storage::disk('public')->delete($kejuaraan->poster_path);
        }

        $kejuaraan->delete();

        return redirect()->route('kejuaraans.index')->with('success', 'Kejuaraan berhasil dihapus.');
    }
}
