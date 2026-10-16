<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionController extends Controller
{
    /**
     * Display the Role and Permission Matrix.
     */
    public function index(Request $request): Response
    {
        $this->authorizeAccess($request);

        $officialRoleConfigs = $this->getOfficialRoleConfigs();
        $officialRoleNames = array_keys($officialRoleConfigs);

        // Fetch roles with their current permissions
        $rolesData = [];
        foreach ($officialRoleConfigs as $roleName => $config) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );

            $rolesData[] = [
                'id' => $role->id,
                'name' => $role->name,
                'label' => $config['label'],
                'desc' => $config['desc'],
                'color' => $config['color'],
                'badge_class' => $config['badge_class'],
                'users_count' => $role->users()->count(),
                'permissions' => $role->permissions()->pluck('name')->values()->all(),
            ];
        }

        $modules = $this->getPermissionModules();

        // Collect all distinct permission names defined in modules
        $allPermissions = [];
        foreach ($modules as $mod) {
            foreach ($mod['permissions'] as $p) {
                $allPermissions[] = $p['name'];
            }
        }
        $allPermissions = array_values(array_unique($allPermissions));

        // Ensure all permissions exist in database
        foreach ($allPermissions as $permName) {
            Permission::firstOrCreate([
                'name' => $permName,
                'guard_name' => 'web',
            ]);
        }

        return Inertia::render('Roles/Index', [
            'roles' => $rolesData,
            'modules' => $modules,
            'allPermissions' => $allPermissions,
        ]);
    }

    /**
     * Update permissions for roles in bulk or individually.
     */
    public function update(Request $request): RedirectResponse
    {
        $this->authorizeAccess($request);

        $request->validate([
            'matrix' => ['required', 'array'],
            'matrix.*' => ['array'],
            'matrix.*.*' => ['string'],
        ]);

        $matrix = $request->input('matrix');
        $officialRoleConfigs = $this->getOfficialRoleConfigs();

        foreach ($matrix as $roleName => $permissionNames) {
            if (! array_key_exists($roleName, $officialRoleConfigs)) {
                continue;
            }

            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);

            // Security guardrail: Super Admin MUST retain role.manage & user.manage
            if ($roleName === 'super_admin') {
                if (! in_array('role.manage', $permissionNames, true)) {
                    $permissionNames[] = 'role.manage';
                }
                if (! in_array('user.manage', $permissionNames, true)) {
                    $permissionNames[] = 'user.manage';
                }
            }

            // Sync with Spatie
            $role->syncPermissions($permissionNames);

            // Keep legacy aliases synced to avoid breaking any legacy code
            if ($roleName === 'pengurus_cabor') {
                $pengkot = Role::where('name', 'pengkot')->first();
                $pengkot?->syncPermissions($permissionNames);

                $opOrg = Role::where('name', 'operator_organisasi')->first();
                $opOrg?->syncPermissions($permissionNames);
            }

            if ($roleName === 'pimpinan') {
                $viewer = Role::where('name', 'viewer')->first();
                $viewer?->syncPermissions($permissionNames);
            }
        }

        // Invalidate cached permissions across the application
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Matriks hak akses role berhasil diperbarui.');
    }

    /**
     * Reset role permissions to the standard default seeder configuration.
     */
    public function resetDefault(Request $request): RedirectResponse
    {
        $this->authorizeAccess($request);

        // Run default seed logic
        $defaults = $this->getDefaultRolePermissions();

        foreach ($defaults as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);

            if ($roleName === 'pengurus_cabor') {
                $pengkot = Role::where('name', 'pengkot')->first();
                $pengkot?->syncPermissions($perms);
            }
            if ($roleName === 'pimpinan') {
                $viewer = Role::where('name', 'viewer')->first();
                $viewer?->syncPermissions($perms);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('roles.index')->with('success', 'Hak akses role berhasil direset ke standar default sistem.');
    }

    /**
     * Authorize that the user has permission to manage roles.
     */
    private function authorizeAccess(Request $request): void
    {
        $user = $request->user();
        if (! $user || (! $user->can('role.manage') && ! $user->hasRole('super_admin'))) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengelola role dan hak akses sistem.');
        }
    }

    /**
     * Official 4 roles configuration.
     */
    private function getOfficialRoleConfigs(): array
    {
        return [
            'super_admin' => [
                'label' => 'Super Admin',
                'desc' => 'Akses penuh dan konfigurasi seluruh modul sistem SINDORA',
                'color' => 'red',
                'badge_class' => 'bg-red-100 text-red-800 dark:bg-red-950/60 dark:text-red-300 ring-red-300',
            ],
            'verifikator' => [
                'label' => 'Verifikator Dispora',
                'desc' => 'Verifikasi dan validasi pengajuan data atlet, klub, kejuaraan, & pembinaan',
                'color' => 'blue',
                'badge_class' => 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300 ring-blue-300',
            ],
            'pimpinan' => [
                'label' => 'Pimpinan',
                'desc' => 'Monitoring eksekutif: dashboard, laporan, WebGIS, dan audit trail (read-only)',
                'color' => 'amber',
                'badge_class' => 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 ring-amber-300',
            ],
            'pengurus_cabor' => [
                'label' => 'Pengurus Cabor (Pengkot)',
                'desc' => 'Pengelolaan data cabor, klub, atlet, sarpras, prestasi cabor yang ditugaskan',
                'color' => 'emerald',
                'badge_class' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300 ring-emerald-300',
            ],
        ];
    }

    /**
     * Grouped permission modules for matrix display.
     */
    private function getPermissionModules(): array
    {
        return [
            [
                'id' => 'master_wilayah',
                'label' => 'Master Wilayah & Organisasi',
                'description' => 'Akses referensi wilayah Kota Tanjungpinang dan induk organisasi olahraga (KONI, KORMI, DISABILITAS).',
                'icon' => 'building',
                'permissions' => [
                    [
                        'name' => 'wilayah.view',
                        'label' => 'Lihat Wilayah',
                        'desc' => 'Melihat daftar kecamatan dan kelurahan',
                    ],
                    [
                        'name' => 'wilayah.manage',
                        'label' => 'Kelola Wilayah',
                        'desc' => 'Tambah, ubah, dan hapus kecamatan dan kelurahan',
                    ],
                    [
                        'name' => 'organisasi.view',
                        'label' => 'Lihat Organisasi',
                        'desc' => 'Melihat daftar induk organisasi keolahragaan',
                    ],
                    [
                        'name' => 'organisasi.manage',
                        'label' => 'Kelola Organisasi',
                        'desc' => 'Tambah, ubah, dan hapus induk organisasi olahraga',
                    ],
                ],
            ],
            [
                'id' => 'cabor_klub',
                'label' => 'Cabang Olahraga & Klub',
                'description' => 'Akses data cabor bidang KONI, KORMI, Disabilitas dan klub/sasana olahraga binaan.',
                'icon' => 'trophy',
                'permissions' => [
                    [
                        'name' => 'cabor.view',
                        'label' => 'Lihat Cabang Olahraga',
                        'desc' => 'Melihat daftar dan profil cabang olahraga',
                    ],
                    [
                        'name' => 'cabor.manage',
                        'label' => 'Kelola Cabang Olahraga',
                        'desc' => 'Tambah dan ubah data cabang olahraga',
                    ],
                    [
                        'name' => 'klub.view',
                        'label' => 'Lihat Klub Olahraga',
                        'desc' => 'Melihat daftar klub dan sasana olahraga',
                    ],
                    [
                        'name' => 'klub.manage',
                        'label' => 'Kelola Klub Olahraga',
                        'desc' => 'Tambah, ubah, dan hapus klub olahraga',
                    ],
                ],
            ],
            [
                'id' => 'atlet_sdm_sarpras',
                'label' => 'Data Inti (Atlet, SDM & Sarpras)',
                'description' => 'Akses biodata atlet, tenaga pelatih/wasit/juri, dan inventaris fasilitas sarana prasarana.',
                'icon' => 'users',
                'permissions' => [
                    [
                        'name' => 'atlet.view',
                        'label' => 'Lihat Data Atlet',
                        'desc' => 'Melihat profil lengkap dan riwayat atlet',
                    ],
                    [
                        'name' => 'atlet.manage',
                        'label' => 'Kelola Data Atlet',
                        'desc' => 'Pendaftaran, pembaruan, dan pengelolaan atlet',
                    ],
                    [
                        'name' => 'sdm.view',
                        'label' => 'Lihat SDM Olahraga',
                        'desc' => 'Melihat data pelatih, wasit, juri, dan tenaga teknis',
                    ],
                    [
                        'name' => 'sdm.manage',
                        'label' => 'Kelola SDM Olahraga',
                        'desc' => 'Pendaftaran dan pembaruan lisensi/sertifikasi SDM',
                    ],
                    [
                        'name' => 'sarpras.view',
                        'label' => 'Lihat Sarana & Prasarana',
                        'desc' => 'Melihat direktori lapangan, gedung, dan fasilitas olahraga',
                    ],
                    [
                        'name' => 'sarpras.manage',
                        'label' => 'Kelola Sarana & Prasarana',
                        'desc' => 'Pencatatan fasilitas dan jadwal pemanfaatan sarpras',
                    ],
                ],
            ],
            [
                'id' => 'kejuaraan_prestasi_pembinaan',
                'label' => 'Kejuaraan, Prestasi & Pembinaan',
                'description' => 'Akses agenda event/kejuaraan, perolehan medali/prestasi, serta program pembinaan atlet.',
                'icon' => 'medal',
                'permissions' => [
                    [
                        'name' => 'kejuaraan.view',
                        'label' => 'Lihat Kejuaraan',
                        'desc' => 'Melihat kalender dan detail kompetisi olahraga',
                    ],
                    [
                        'name' => 'kejuaraan.manage',
                        'label' => 'Kelola Kejuaraan',
                        'desc' => 'Penyusunan event, jadwal, dan administrasi kejuaraan',
                    ],
                    [
                        'name' => 'prestasi.view',
                        'label' => 'Lihat Prestasi',
                        'desc' => 'Melihat rekam jejak juara dan perolehan medali',
                    ],
                    [
                        'name' => 'prestasi.manage',
                        'label' => 'Kelola Prestasi',
                        'desc' => 'Pencatatan perolehan medali dan sertifikat prestasi',
                    ],
                    [
                        'name' => 'pembinaan.view',
                        'label' => 'Lihat Program Pembinaan',
                        'desc' => 'Melihat program pelatihan, diklat, dan pembibitan atlet',
                    ],
                    [
                        'name' => 'pembinaan.manage',
                        'label' => 'Kelola Program Pembinaan',
                        'desc' => 'Penyelenggaraan program dan pendaftaran peserta diklat',
                    ],
                ],
            ],
            [
                'id' => 'verifikasi_data',
                'label' => 'Workflow Verifikasi',
                'description' => 'Akses antrean verifikasi usulan data dari pengurus cabor dan approval berjenjang.',
                'icon' => 'check-badge',
                'permissions' => [
                    [
                        'name' => 'verifikasi.view',
                        'label' => 'Lihat Antrean Verifikasi',
                        'desc' => 'Melihat data usulan yang menunggu validasi',
                    ],
                    [
                        'name' => 'verifikasi.manage',
                        'label' => 'Eksekusi Verifikasi',
                        'desc' => 'Memberikan persetujuan (approve), tolak, atau minta revisi',
                    ],
                ],
            ],
            [
                'id' => 'analitik_gis',
                'label' => 'Dashboard, Laporan & GIS',
                'description' => 'Akses visualisasi data statistik eksekutif, peta spasial sarpras, dan rekapitulasi data.',
                'icon' => 'chart-bar',
                'permissions' => [
                    [
                        'name' => 'dashboard.view',
                        'label' => 'Akses Dashboard',
                        'desc' => 'Melihat ringkasan metrik dan grafik statistik',
                    ],
                    [
                        'name' => 'laporan.view',
                        'label' => 'Akses & Cetak Laporan',
                        'desc' => 'Mengekspor laporan statistik dan rekapan data',
                    ],
                    [
                        'name' => 'gis.view',
                        'label' => 'Akses WebGIS Peta Olahraga',
                        'desc' => 'Melihat sebaran geografis fasilitas dan klub olahraga',
                    ],
                ],
            ],
            [
                'id' => 'administrasi_keamanan',
                'label' => 'Administrasi & Keamanan Sistem',
                'description' => 'Pengelolaan akun pengguna, pembagian hak akses matriks role, dan pemeriksaan jejak audit.',
                'icon' => 'shield-check',
                'permissions' => [
                    [
                        'name' => 'user.view',
                        'label' => 'Lihat Pengguna',
                        'desc' => 'Melihat daftar akun pengguna sistem SINDORA',
                    ],
                    [
                        'name' => 'user.manage',
                        'label' => 'Kelola Pengguna',
                        'desc' => 'Tambah akun, atur penugasan cabor, dan reset password',
                    ],
                    [
                        'name' => 'role.manage',
                        'label' => 'Kelola Hak Akses (ACL)',
                        'desc' => 'Mengatur matriks permissions untuk setiap role',
                    ],
                    [
                        'name' => 'audit.view',
                        'label' => 'Lihat Audit Trail',
                        'desc' => 'Memeriksa riwayat aktivitas dan log perubahan data',
                    ],
                ],
            ],
        ];
    }

    /**
     * Default permissions per role according to system specification.
     */
    private function getDefaultRolePermissions(): array
    {
        $allPermissions = Permission::pluck('name')->all();

        return [
            'super_admin' => $allPermissions,
            'verifikator' => [
                'wilayah.view',
                'organisasi.view',
                'cabor.view',
                'klub.view',
                'atlet.view',
                'sdm.view',
                'sarpras.view',
                'kejuaraan.view',
                'prestasi.view',
                'pembinaan.view',
                'verifikasi.view',
                'verifikasi.manage',
                'dashboard.view',
                'laporan.view',
                'gis.view',
                'audit.view',
            ],
            'pimpinan' => [
                'wilayah.view',
                'organisasi.view',
                'cabor.view',
                'klub.view',
                'atlet.view',
                'sdm.view',
                'sarpras.view',
                'kejuaraan.view',
                'prestasi.view',
                'pembinaan.view',
                'dashboard.view',
                'laporan.view',
                'gis.view',
                'audit.view',
            ],
            'pengurus_cabor' => [
                'wilayah.view',
                'organisasi.view',
                'cabor.view',
                'cabor.manage',
                'klub.view',
                'klub.manage',
                'atlet.view',
                'atlet.manage',
                'sdm.view',
                'sdm.manage',
                'sarpras.view',
                'sarpras.manage',
                'kejuaraan.view',
                'kejuaraan.manage',
                'prestasi.view',
                'prestasi.manage',
                'pembinaan.view',
                'pembinaan.manage',
                'verifikasi.view',
                'dashboard.view',
                'laporan.view',
                'gis.view',
            ],
        ];
    }
}
