# Backlog Task Per Modul - SINDORA

> Turunan langsung dari `spesifikasi_kebutuhan_sindora.md`. Semua task menggunakan format: `ID | Task | Prioritas | Estimasi | Ketergantungan`
> **Tech Stack: Laravel 11 + Inertia.js + Vue 3 + Vite + Tailwind CSS + MySQL 8** — lihat `tech_stack.md` untuk detail.

---

## MODUL 0 - Fondasi & Cross-Cutting (Wajib Duluan)

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 0.1 | Setup Laravel 11 + Breeze Inertia Vue + Vite + Tailwind, `.env` MySQL, Pint, git flow | P0 | 2d | - | `composer create-project laravel/laravel`, `breeze --inertia-vue`, `npm install` |
| 0.2 | Design DB: ERD 10 entitas + relasi + index (FK, unique NIK) | P0 | 2d | - | MySQL 8, `utf8mb4`, InnoDB, `foreignId()->constrained()` |
| 0.3 | Migration + Seeder Wilayah (Kecamatan->Kelurahan Tanjungpinang) | P0 | 1d | 0.2 | `php artisan make:migration`, seeder real data Tanjungpinang |
| 0.4 | Implementasi Soft Deletes Laravel (`SoftDeletes` trait, `deleted_at`, global scope) | P0 | 1d | 0.2 | `softDeletes()` di migration + `use SoftDeletes` di Model, `withTrashed()` untuk Admin |
| 0.5 | Audit Trail (`audit_logs` table, Trait `Auditable` + Observer, `spatie/laravel-activitylog` alternatif) | P0 | 1.5d | 0.2 | Catat `user_id`, `event`, `auditable_type/id`, `old_values/new_values` |
| 0.6 | Setup file upload & storage (logo klub, foto atlet, dokumen legalitas) - `Storage::disk('public')` + validasi mime/size | P1 | 1d | 0.1 | `php artisan storage:link`, Vue File Input component |
| 0.7 | Masking/Enkripsi field sensitif (NIK, HP, alamat) - Cast `encrypted` + Accessor masking per Policy | P0 | 1.5d | - | `Custom Cast` + `Gate::allows('view-sensitive')`, masking di Resource/API |
| 0.8 | Layout & Design System - AppLayout.vue, Sidebar, Mobile-first Dashboard + Desktop-optimized Form (Tailwind) | P0 | 3d | - | Inertia Layout, shadcn-vue / HeadlessUI, responsive breakpoints |

## MODUL 1 - Auth & RBAC (5 Role) — `spatie/laravel-permission`

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 1.1 | Install `spatie/laravel-permission`, migrate `roles/permissions`, seeder 5 role + permission matrix | P0 | 1d | 0.2 | Roles: `super_admin`, `verifikator`, `operator_organisasi`, `operator_klub`, `viewer` |
| 1.2 | Auth: Laravel Breeze (Inertia) Login/Logout/Forgot/Reset + email verification | P0 | 2d | 1.1 | Inertia Vue pages `Auth/Login.vue`, session guard, `throttle` |
| 1.3 | Middleware `role`/`permission` + Policy per Model (`KlubPolicy`, `AtletPolicy` dll) + `HandleInertiaRequests` share role | P0 | 2d | 1.2 | `php artisan make:policy`, Gate di `AppServiceProvider`, props `auth.user.roles` ke Vue |
| 1.4 | Manajemen Pengguna (CRUD `UserController`, assign role via `syncRoles()`, reset password) - SuperAdmin only | P0 | 2d | 1.3 | Inertia `Users/Index.vue` + `Form.vue`, validasi `exists:roles` |
| 1.5 | Scope data: Global Scope `KlubScope` (operator_klub → `where klub_id = auth()->user()->klub_id`), Organisasi scope cabor | P0 | 2d | 1.3 | Query Scope di Model + `Auth::user()->hasRole()` di Controller |
| 1.6 | Halaman Profile & Ganti Password (Breeze `ProfileController`) | P1 | 0.5d | 1.2 | `Profile/Edit.vue` bawaan Breeze, tambah avatar |

