<?php

namespace App\Http\Controllers;

use App\Http\Requests\Prestasi\StorePrestasiRequest;
use App\Http\Requests\Prestasi\UpdatePrestasiRequest;
use App\Http\Resources\PrestasiResource;
use App\Models\Atlet;
use App\Models\Cabor;
use App\Models\Kejuaraan;
use App\Models\Prestasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class PrestasiController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Prestasi::class);

        $search = $request->string('search')->toString();
        $medali = $request->string('medali')->toString();
        $caborId = $request->string('cabor_id')->toString();
        $kejuaraanId = $request->string('kejuaraan_id')->toString();
        $status = $request->string('verification_status')->toString();

        $query = Prestasi::query()
            ->with(['atlet', 'cabor', 'kejuaraan'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->whereHas('atlet', fn ($aq) => $aq->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('kejuaraan', fn ($kq) => $kq->where('nama', 'like', "%{$search}%"));
            }))
            ->when($medali, fn ($q) => $q->where('medali', $medali))
            ->when($caborId, fn ($q) => $q->where('cabor_id', $caborId))
            ->when($kejuaraanId, fn ($q) => $q->where('kejuaraan_id', $kejuaraanId))
            ->when($status, fn ($q) => $q->where('verification_status', $status))
            ->orderByDesc('tanggal')
            ->orderBy('id');

        // Optional scope for operator_klub: only show prestasi belonging to own klub's atlet? Keep visible but manage filtered by policy
        // If operator_klub, limit index to own klub's prestasis for UX
        $user = $request->user();
        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $query->whereHas('atlet', fn ($q) => $q->where('klub_id', $user->klub_id));
        }

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('Prestasi/Index', [
            'prestasis' => [
                'data' => PrestasiResource::collection($paginator->items())->resolve(),
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
                'medali' => $medali,
                'cabor_id' => $caborId,
                'kejuaraan_id' => $kejuaraanId,
                'verification_status' => $status,
            ],
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kejuaraans' => Kejuaraan::query()->select('id', 'nama', 'tingkat')->orderBy('nama')->get(),
            'atlets' => Atlet::query()->select('id', 'nama', 'klub_id')->orderBy('nama')->limit(500)->get(),
            'can' => [
                'manage' => $request->user()->can('prestasi.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Prestasi::class);

        $user = $request->user();
        $atletQuery = Atlet::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama');
        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $atletQuery->where('klub_id', $user->klub_id);
        }

        return Inertia::render('Prestasi/Form', [
            'prestasi' => null,
            'atlets' => $atletQuery->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kejuaraans' => Kejuaraan::query()->select('id', 'nama', 'tingkat')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StorePrestasiRequest $request): RedirectResponse
    {
        $this->authorize('create', Prestasi::class);

        $validated = $request->validated();

        // Scope check: operator_klub can only create prestasi for own klub's atlet
        $user = $request->user();
        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $atlet = Atlet::find($validated['atlet_id']);
            if (! $atlet || (int) $atlet->klub_id !== (int) $user->klub_id) {
                abort(403, 'Anda hanya dapat mencatat prestasi atlet dari klub Anda.');
            }
        }

        $data = collect($validated)->except(['sertifikat'])->toArray();

        if ($request->hasFile('sertifikat')) {
            $data['sertifikat_path'] = $request->file('sertifikat')->store('prestasi-sertifikats', 'public');
        }

        Prestasi::create($data);

        return redirect()->route('prestasis.index')->with('success', 'Prestasi berhasil dibuat.');
    }

    public function show(Request $request, Prestasi $prestasi): Response
    {
        $this->authorize('view', $prestasi);

        $prestasi->load(['atlet', 'cabor', 'kejuaraan']);

        $user = $request->user();
        $atletQuery = Atlet::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama');
        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $atletQuery->where('klub_id', $user->klub_id);
        }

        return Inertia::render('Prestasi/Form', [
            'prestasi' => (new PrestasiResource($prestasi))->resolve(),
            'atlets' => $atletQuery->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kejuaraans' => Kejuaraan::query()->select('id', 'nama', 'tingkat')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Prestasi $prestasi): Response
    {
        $this->authorize('update', $prestasi);

        $prestasi->load(['atlet', 'cabor', 'kejuaraan']);

        $user = $request->user();
        $atletQuery = Atlet::query()->select('id', 'nama', 'klub_id', 'cabor_id')->orderBy('nama');
        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $atletQuery->where('klub_id', $user->klub_id);
        }

        return Inertia::render('Prestasi/Form', [
            'prestasi' => (new PrestasiResource($prestasi))->resolve(),
            'atlets' => $atletQuery->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kode')->orderBy('nama')->get(),
            'kejuaraans' => Kejuaraan::query()->select('id', 'nama', 'tingkat')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdatePrestasiRequest $request, Prestasi $prestasi): RedirectResponse
    {
        $this->authorize('update', $prestasi);

        $validated = $request->validated();

        $user = $request->user();
        if ($user->hasRole('operator_klub') && $user->klub_id !== null) {
            $atlet = Atlet::find($validated['atlet_id']);
            if (! $atlet || (int) $atlet->klub_id !== (int) $user->klub_id) {
                abort(403, 'Anda hanya dapat mencatat prestasi atlet dari klub Anda.');
            }
        }

        $data = collect($validated)->except(['sertifikat'])->toArray();

        if ($request->hasFile('sertifikat')) {
            if ($prestasi->sertifikat_path) {
                Storage::disk('public')->delete($prestasi->sertifikat_path);
            }
            $data['sertifikat_path'] = $request->file('sertifikat')->store('prestasi-sertifikats', 'public');
        }

        $prestasi->update($data);

        return redirect()->route('prestasis.index')->with('success', 'Prestasi berhasil diperbarui.');
    }

    public function destroy(Request $request, Prestasi $prestasi): RedirectResponse
    {
        $this->authorize('delete', $prestasi);

        if ($prestasi->sertifikat_path) {
            Storage::disk('public')->delete($prestasi->sertifikat_path);
        }

        $prestasi->delete();

        return redirect()->route('prestasis.index')->with('success', 'Prestasi berhasil dihapus.');
    }
}
