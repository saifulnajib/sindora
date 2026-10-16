<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Cabor;
use App\Models\Klub;
use App\Models\Organisasi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $search = $request->string('search')->toString();
        $roleFilter = $request->string('role')->toString();

        $query = User::query()
            ->with(['roles', 'klub', 'organisasi', 'cabor', 'cabors'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($roleFilter, function ($q) use ($roleFilter) {
                $q->whereHas('roles', fn ($qq) => $qq->where('name', $roleFilter));
            })
            ->latest('id');

        $users = $query->paginate(10)->withQueryString();

        $officialRoles = $this->getOfficialRoles();

        return Inertia::render('Users/Index', [
            'users' => [
                'data' => UserResource::collection($users->items())->resolve(),
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ],
                'links' => [
                    'first' => $users->url(1),
                    'last' => $users->url($users->lastPage()),
                    'prev' => $users->previousPageUrl(),
                    'next' => $users->nextPageUrl(),
                ],
            ],
            'filters' => [
                'search' => $search,
                'role' => $roleFilter,
            ],
            'roles' => $officialRoles,
            'can' => [
                'create' => $request->user()->can('user.manage'),
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('Users/Form', [
            'user' => null,
            'roles' => $this->getOfficialRoles(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kategori', 'organisasi_id')->orderBy('nama')->get(),
            'isEdit' => false,
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();

        $caborIds = isset($data['cabor_ids']) && is_array($data['cabor_ids'])
            ? array_map('intval', $data['cabor_ids'])
            : (! empty($data['cabor_id']) ? [(int) $data['cabor_id']] : []);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'klub_id' => $data['klub_id'] ?? null,
            'organisasi_id' => $data['organisasi_id'] ?? null,
            'cabor_id' => $caborIds[0] ?? ($data['cabor_id'] ?? null),
        ]);

        $user->syncRoles($data['roles']);
        if (! empty($caborIds)) {
            $user->cabors()->sync($caborIds);
        }

        return redirect()->route('users.index')->with('success', 'User berhasil dibuat.');
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('update', $user);

        $user->load(['roles', 'klub', 'organisasi', 'cabor', 'cabors']);

        return Inertia::render('Users/Form', [
            'user' => (new UserResource($user))->resolve(),
            'roles' => $this->getOfficialRoles(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kategori', 'organisasi_id')->orderBy('nama')->get(),
            'isEdit' => true,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        $caborIds = isset($data['cabor_ids']) && is_array($data['cabor_ids'])
            ? array_map('intval', $data['cabor_ids'])
            : (! empty($data['cabor_id']) ? [(int) $data['cabor_id']] : []);

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'klub_id' => $data['klub_id'] ?? null,
            'organisasi_id' => $data['organisasi_id'] ?? null,
            'cabor_id' => $caborIds[0] ?? ($data['cabor_id'] ?? null),
        ]);

        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();
        $user->syncRoles($data['roles']);
        $user->cabors()->sync($caborIds);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function show(Request $request, User $user): Response
    {
        $this->authorize('view', $user);

        $user->load(['roles', 'klub', 'organisasi', 'cabor', 'cabors']);

        return Inertia::render('Users/Form', [
            'user' => (new UserResource($user))->resolve(),
            'roles' => $this->getOfficialRoles(),
            'klubs' => Klub::query()->select('id', 'nama')->orderBy('nama')->get(),
            'organisasis' => Organisasi::query()->select('id', 'nama')->orderBy('nama')->get(),
            'cabors' => Cabor::query()->select('id', 'nama', 'kategori', 'organisasi_id')->orderBy('nama')->get(),
            'isEdit' => false,
            'isShow' => true,
        ]);
    }

    private function getOfficialRoles(): array
    {
        return [
            ['name' => 'super_admin', 'label' => 'Super Admin', 'desc' => 'Akses penuh seluruh sistem'],
            ['name' => 'verifikator', 'label' => 'Verifikator Dispora', 'desc' => 'Verifikasi & validasi data'],
            ['name' => 'pimpinan', 'label' => 'Pimpinan', 'desc' => 'Monitoring eksekutif (read-only, dashboard, laporan)'],
            ['name' => 'pengurus_cabor', 'label' => 'Pengurus Cabor (Pengkot)', 'desc' => 'Mengelola cabang olahraga tertentu'],
        ];
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        if ((int) $request->user()->id === (int) $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $request->validate([
            'password' => ['required', 'string', 'confirmed', 'min:8'],
        ]);

        $user->password = Hash::make($request->input('password'));
        $user->save();

        return back()->with('success', 'Password berhasil direset.');
    }
}