## MODUL 2 - Master Data (Relasional) — Eloquent + Inertia Resource

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 2.1 | CRUD Wilayah (Kecamatan, Kelurahan) — `WilayahController` + `WilayahResource` | P0 | 1d | 0.3 | `hasMany` Kelurahan, eager load, pagination Inertia |
| 2.2 | CRUD Organisasi Olahraga (KONI, KORMI, NPCI, BAPOMI) | P0 | 1d | 1.3 | Enum `jenis_organisasi`, FormRequest validate |
| 2.3 | CRUD Cabor (`belongsTo` Organisasi) | P0 | 1d | 2.2 | `foreignId('organisasi_id')->constrained()` |
| 2.4 | CRUD Klub/Perkumpulan (relasi Cabor+Wilayah, upload logo/dokumen, jadwal latihan JSON) | P0 | 3d | 2.1-2.3 | `KlubController@index` inertia, `useForm` Vue, `Storage` upload |
| 2.5 | CRUD Atlet (relasi Klub+Cabor+Pelatih, NIK encrypted cast, kelas tanding enum, status pembinaan) | P0 | 3d | 2.4 | `Encrypted` cast NIK, Resource masking, `unique:atlets,nik` + soft delete rule |
| 2.6 | CRUD SDM Pelatih (lisensi, level, `tanggal_expired`, accessor `is_expired`) | P0 | 2d | 2.4 | Trait `HasLicense` + query `whereDate('expired_at','<',now())` |
| 2.7 | CRUD SDM Wasit/Juri | P0 | 1.5d | 2.6 | Reuse komponen Pelatih, beda `tipe_sdm` enum |
| 2.8 | CRUD SDM Tenaga Keolahragaan (Medis, Psikolog) | P1 | 1.5d | 2.6 | Sama, `tipe_sdm = tenaga` |
| 2.9 | Validasi & Form UX (FormRequest + Vue `useForm` errors, duplicate NIK check via `Rule::unique()->ignore()`) | P0 | 1.5d | 2.5-2.8 | Inertia error bag, `preserveScroll`, toast success |

## MODUL 3 - Sarana & Prasarana (Sarpras)

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 3.1 | CRUD Sarpras (Lat/Long `decimal(10,8)`, kondisi enum, foto) — `SarprasController` + `SarprasResource` | P0 | 2d | 2.1 | MySQL `decimal` untuk koordinat, `POINT` optional, `Storage` foto |
| 3.2 | Jadwal Pemanfaatan (`sarpras_jadwals` table, bentrok detection `whereBetween`) | P1 | 2d | 3.1 | Validasi overlap di FormRequest, Vue calendar component |
| 3.3 | Filter & Search (Kecamatan, Kondisi, Cabor) — Query scope + Inertia filter query string | P1 | 0.5d | 3.1 | `when($request->kondisi, fn)` + `preserveState` |
| 3.4 | Import/Export Excel (`maatwebsite/excel` - `SarprasImport`/`SarprasExport`) | P2 | 1d | 3.1 | `composer require maatwebsite/excel` |

## MODUL 4 - Workflow Verifikasi — Enum + Policy

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 4.1 | State machine: PHP 8.1 Enum `VerificationStatus: Draft/Menunggu/Terverifikasi/PerluPerbaikan/Ditolak` + kolom `verification_status` | P0 | 1.5d | 1.3 | `enum` cast di Model, `VerifikasiController` |
| 4.2 | Aksi Operator: `POST /verifikasi/submit` (single+bulk `whereIn()->update()`) | P0 | 1d | 4.1 | FormRequest `can:submit`, Inertia `router.post()` |
| 4.3 | Dashboard Verifikator: `VerifikatorQueue.vue` + filter entitas, pagination, `withCount` | P0 | 2d | 4.1 | `VerificationQueueController@index` scope `menunggu_verifikasi` |
| 4.4 | Aksi Verifikator: `approve/reject/requestRevision` + `catatan_verifikator` wajib (validate `required_if`) | P0 | 1.5d | 4.3 | Transaction + audit log, notif via `DB::table('notifications')` |
| 4.5 | Notifikasi in-app (tabel `notifications` Laravel, badge di AppLayout.vue, polling Inertia) | P1 | 1.5d | 4.4 | `php artisan notifications:table`, bell icon + badge |
| 4.6 | Riwayat & Timeline (`verification_logs` / `audit_logs` polymorphic) | P1 | 1d | 4.4 | `morphMany` + Timeline Vue component |
| 4.7 | Guard: Policy `update` cek `status === Terverifikasi → deny` + redirect minta revisi | P0 | 1d | 4.1 | `Gate::denyIf($model->isTerverifikasi())` |

