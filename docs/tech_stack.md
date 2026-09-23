# Tech Stack — SINDORA

> Stack resmi: **Laravel 11 + Inertia.js + Vue 3 + Vite + Tailwind CSS + MySQL 8**
> Dokumen ini mengikat `backlog_task_per_modul.md` dan `implementation_plan_sprint.md`.

## 1. Ringkasan Stack

| Layer | Teknologi | Versi | Alasan |
|---|---|---|---|
| **Backend** | Laravel | 11.x (PHP 8.2+) | Eloquent, Policy, SoftDeletes, Scheduler, Queue, built-in auth |
| **Frontend Adapter** | Inertia.js | ^1.0 (inertia-laravel ^1.0) | SPA tanpa API terpisah, pakai Controller+Resource biasa |
| **Frontend** | Vue | 3.x (Composition API + `<script setup>`) | Reaktif, ekosistem Leaflet/Chart.js matang |
| **Build** | Vite | ^5.x | Bawaan Laravel 11, HMR cepat |
| **Styling** | Tailwind CSS | ^3.4 | Mobile-first utility, konsisten dengan Breeze |
| **Auth Starter** | Laravel Breeze (Inertia Vue) | ^2.0 | Login/Register/Forgot/Profile siap pakai, Inertia Vue stack |
| **Database** | MySQL | 8.x (InnoDB, utf8mb4) | Relasional kuat, `decimal` Lat/Long, `softDeletes`, full-text optional |
| **RBAC** | spatie/laravel-permission | ^6.0 | Role & permission DB-driven, `hasRole()`/`can()` |
| **Export Excel** | maatwebsite/excel | ^3.1 | `Excel::download()`, `WithHeadings`, queue export |
| **Export PDF** | barryvdh/laravel-dompdf | ^3.0 | `Pdf::loadView('pdf.laporan')`, Kop Dispora |
| **GIS** | Leaflet + @vue-leaflet/vue-leaflet + markercluster | ^1.9 / ^0.10 | OSM gratis, ringan, Vue binding |
| **Chart** | chart.js + vue-chartjs | ^4.x | Tren medali, rasio, distribusi, Cabor Unggulan |
| **Audit** | Custom Trait `Auditable` + Observer (alternatif `spatie/activitylog`) | - | `audit_logs` polymorphic |
| **Lint/Format** | Laravel Pint + ESLint + Prettier | - | Konsistensi BE/FE |

## 2. Arsitektur Laravel + Inertia

```
Browser (Vue 3 SFC)
  ↕ Inertia (JSON + props, tanpa REST API terpisah)
Laravel Controller → FormRequest → Model (Eloquent) → Resource → Inertia::render('Page.vue', props)
  ↕
MySQL 8 (InnoDB)
  ↕ Storage (public disk), Queue (database), Scheduler (cron), Mail (optional)
```

**Alur request:** `routes/web.php` → `Controller@index` (authorize via Policy) → `Model::query()->filter()->paginate()` → `Resource::collection()` → `Inertia::render('Modul/Index.vue', ['data' => ...])` → Vue `useForm`/`router`.

**Tidak ada API terpisah** — semua via `web.php` + Inertia. Jika butuh API eksternal nanti, tambah `routes/api.php`.

## 3. Struktur Project (Laravel 11)

```
app/
  Http/
    Controllers/        # WilayahController, KlubController, AtletController, SarprasController, VerifikasiController, DashboardController, LaporanController, GisController
    Requests/           # StoreAtletRequest, UpdateKlubRequest (FormRequest + authorize + rules)
    Resources/          # AtletResource (masking NIK), KlubResource, SarprasResource
    Middleware/         # HandleInertiaRequests (share auth.user, roles, flash)
  Models/               # Wilayah, Organisasi, Cabor, Klub, Atlet, Sdm (Pelatih/Wasit/Tenaga), Sarpras, Kejuaraan, Prestasi, Pembinaan + traits
  Policies/             # KlubPolicy, AtletPolicy, SarprasPolicy (Gate)
  Services/             # CaborRankingService (algoritma unggulan)
  Traits/               # Auditable, HasLicense, HasVerificationStatus
  Enums/                # VerificationStatus.php (Draft, MenungguVerifikasi, Terverifikasi, PerluPerbaikan, Ditolak)
  Console/Commands/     # CheckLicenseExpiry.php
resources/
  js/
    Pages/              # Auth/Login.vue, Dashboard/Index.vue, Wilayah/Index.vue, Klub/Form.vue, Atlet/Index.vue, Sarpras/Form.vue, Verifikasi/Queue.vue, Laporan/Index.vue, GIS/Index.vue, Eksekutif/Dashboard.vue
    Layouts/            # AppLayout.vue, GuestLayout.vue
    Components/         # DataTable.vue, FormInput.vue, FileUpload.vue, ChartCard.vue, Timeline.vue, MapPicker.vue
    Composables/        # useFilters.js (Inertia filter + preserveState)
  views/pdf/            # laporan.blade.php (dompdf)
  css/app.css           # Tailwind directives
routes/
  web.php               # Semua route Inertia + `middleware(['auth','role:..'])`
  auth.php              # Breeze bawaan
database/
  migrations/           # 10 entitas + roles/permissions + audit_logs + notifications + softDeletes
  seeders/              # WilayahSeeder (Tanjungpinang real), RolePermissionSeeder (5 role), DemoSeeder
config/
  sindora.php           # bobot Cabor Unggulan w1/w2/w3, bbox Tanjungpinang, H- lisensi
  permission.php        # spatie
```

