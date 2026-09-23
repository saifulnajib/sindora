<?php

namespace App\Http\Controllers;

use App\Http\Requests\SarprasJadwal\StoreSarprasJadwalRequest;
use App\Http\Requests\SarprasJadwal\UpdateSarprasJadwalRequest;
use App\Http\Resources\SarprasJadwalResource;
use App\Models\Klub;
use App\Models\Sarpras;
use App\Models\SarprasJadwal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SarprasJadwalController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Sarpras::class);

        $sarprasId = $request->string('sarpras_id')->toString();
        $klubId = $request->string('klub_id')->toString();
        $tanggal = $request->string('tanggal')->toString();
        $search = $request->string('search')->toString();

        $query = SarprasJadwal::query()
            ->with(['sarpras:id,nama,jenis', 'klub:id,nama'])
            ->when($sarprasId !== '', fn ($q) => $q->where('sarpras_id', $sarprasId))
            ->when($klubId !== '', fn ($q) => $q->where('klub_id', $klubId))
            ->when($tanggal !== '', fn ($q) => $q->where('tanggal', $tanggal))
            ->when($search !== '', fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('kegiatan', 'like', "%{$search}%")
                    ->orWhereHas('sarpras', fn ($sq) => $sq->where('nama', 'like', "%{$search}%"))
                    ->orWhereHas('klub', fn ($kq) => $kq->where('nama', 'like', "%{$search}%"));
            }))
            ->orderBy('tanggal', 'desc')
            ->orderBy('jam_mulai');

        $paginator = $query->paginate(10)->withQueryString();

        return Inertia::render('SarprasJadwal/Index', [
            'jadwals' => [
                'data' => SarprasJadwalResource::collection($paginator->items())->resolve(),
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
                'sarpras_id' => $sarprasId,
                'klub_id' => $klubId,
                'tanggal' => $tanggal,
                'search' => $search,
            ],
            'sarprasList' => Sarpras::query()->select('id', 'nama', 'jenis')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'can' => [
                'manage' => $request->user()->can('sarpras.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Sarpras::class);

        return Inertia::render('SarprasJadwal/Form', [
            'jadwal' => null,
            'sarprasList' => Sarpras::query()->select('id', 'nama', 'jenis')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreSarprasJadwalRequest $request): RedirectResponse
    {
        $this->authorize('create', Sarpras::class);

        $validated = $request->validated();

        // Normalize alias keperluan -> kegiatan already handled in request, ensure kegiatan set
        if (isset($validated['keperluan']) && empty($validated['kegiatan'])) {
            $validated['kegiatan'] = $validated['keperluan'];
        }
        unset($validated['keperluan']);

        // Normalize times to H:i:s
        foreach (['jam_mulai', 'jam_selesai'] as $field) {
            if (isset($validated[$field]) && strlen($validated[$field]) === 5) {
                $validated[$field] = $validated[$field].':00';
            }
        }

        foreach (['klub_id'] as $fk) {
            if (array_key_exists($fk, $validated) && $validated[$fk] === '') {
                $validated[$fk] = null;
            }
        }

        SarprasJadwal::create($validated);

        return redirect()->route('sarpras-jadwals.index')->with('success', 'Jadwal sarpras berhasil dibuat.');
    }

    public function show(Request $request, SarprasJadwal $sarprasJadwal): Response
    {
        $this->authorize('view', $sarprasJadwal->sarpras ?? Sarpras::find($sarprasJadwal->sarpras_id) ?? new Sarpras);

        $sarprasJadwal->load(['sarpras', 'klub']);

        return Inertia::render('SarprasJadwal/Form', [
            'jadwal' => (new SarprasJadwalResource($sarprasJadwal))->resolve(),
            'sarprasList' => Sarpras::query()->select('id', 'nama', 'jenis')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, SarprasJadwal $sarprasJadwal): Response
    {
        // Check manage permission
        $this->authorize('update', $sarprasJadwal->sarpras ?? Sarpras::find($sarprasJadwal->sarpras_id) ?? new Sarpras);

        $sarprasJadwal->load(['sarpras', 'klub']);

        return Inertia::render('SarprasJadwal/Form', [
            'jadwal' => (new SarprasJadwalResource($sarprasJadwal))->resolve(),
            'sarprasList' => Sarpras::query()->select('id', 'nama', 'jenis')->orderBy('nama')->get(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateSarprasJadwalRequest $request, SarprasJadwal $sarprasJadwal): RedirectResponse
    {
        $this->authorize('update', $sarprasJadwal->sarpras ?? Sarpras::find($sarprasJadwal->sarpras_id) ?? new Sarpras);

        $validated = $request->validated();

        if (isset($validated['keperluan']) && empty($validated['kegiatan'])) {
            $validated['kegiatan'] = $validated['keperluan'];
        }
        unset($validated['keperluan']);

        foreach (['jam_mulai', 'jam_selesai'] as $field) {
            if (isset($validated[$field]) && strlen($validated[$field]) === 5) {
                $validated[$field] = $validated[$field].':00';
            }
        }

        foreach (['klub_id'] as $fk) {
            if (array_key_exists($fk, $validated) && $validated[$fk] === '') {
                $validated[$fk] = null;
            }
        }

        $sarprasJadwal->update($validated);

        return redirect()->route('sarpras-jadwals.index')->with('success', 'Jadwal sarpras berhasil diperbarui.');
    }

    public function destroy(Request $request, SarprasJadwal $sarprasJadwal): RedirectResponse
    {
        $this->authorize('delete', $sarprasJadwal->sarpras ?? Sarpras::find($sarprasJadwal->sarpras_id) ?? new Sarpras);

        $sarprasJadwal->delete();

        return redirect()->route('sarpras-jadwals.index')->with('success', 'Jadwal sarpras berhasil dihapus.');
    }
}