## MODUL 5 - Transaksional (Kejuaraan, Prestasi, Pembinaan)

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 5.1 | CRUD Kejuaraan (`kejuaraans` table, tingkat enum, `tanggal_mulai/selesai`, lokasi) | P0 | 2d | 1.3 | `KejuaraanController` + `KejuaraanResource`, date cast |
| 5.2 | CRUD Prestasi (pivot Atlet-Cabor-Kejuaraan, `medali` enum, `sertifikat_path`, ikut workflow `verification_status`) | P0 | 2.5d | 4.1, 5.1 | `Prestasi` model `BelongsTo` semua, `PrestasiPolicy` cek workflow |
| 5.3 | CRUD Pembinaan (`pembinaans` + `anggaran decimal(15,2)`, `periode`, evaluasi) | P0 | 2d | 1.3 | `PembinaanController`, currency input Vue |
| 5.4 | Relasi Pembinaan -> Atlet/Klub/Cabor (`pembinaan_peserta` pivot polymorphic) | P1 | 1d | 5.3 | `belongsToMany`/`morphToMany` |
| 5.5 | Kalender Kegiatan (Vue `FullCalendar` atau custom, filter Cabor/Organisasi via query string) | P1 | 1.5d | 5.1 | Inertia props `events` JSON, `Kalender/Index.vue` |

## MODUL 6 - Dashboard & Analitik — Laravel + Vue Chart

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 6.1 | Widget Ringkasan (`DashboardController` aggregate `where verification_status='terverifikasi'`, `Cache::remember(60)`) | P0 | 1.5d | 2.5, 3.1 | MySQL `COUNT(*)`, Inertia `Dashboard/Index.vue` stat cards |
| 6.2 | Grafik Tren Medali (query `groupBy year/cabor`, `Chart.js`/`recharts` via Vue) | P0 | 1.5d | 5.2 | `Prestasi::selectRaw('YEAR(tanggal) as y, COUNT(*)')` |
| 6.3 | Rasio Pelatih:Atlet (`withCount`, Vue radial/bar) | P0 | 1d | 2.5, 2.6 | `Cabor::withCount(['atlets','pelatihs'])` |
| 6.4 | Distribusi Atlet per Kecamatan/Cabor (MySQL `groupBy` + Chart.js pie/bar) | P0 | 1.5d | 2.5 | Join `kelurahans->kecamatans` |
| 6.5 | Algoritma Cabor Unggulan (Service `CaborRankingService`, bobot di `config/sindora.php`) | P0 | 2d | 6.2-6.4 | `skor = w1*medali + w2*sarpras + w3*sdm`, sortable table |
| 6.6 | Notifikasi Lisensi Kadaluarsa (`php artisan schedule` daily, `Command CheckLicenseExpiry`, query `where expired_at between now() and +90d`) | P0 | 1.5d | 2.6-2.8 | `Scheduler` + `Queue` database, badge di Dashboard Vue |
| 6.7 | Dashboard Eksekutif read-only mobile-first (role `viewer`, `can:view-dashboard`, layout ringkas Tailwind) | P0 | 2d | 6.1-6.5 | `Eksekutif/Dashboard.vue` tanpa tombol CRUD, `Policy viewAny` |

