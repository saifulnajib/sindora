<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cabor\StoreCaborRequest;
use App\Http\Requests\Cabor\UpdateCaborRequest;
use App\Http\Resources\CaborResource;
use App\Models\Cabor;
use App\Models\Organisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class CaborController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', Cabor::class);

        $search = $request->string('search')->toString();
        $organisasiId = $request->string('organisasi_id')->toString();
        $kategori = $request->string('kategori')->toString();

        $query = Cabor::query()
            ->with(['organisasi'])
            ->withCount(['klubs', 'atlets'])
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('nama', 'like', "%{$search}%")
                    ->orWhere('kode', 'like', "%{$search}%")
                    ->orWhere('kategori', 'like', "%{$search}%")
                    ->orWhere('sk_nomor', 'like', "%{$search}%")
                    ->orWhere('nama_ketua', 'like', "%{$search}%");
            }))
            ->when($organisasiId, fn ($q) => $q->where('organisasi_id', $organisasiId))
            ->when($kategori, fn ($q) => $q->where('kategori', strtolower($kategori)))
            ->orderBy('nama');

        $user = $request->user();
        if ($user && $user->isPengkot()) {
            $query->whereIn('id', $user->getAssignedCaborIds());
        }

        $paginator = $query->paginate(10)->withQueryString();

        $kategoriList = [
            ['value' => 'koni', 'label' => 'KONI (Olahraga Prestasi)'],
            ['value' => 'kormi', 'label' => 'KORMI (Olahraga Rekreasi / Masyarakat)'],
            ['value' => 'disabilitas', 'label' => 'DISABILITAS (Olahraga Disabilitas / NPCI)'],
        ];

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
                'kategori' => $kategori,
            ],
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'kategoriList' => $kategoriList,
            'can' => [
                'manage' => $request->user()->can('cabor.manage'),
                'create' => $request->user()->can('create', Cabor::class),
                'is_pengkot' => $request->user()?->isPengkot() ?? false,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', Cabor::class);

        return Inertia::render('Cabor/Form', [
            'cabor' => null,
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'kategoriList' => [
                ['value' => 'koni', 'label' => 'KONI (Olahraga Prestasi)'],
                ['value' => 'kormi', 'label' => 'KORMI (Olahraga Rekreasi / Masyarakat)'],
                ['value' => 'disabilitas', 'label' => 'DISABILITAS (Olahraga Disabilitas / NPCI)'],
            ],
            'isEdit' => false,
        ]);
    }

    public function store(StoreCaborRequest $request): RedirectResponse
    {
        $this->authorize('create', Cabor::class);

        $validated = $request->validated();
        $data = collect($validated)->except(['logo', 'sk_file'])->toArray();

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('cabor-logos', 'public');
        }

        if ($request->hasFile('sk_file')) {
            $data['sk_file_path'] = $request->file('sk_file')->store('cabor-sk', 'public');
        }

        Cabor::create($data);

        return redirect()->route('cabors.index')->with('success', 'Cabang olahraga berhasil ditambahkan.');
    }

    public function show(Request $request, Cabor $cabor): Response
    {
        $this->authorize('view', $cabor);

        $cabor->load(['organisasi'])->loadCount(['klubs', 'atlets']);

        return Inertia::render('Cabor/Form', [
            'cabor' => (new CaborResource($cabor))->resolve(),
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'kategoriList' => [
                ['value' => 'koni', 'label' => 'KONI (Olahraga Prestasi)'],
                ['value' => 'kormi', 'label' => 'KORMI (Olahraga Rekreasi / Masyarakat)'],
                ['value' => 'disabilitas', 'label' => 'DISABILITAS (Olahraga Disabilitas / NPCI)'],
            ],
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    public function edit(Request $request, Cabor $cabor): Response
    {
        $this->authorize('update', $cabor);

        $cabor->load(['organisasi'])->loadCount(['klubs', 'atlets']);

        return Inertia::render('Cabor/Form', [
            'cabor' => (new CaborResource($cabor))->resolve(),
            'organisasis' => Organisasi::query()->select('id', 'nama', 'singkatan')->orderBy('nama')->get(),
            'kategoriList' => [
                ['value' => 'koni', 'label' => 'KONI (Olahraga Prestasi)'],
                ['value' => 'kormi', 'label' => 'KORMI (Olahraga Rekreasi / Masyarakat)'],
                ['value' => 'disabilitas', 'label' => 'DISABILITAS (Olahraga Disabilitas / NPCI)'],
            ],
            'isEdit' => true,
        ]);
    }

    public function update(UpdateCaborRequest $request, Cabor $cabor): RedirectResponse
    {
        $this->authorize('update', $cabor);

        $validated = $request->validated();
        $data = collect($validated)->except(['logo', 'sk_file'])->toArray();

        // Security rule: Core Cabor Data can ONLY be edited by Super Admin.
        // Pengkot (non-superadmin) is strictly limited to updating legalitas & pengurusan (logo, SK, ketua, kontak).
        if (! $request->user()?->hasRole('super_admin')) {
            unset($data['organisasi_id'], $data['nama'], $data['kode'], $data['kategori'], $data['deskripsi']);
        }

        if ($request->hasFile('logo')) {
            if ($cabor->logo_path) {
                Storage::disk('public')->delete($cabor->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('cabor-logos', 'public');
        }

        if ($request->hasFile('sk_file')) {
            if ($cabor->sk_file_path) {
                Storage::disk('public')->delete($cabor->sk_file_path);
            }
            $data['sk_file_path'] = $request->file('sk_file')->store('cabor-sk', 'public');
        }

        $cabor->update($data);

        return redirect()->route('cabors.index')->with('success', 'Data cabor dan dokumen pengurusan organisasi berhasil diperbarui.');
    }

    public function destroy(Request $request, Cabor $cabor): RedirectResponse
    {
        $this->authorize('delete', $cabor);

        if ($cabor->logo_path) {
            Storage::disk('public')->delete($cabor->logo_path);
        }
        if ($cabor->sk_file_path) {
            Storage::disk('public')->delete($cabor->sk_file_path);
        }

        $cabor->delete();

        return redirect()->route('cabors.index')->with('success', 'Cabor berhasil dihapus.');
    }
}
