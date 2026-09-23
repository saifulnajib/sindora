# Implementation Plan Sprint - SINDORA

> Roadmap eksekusi mengacu pada `spesifikasi_kebutuhan_sindora.md` Bab 6 (4 Tahap) yang dipecah menjadi **8 Sprint x 2 minggu** (total 16 minggu / 4 bulan). Tim asumsi: 3 Dev (1 BE, 1 FE, 1 Fullstack/QA) + 1 PO.
> **Stack: Laravel 11 + Inertia.js + Vue 3 + Vite + Tailwind + MySQL 8** — detail di `tech_stack.md` & `backlog_task_per_modul.md`.

**Konvensi Prioritas:** P0 = Must-have, P1 = Should-have, P2 = Nice-to-have  
**Definition of Done:** `php artisan migrate:fresh --seed` OK, `pint` pass, `npm run build` pass, RBAC/soft-delete/audit terpenuhi, Policy test manual + bukti screenshot, docs update.

---

## Tahap 1 — Base DB & RBAC (Sprint 1-3)

### Sprint 1 — Fondasi & Auth (Minggu 1-2)
**Goal:** `laravel new` + Breeze Inertia Vue jalan, MySQL migrate OK, 5 role bisa login.

| Task ID | Task | Owner | DoD |
|---|---|---|---|
| 0.1 | Setup Laravel 11 + Breeze Inertia Vue + Vite+Tailwind, MySQL `.env`, Pint, git flow | BE/FE | `composer create-project`, `breeze:install vue`, `npm run dev` OK, CI Pint pass |
| 0.2 | ERD + Migration awal 10 entitas (MySQL InnoDB, `utf8mb4`, FK index) | BE | `migrate:fresh` OK, ERD di `docs/erd.png`, `SHOW CREATE TABLE` cek FK |
| 0.3 | Seeder Wilayah Kecamatan->Kelurahan Tanjungpinang | BE | `db:seed` OK, dropdown Wilayah di `Atlet/Form.vue` tampil |
| 0.7 | Cast `encrypted` + masking accessor NIK/HP/alamat | BE | Unit test `AtletResource` masking untuk role non-admin |
| 0.8 | AppLayout.vue + Sidebar + Design System Tailwind (mobile-first) | FE | Layout Inertia share `auth.user`, responsive test HP/desktop |
| 1.1 | `spatie/laravel-permission` + seeder 5 role + permission matrix | BE | `hasRole()` OK, 5 akun demo di `DatabaseSeeder` |
| 1.2 | Breeze Auth (Login/Logout/Forgot/Reset, throttle) | FE+BE | E2E login 5 role via Inertia `Auth/Login.vue` |

**Stack Sprint 1:** `composer require spatie/laravel-permission`, `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`

**Deliverable:** URL staging / login, akun demo 5 role.  
**Risiko:** Perubahan ERD → mitigasi: freeze ERD di akhir Sprint 1, perubahan lewat ADR.

### Sprint 2 — RBAC & Master Data Inti (Minggu 3-4)
**Goal:** Policy + Scope + SoftDeletes + Audit jalan, CRUD Wilayah/Organisasi/Cabor via Inertia.

| Task ID | Task | Owner | Stack Note |
|---|---|---|---|
| 1.3 | Middleware `role`/`permission` + Policy per Model + `HandleInertiaRequests` share role | BE | `make:policy`, Gate di `AppServiceProvider`, Vue `v-if="page.props.auth.can.klub_create"` |
| 1.4 | Manajemen Pengguna (`UserController`, `syncRoles()`) | FE+BE | `Users/Index.vue`, `Users/Form.vue` Inertia `useForm` |
| 1.5 | Global Scope Klub/Organisasi (`KlubScope`, `hasRole` di Controller) | BE | `addGlobalScope`, `Auth::user()->klub_id` |
| 0.4 | SoftDeletes trait (`deleted_at`, `withTrashed` untuk Admin) | BE | `softDeletes()` + `use SoftDeletes` semua Model master |
| 0.5 | Audit Trail (`Auditable` trait + Observer / `spatie/activitylog`) | BE | `audit_logs` polymorphic, `created`/`updated` hook |
| 2.1 | CRUD Wilayah (Kecamatan/Kelurahan) | FE+BE | `WilayahController` + `WilayahResource` + `Index.vue` pagination |
| 2.2 | CRUD Organisasi (KONI/KORMI/NPCI) | FE+BE | Enum `jenis_organisasi`, `FormRequest` |
| 2.3 | CRUD Cabor (`belongsTo` Organisasi) | FE+BE | `foreignId('organisasi_id')->constrained()` |
| 1.6 | Profile & Ganti Password (Breeze `ProfileController`) | FE | `Profile/Edit.vue` bawaan Breeze |