## MODUL 7 - Pelaporan (Export) — `maatwebsite/excel` + `barryvdh/laravel-dompdf`

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 7.1 | Report Builder (`LaporanController`, filter query `when()`, Inertia `Laporan/Index.vue` + filter bar) | P0 | 2d | 2.x, 5.x | `scopeFilter()` di Model, `request()->validate()` |
| 7.2 | Export Excel (`maatwebsite/excel` `Export` class, template Kop Dispora, `WithHeadings`, `WithStyles`) | P0 | 2d | 7.1 | `composer require maatwebsite/excel`, `Excel::download()` |
| 7.3 | Export PDF (`barryvdh/laravel-dompdf`, Blade `pdf/laporan.blade.php`, landscape, Kop + ttd) | P0 | 2d | 7.1 | `composer require barryvdh/laravel-dompdf`, `Pdf::loadView()` |
| 7.4 | Preview di browser (Inertia table preview + `print` CSS sebelum download) | P1 | 1d | 7.1 | Vue table + pagination preview |
| 7.5 | Log Export (`audit_logs` event `export`, `user_id`, `payload`) | P1 | 0.5d | 7.2 | Observer atau manual `activity()->log()` |

## MODUL 8 - Peta Olahraga (WebGIS Ringan) — Vue + Leaflet

| ID | Task | Pri | Est | Dep | Catatan Stack |
|---|---|---|---|---|---|
| 8.1 | Integrasi `leaflet` + `vue-leaflet`/`@vue-leaflet/vue-leaflet`, OSM tiles, `GIS/Index.vue` | P0 | 1d | 3.1 | `npm i leaflet`, Vite import CSS, base map OSM gratis |
| 8.2 | Layer Marker Sarpras (props `sarpras` JSON dari `GisController`, `L.marker`, cluster `leaflet.markercluster`, icon by `kondisi`) | P0 | 1.5d | 8.1, 3.1 | `SarprasResource` include `lat`, `lng` |
| 8.3 | Layer Marker Klub (agregasi `withCount('atlets')`, warna by Cabor, cluster) | P0 | 1.5d | 8.1, 2.4 | `KlubResource` + `atlets_count` |
| 8.4 | Popup Detail (`L.popup` Vue slot, foto `Storage::url()`, kapasitas, kondisi, jumlah atlet) | P0 | 1d | 8.2, 8.3 | Inertia props, `v-html` popup content |
| 8.5 | Filter GIS (Inertia filter query `?cabor=&kecamatan=&kondisi=`, `watch` + `router.get` preserveState, sync list+map) | P0 | 1.5d | 8.2, 8.3 | `GisController@index` scope filter sama dengan 3.3 |
| 8.6 | Geocoding picker (Leaflet click → set `lat/lng` di `Sarpras/Form.vue`, bbox Tanjungpinang clamp) | P1 | 1d | 3.1 | `@click` map event, `v-model` lat/lng input |
| 8.7 | Overlay DOD (GeoJSON layer / indikator warna kecamatan, data `config/dod.php`) | P2 | 2d | 8.5 | `L.geoJSON` + choropleth |

---

## Ringkasan Estimasi Per Modul

| Modul | Jumlah Task | Estimasi Total |
|---|---|---|
| 0 Fondasi | 8 | 12.5d |
| 1 Auth & RBAC | 6 | 9.5d |
| 2 Master Data | 9 | 15d |
| 3 Sarpras | 4 | 5.5d |
| 4 Workflow | 7 | 9.5d |
| 5 Transaksional | 5 | 9d |
| 6 Dashboard & Analitik | 7 | 11d |
| 7 Pelaporan | 5 | 7.5d |
| 8 WebGIS | 7 | 9.5d |
| **TOTAL** | **58 task** | **~89 hari kerja (1 orang)** / **~30-35 hari kalender (tim 3 dev)** |

> Estimasi belum termasuk QA, UAT, dan buffer. Tambahkan +20% buffer untuk UAT & bugfix.