## 4. Database MySQL 8 — Konvensi

- **Engine:** InnoDB, **Charset:** `utf8mb4_unicode_ci` (di `config/database.php`).
- **PK:** `id` `bigIncrements`, **FK:** `foreignId('x_id')->constrained()->cascadeOnDelete()` atau `nullOnDelete()` + index.
- **Soft Delete:** `$table->softDeletes()` di semua tabel master/transaksional, Model `use SoftDeletes`, query default exclude trashed.
- **Verification:** `$table->string('verification_status')->default('draft')` + cast Enum di Model.
- **Koordinat:** `$table->decimal('latitude', 10, 8)->nullable()` + `longitude decimal(11,8)` (cukup presisi, `POINT` opsional jika butuh spatial index).
- **Enkripsi:** Kolom sensitif `nik`, `no_hp`, `alamat` → `encrypted` cast Laravel (`$casts = ['nik' => 'encrypted']`), MySQL simpan `TEXT`.
- **Audit:** `audit_logs` (`id`, `user_id`, `event`, `auditable_type`, `auditable_id`, `old_values JSON`, `new_values JSON`, `created_at`).
- **Index:** Index di `verification_status`, `kecamatan_id`, `cabor_id`, `expired_at` untuk filter cepat. `EXPLAIN` saat perf audit Sprint 8.

## 5. RBAC — 5 Role (spatie/laravel-permission)

| Role | Guard | Akses |
|---|---|---|
| `super_admin` | `*` | Semua + Manajemen Pengguna + Audit |
| `verifikator` | `approve/reject/requestRevision` | Antrian verifikasi, tidak bisa CRUD master |
| `operator_organisasi` | `scope cabor` | CRUD cabor naungannya + daftar kejuaraan |
| `operator_klub` | `scope klub_id` | CRUD klub sendiri (profil, atlet, pelatih, sarpras latihan, prestasi) |
| `viewer` | `read-only` | Dashboard Eksekutif, Analitik, GIS, Laporan (tanpa export sensitif) |

Implementasi:
- `HasRoles` trait di `User`, `syncRoles()` di `UserController`.
- `Middleware` `role:verifikator|super_admin` di route verifikasi.
- `Policy` per Model: `viewAny`, `view`, `create`, `update`, `delete`, `verify`.
- Inertia share: `HandleInertiaRequests::share()` kirim `auth.user.roles`, `auth.user.permissions`, `auth.can.*` untuk `v-if` di Vue.
- Scope: `KlubScope` global scope + `when(Auth::user()->hasRole('operator_klub'), fn)` di Controller.

## 6. Cross-Cutting

| Kebutuhan Spec | Implementasi Laravel |
|---|---|
| **Soft Deletes** | `SoftDeletes` trait + `deleted_at`, `withTrashed()` untuk Admin, `restore()` |
| **Audit Trail** | `Auditable` trait `booted()` hook `created/updated/deleted` → insert `audit_logs`; atau `spatie/activitylog` `LogsActivity` |
| **Masking NIK/HP/Alamat** | Cast `encrypted` + `AtletResource` cek `Gate::allows('view-sensitive', $atlet)` → mask `****1234` jika tidak boleh |
| **Notifikasi Lisensi** | `Command CheckLicenseExpiry` (`where expired_at between now() and +90d`) + `Scheduler daily` + `notifications` table + badge Vue |
| **File Upload** | `Storage::disk('public')`, `store('klub-logos')`, `Storage::url()`, validasi `mimes:jpg,png,pdf|max:2048` |
| **Responsivitas** | Tailwind `sm/md/lg` breakpoints, `AppLayout` mobile drawer, form desktop `grid-cols-2` |
| **Verifikasi Workflow** | Enum `VerificationStatus` + `VerifikasiController` + Policy `cannot update if terverifikasi` |

## 7. Setup Lokal (Sprint 1 — 0.1)

```bash
# Prasyarat: PHP 8.2+, Composer 2, Node 20+, MySQL 8
composer create-project laravel/laravel sindora
cd sindora
composer require laravel/breeze --dev
php artisan breeze:install vue --dark
composer require spatie/laravel-permission maatwebsite/excel barryvdh/laravel-dompdf
npm install leaflet @vue-leaflet/vue-leaflet leaflet.markercluster chart.js vue-chartjs
# .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sindora
DB_USERNAME=root
DB_PASSWORD=
php artisan migrate --seed
php artisan storage:link
npm run dev
php artisan serve
# Queue & Scheduler (dev)
php artisan queue:work
php artisan schedule:work
```

## 8. Konvensi Kode

- **Controller:** Tipis, delegasi ke `FormRequest` + `Service` jika logika berat (CaborRanking).
- **Validasi:** Selalu `FormRequest` (`authorize()` cek Policy, `rules()` return array), Vue `useForm` tampilkan `errors`.
- **Resource:** Selalu pakai `Resource` untuk masking & format tanggal (`$casts` di Model `datetime`).
- **Vue:** `<script setup>`, `defineProps`, `usePage()`, `router.get/post` dengan `preserveState/preserveScroll`.
- **Commit:** `feat(klub): CRUD Klub Inertia`, `fix(atlet): masking NIK viewer`, `chore: pint`.

## 9. Referensi

- `spesifikasi_kebutuhan_sindora.md` — sumber kebenaran fitur
- `backlog_task_per_modul.md` — backlog 58 task + kolom Catatan Stack
- `implementation_plan_sprint.md` — 8 Sprint (16 minggu) + Dependensi Package