**Deliverable:** Demo CRUD Wilayah/Organisasi/Cabor dengan audit log terlihat.  
**QA Focus:** Test matrix 5 role x 3 modul (harus 403 jika tidak berhak).

### Sprint 3 — Klub, Atlet, SDM (Minggu 5-6)
**Goal:** Entitas utama lengkap via Eloquent + Inertia `useForm`, upload jalan.

| Task ID | Task | Stack Note |
|---|---|---|
| 0.6 | File upload `Storage::disk('public')` + `storage:link` + validasi mime/size | `enctype` Inertia, Vue FileInput, `Storage::url()` |
| 2.4 | CRUD Klub (legalitas, jadwal JSON, upload dokumen) | `KlubController`, `KlubResource`, `Klub/Form.vue` |
| 2.5 | CRUD Atlet (NIK encrypted cast, masking di Resource, kelas tanding enum) | `Encrypted` cast + `Rule::unique()->ignore()` + soft delete |
| 2.6 | CRUD Pelatih (`tanggal_expired`, `is_expired` accessor) | Trait `HasLicense`, `date` cast |
| 2.7 | CRUD Wasit/Juri | Reuse komponen Pelatih, `tipe_sdm` enum |
| 2.8 | CRUD Tenaga Keolahragaan | Sama, `tipe_sdm=tenaga` |
| 2.9 | Validasi FormRequest + Vue `useForm` errors + toast | `preserveScroll`, `errorBag`, duplicate NIK check |
| 3.1 | CRUD Sarpras (`decimal(10,8)` Lat/Long, foto, kondisi enum) | `SarprasResource` include lat/lng untuk GIS nanti |

**Deliverable:** UAT Tahap 1 dengan Dispora - input 10 klub + 30 atlet dummy.  
**Exit Criteria Tahap 1:** Semua CRUD P0 lolos UAT, audit log & soft delete verified.

---

## Tahap 2 — Workflow & Transaksional (Sprint 4-5)

### Sprint 4 — Workflow Verifikasi (Minggu 7-8)
**Goal:** Enum `VerificationStatus` + Policy guard + antrian Verifikator jalan end-to-end (Inertia).

| Task ID | Task | Stack Note |
|---|---|---|
| 4.1 | PHP Enum `VerificationStatus` + `verification_status` cast di Model | `enum VerificationStatus: string`, `casts = ['status' => VerificationStatus::class]` |
| 4.2 | `POST /verifikasi/submit` single+bulk (`whereIn()->update()`) | `VerifikasiController@submit`, `router.post()` Vue |
| 4.3 | `VerifikatorQueue.vue` + filter entitas, `withCount`, pagination | `VerificationQueueController@index` scope `menunggu_verifikasi` |
| 4.4 | `approve/reject/requestRevision` + `catatan_verifikator required_if` | `DB::transaction` + `audit_logs`, `validate required_if:status,perlu_perbaikan` |
| 4.6 | Timeline `verification_logs` polymorphic + Vue Timeline | `morphMany`, component `Timeline.vue` |
| 4.7 | Policy guard `Terverifikasi → deny update` | `Gate::denyIf($model->status->isTerverifikasi())` |

**Deliverable:** Simulasi: Operator Klub submit 5 atlet → Verifikator approve/reject → Operator lihat catatan.  
**Dependensi Kritis:** 4.x memblokir 5.2 (Prestasi), jadi harus done sebelum Sprint 5.

### Sprint 5 — Kejuaraan, Prestasi, Pembinaan (Minggu 9-10)
**Goal:** Transaksional + Kalender + Notifikasi in-app Laravel.

| Task ID | Task | Stack Note |
|---|---|---|
| 5.1 | CRUD Kejuaraan (`tanggal_mulai/selesai` cast, tingkat enum) | `KejuaraanController` + `KejuaraanResource` |
| 5.2 | CRUD Prestasi (ikut workflow `verification_status`, `sertifikat_path`) | `Prestasi` belongsTo Atlet/Cabor/Kejuaraan, `PrestasiPolicy` |
| 5.3 | CRUD Pembinaan (`anggaran decimal(15,2)`, evaluasi) | `PembinaanController`, currency Vue input |
| 5.4 | Pivot `pembinaan_peserta` (`morphToMany` Atlet/Klub/Cabor) | `belongsToMany`/`morphToMany` |
| 5.5 | Kalender (`FullCalendar` Vue, props `events` JSON dari Controller) | `Kalender/Index.vue`, filter `?cabor=` |
| 4.5 | Notifikasi in-app (`notifications` table Laravel, bell + badge di AppLayout.vue) | `php artisan notifications:table`, `Notifiable` trait |
| 3.2 | Jadwal Sarpras (`sarpras_jadwals`, bentrok `whereBetween`) | Validasi overlap di FormRequest |

**Deliverable:** UAT Tahap 2 - skenario kejuaraan + input prestasi + verifikasi prestasi.

---

## Tahap 3 — Dashboard & Pelaporan (Sprint 6-7)

### Sprint 6 — Dashboard & Analitik (Minggu 11-12)
**Goal:** `DashboardController` aggregate MySQL + `Cache::remember` + Chart.js Vue + `CaborRankingService`.

| Task ID | Task | Stack Note |
|---|---|---|
| 6.1 | Widget Ringkasan (`where verification_status='terverifikasi'`, `Cache::remember(60)`) | MySQL `COUNT(*)`, `Dashboard/Index.vue` stat cards |
| 6.2 | Tren Medali (`groupBy YEAR`, Chart.js) | `Prestasi::selectRaw('YEAR(tanggal) as y, COUNT(*)')` |
| 6.3 | Rasio Pelatih:Atlet (`withCount`) | `Cabor::withCount(['atlets','pelatihs'])` |
| 6.4 | Distribusi per Kecamatan/Cabor (join kelurahan→kecamatan) | `join` MySQL + Chart.js pie/bar |
| 6.5 | `CaborRankingService` (`w1*medali+w2*sarpras+w3*sdm`, `config/sindora.php`) | Service class, sortable table |
| 6.6 | `Command CheckLicenseExpiry` + `Scheduler daily` + queue `database` | `where expired_at between now() and +90d`, badge Vue |
| 6.7 | `Eksekutif/Dashboard.vue` read-only mobile-first (role `viewer`) | `can:view-dashboard`, tanpa tombol CRUD |

**Deliverable:** Demo Dashboard ke Pimpinan (Viewer) di HP + desktop.  
**Stack:** `npm i chart.js vue-chartjs`, `Cache` MySQL/file (Redis optional).

### Sprint 7 — Pelaporan Export (Minggu 13-14)
**Goal:** `LaporanController` + `maatwebsite/excel` + `barryvdh/dompdf` + preview Inertia.

| Task ID | Task | Stack Note |
|---|---|---|
| 7.1 | Report Builder (`scopeFilter when()`, `Laporan/Index.vue` filter bar) | `request()->validate()`, query string Inertia |
| 7.2 | Excel Export (`maatwebsite/excel` `WithHeadings`+`WithStyles`, Kop Dispora) | `composer require maatwebsite/excel`, `Excel::download()` |
| 7.3 | PDF Export (`barryvdh/laravel-dompdf`, Blade `pdf/laporan.blade.php` landscape) | `composer require barryvdh/laravel-dompdf`, `Pdf::loadView()` |
| 7.4 | Preview Vue table + `print` CSS | Pagination preview sebelum download |
| 7.5 | Log Export (`audit_logs` event `export`) | `activity()->log('export laporan')` |
| 3.4 | Import/Export Sarpras Excel | `SarprasImport`/`SarprasExport` class |
| 3.3 | Filter & Search Sarpras (query scope + `preserveState`) | `when($request->kondisi, fn)` |

**Deliverable:** UAT Tahap 3 - Pimpinan export laporan profil + verifikasi hasil PDF/Excel.

---

## Tahap 4 — GIS & Integrasi (Sprint 8)

### Sprint 8 — WebGIS + Hardening (Minggu 15-16)
**Goal:** `GisController` + `vue-leaflet` + OSM + filter sync + hardening Laravel.

| Task ID | Task | Stack Note |
|---|---|---|
| 8.1 | `leaflet` + `@vue-leaflet/vue-leaflet`, OSM tiles, `GIS/Index.vue` | `npm i leaflet`, Vite CSS import |
| 8.2 | Layer Sarpras (`L.marker` + `markercluster`, icon by `kondisi`, props `SarprasResource`) | `GisController` return `lat/lng` JSON |
| 8.3 | Layer Klub (`withCount('atlets')`, warna by Cabor) | `KlubResource` + `atlets_count` |
| 8.4 | Popup (`L.popup` slot, `Storage::url()` foto, kapasitas/kondisi) | Vue `v-html` popup |
| 8.5 | Filter GIS (`?cabor=&kecamatan=&kondisi=`, `router.get`+`preserveState`) | Scope filter sama dengan 3.3 |
| 8.6 | Picker Lat/Long (Leaflet `@click` → `v-model` di `Sarpras/Form.vue`, bbox Tanjungpinang) | Map click event |
| 8.7 | Overlay DOD GeoJSON (`L.geoJSON` choropleth, `config/dod.php`) | P2 - bisa geser Phase 2 |
| 0.x | Hardening: `throttle:api`, MySQL index, `php artisan optimize`, backup `mysqldump`, perf audit | `RateLimiter`, `EXPLAIN` query, Lighthouse |

**Stack Sprint 8:** `npm i leaflet @vue-leaflet/vue-leaflet leaflet.markercluster`

**Deliverable:** Demo GIS ke Dispora + UAT akhir.  
**Go-Live Checklist:** Seeder produksi, akun 5 role produksi, backup harian, SOP verifikasi.

---

## Timeline Gantt (Ringkas)

```
Minggu: 1  2  3  4  5  6  7  8  9  10 11 12 13 14 15 16
S1 Fondasi+Auth      [####]
S2 RBAC+Master           [####]
S3 Klub/Atlet/SDM            [####]
S4 Workflow                     [####]
S5 Transaksional                   [####]
S6 Dashboard                          [####]
S7 Pelaporan                             [####]
S8 GIS+Hardening                            [####]
UAT              ^         ^          ^         ^ Go-Live
```

---

## Dependensi Kritis & Urutan Wajib

```
0.2 ERD -> semua modul
1.3 RBAC -> 2.x, 3.x, 4.x, 5.x
4.1 State Machine -> 4.2-4.7, 5.2
2.4 Klub -> 2.5 Atlet, 2.6-2.8 SDM
5.1 Kejuaraan -> 5.2 Prestasi -> 6.2 Tren Medali -> 6.5 Cabor Unggulan
3.1 Sarpras -> 8.2 GIS Sarpras, 6.5
```

## Mitigasi Risiko

| Risiko | Dampak | Mitigasi (Laravel) |
|---|---|---|
| Perubahan ERD | Rework migrasi | Freeze ERD Sprint 1, ADR, `php artisan make:migration` incremental |
| Data sensitif bocor | Privasi | `Encrypted` cast + `Resource` masking + `Policy` test per role |
| Koordinat tidak akurat | GIS meleset | Picker + validasi `between` bbox Tanjungpinang di `FormRequest` |
| Export PDF timeout | 504 | `queue:work` database, `Excel::queue()`, limit 5k rows, `chunk()` |
| Scope creep DOD | Sprint molor | 8.7 P2 geser Phase 2, `config/dod.php` placeholder dulu |

## Dependensi Package Laravel

| Package | Versi | Untuk |
|---|---|---|
| `spatie/laravel-permission` | ^6.0 | RBAC 5 role |
| `maatwebsite/excel` | ^3.1 | Export/Import Excel |
| `barryvdh/laravel-dompdf` | ^3.0 | Export PDF |
| `spatie/laravel-activitylog` (opsional) | ^4.8 | Alternatif Audit Trail |
| `leaflet` + `@vue-leaflet/vue-leaflet` | ^1.9 / ^0.10 | WebGIS |
| `chart.js` + `vue-chartjs` | ^4.x | Dashboard grafik |

## Backlog Phase 2 (Out of Scope Sprint 1-8)

- Notifikasi WA/Email lisensi & verifikasi
- SSO / integrasi SIPD
- Mobile App (terpisah dari mobile-first web)
- Analitik lanjutan (prediksi prestasi ML)

---

## Cara Pakai Dokumen Ini

1. Import task dari `backlog_task_per_modul.md` ke tracker (Jira/Trello/Linear) - ID sudah siap.
2. Setiap Sprint Planning: ambil baris Sprint terkait, breakdown jadi subtask FE/BE/QA.
3. Setiap Sprint Review: demo Deliverable kolom, update status di tracker.
4. UAT tiap akhir Tahap (Sprint 3,5,7,8) - libatkan Dispora & Pimpinan.
