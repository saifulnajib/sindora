# ERD SINDORA — Sistem Informasi Data Olahraga Daerah

> Generated: 2026-09-22 | Stack: Laravel 12 + MySQL 8 InnoDB utf8mb4_unicode_ci | 10 Entitas + RBAC + Audit

## Ringkasan Entitas (10)

| # | Entitas | Tabel | Relasi Utama | Catatan |
|---|---|---|---|---|
| 1 | Wilayah | `kecamatans`, `kelurahans` | Kecamatan 1—N Kelurahan | Kelurahan FK kecamatan_id |
| 2 | Organisasi | `organisasis` | — | KONI/KORMI/NPCI/BAPOMI, jenis |
| 3 | Cabor | `cabors` | N—1 Organisasi | FK organisasi_id |
| 4 | Klub | `klubs` | N—1 Cabor, N—1 Kelurahan/Kecamatan | jadwal_latihan JSON, legalitas, lat/long |
| 5 | Atlet | `atlets` | N—1 Klub, N—1 Cabor, N—1 Pelatih(SDM) | nik encrypted TEXT, status_pembinaan enum |
| 6 | SDM | `sdms` | N—1 Cabor, N—1 Klub | single table tipe {pelatih,wasit,tenaga}, expired_at |
| 7 | Sarpras | `sarpras` + `sarpras_jadwals` | N—1 Kelurahan/Klub/Cabor | lat decimal(10,8) lng decimal(11,8), kondisi enum |
| 8 | Kejuaraan | `kejuaraans` | N—1 Organisasi/Cabor | tingkat enum, tanggal_mulai/selesai |
| 9 | Prestasi | `prestasis` | N—1 Atlet, Cabor, Kejuaraan | transaksi, medali enum, sertifikat_path |
| 10 | Pembinaan | `pembinaans` + `pembinaan_peserta` | N—1 Organisasi/Cabor, N—N Peserta polymorphic | anggaran decimal(15,2), pivot peserta_type/id |
| + | Audit | `audit_logs` | polymorphic auditable | user_id, event, old/new JSON |
| + | User context | `users` | N—1 Klub/Organisasi/Cabor | klub_id, organisasi_id, cabor_id nullable FK |
| + | RBAC | spatie: `roles`, `permissions` etc | — | 2026_09_22_053534_create_permission_tables |

Semua tabel master/transaksi: `softDeletes()`, `string verification_status default 'draft' indexed`, `catatan_verifikator`, `verified_at/by`, `timestamps`.

Indexes: `verification_status`, `expired_at` (sdms), `kecamatan_id`, `cabor_id`.

Enkripsi: `atlets.nik`, `sdms.nik`, `atlets.no_hp` via Laravel `encrypted` cast (DB TEXT).

## Mermaid ERD

```mermaid
erDiagram
    users ||--o{ klubs : "klub_id nullable"
    users ||--o{ organisasis : "organisasi_id nullable"
    users ||--o{ cabors : "cabor_id nullable"
    users ||--o{ audit_logs : "user_id"

    kecamatans ||--o{ kelurahans : "kecamatan_id FK"
    kecamatans ||--o{ klubs : "kecamatan_id"
    kecamatans ||--o{ sarpras : "kecamatan_id"
    kelurahans ||--o{ klubs : "kelurahan_id"
    kelurahans ||--o{ atlets : "kelurahan_id"
    kelurahans ||--o{ sdms : "kelurahan_id"
    kelurahans ||--o{ sarpras : "kelurahan_id"

    organisasis ||--o{ cabors : "organisasi_id"
    organisasis ||--o{ kejuaraans : "organisasi_id"
    organisasis ||--o{ pembinaans : "organisasi_id"
    organisasis ||--o{ users : "organisasi_id"

    cabors ||--o{ klubs : "cabor_id"
    cabors ||--o{ atlets : "cabor_id"
    cabors ||--o{ sdms : "cabor_id"
    cabors ||--o{ sarpras : "cabor_id"
    cabors ||--o{ kejuaraans : "cabor_id"
    cabors ||--o{ prestasis : "cabor_id"
    cabors ||--o{ pembinaans : "cabor_id"
    cabors ||--o{ users : "cabor_id"

    klubs ||--o{ atlets : "klub_id"
    klubs ||--o{ sdms : "klub_id"
    klubs ||--o{ sarpras : "klub_id pemilik"
    klubs ||--o{ sarpras_jadwals : "klub_id"
    klubs ||--o{ users : "klub_id"

    sdms ||--o{ atlets : "pelatih_id"
    %% sdms tipe = pelatih|wasit|tenaga, self not hierarchical

    sarpras ||--o{ sarpras_jadwals : "sarpras_id"

    kejuaraans ||--o{ prestasis : "kejuaraan_id"
    atlets ||--o{ prestasis : "atlet_id"

    pembinaans ||--o{ pembinaan_peserta : "pembinaan_id"
    %% pembinaan_peserta polymorphic: peserta_type = Atlet|Klub|Sdm, peserta_id

    audit_logs }o--|| users : "user_id nullable"
    audit_logs }o--o{ kecamatans : "auditable polymorphic"
    audit_logs }o--o{ kelurahans : "auditable"
    audit_logs }o--o{ organisasis : "auditable"
    audit_logs }o--o{ cabors : "auditable"
    audit_logs }o--o{ klubs : "auditable"
    audit_logs }o--o{ sdms : "auditable"
    audit_logs }o--o{ atlets : "auditable"
    audit_logs }o--o{ sarpras : "auditable"
    audit_logs }o--o{ kejuaraans : "auditable"
    audit_logs }o--o{ prestasis : "auditable"
    audit_logs }o--o{ pembinaans : "auditable"

    kecamatans {
        bigint id PK
        string nama
        string kode UK
        timestamps
    }
    kelurahans {
        bigint id PK
        bigint kecamatan_id FK
        string nama
        string kode UK
    }
    organisasis {
        bigint id PK
        string nama
        string singkatan
        string jenis
        string verification_status IDX "draft|menunggu_verifikasi|terverifikasi|perlu_perbaikan|ditolak"
        text catatan_verifikator
        datetime verified_at
        bigint verified_by FK users
        softDeletes deleted_at
    }
    cabors {
        bigint id PK
        bigint organisasi_id FK
        string nama
        string kode UK
        string verification_status IDX
        softDeletes
    }
    klubs {
        bigint id PK
        bigint cabor_id FK
        bigint kelurahan_id FK
        bigint kecamatan_id FK
        string nama
        json jadwal_latihan
        string nomor_sk
        date tanggal_sk
        string dokumen_legalitas_path
        decimal latitude "10,8"
        decimal longitude "11,8"
        string verification_status IDX
        softDeletes
    }
    sdms {
        bigint id PK
        string nama
        string tipe IDX "pelatih|wasit|tenaga"
        text nik "encrypted"
        bigint cabor_id FK
        bigint klub_id FK
        string nomor_lisensi
        string level
        date expired_at IDX
        string verification_status IDX
        softDeletes
    }
    atlets {
        bigint id PK
        string nama
        text nik "encrypted TEXT"
        bigint klub_id FK
        bigint cabor_id FK
        bigint pelatih_id FK "-> sdms"
        string kelas_tanding
        string status_pembinaan IDX "Daerah|Provinsi|Nasional"
        string verification_status IDX
        softDeletes
    }
    sarpras {
        bigint id PK
        string nama
        string jenis
        bigint kelurahan_id FK
        decimal latitude "10,8"
        decimal longitude "11,8"
        string kondisi IDX "baik|rusak_ringan|rusak_berat"
        int kapasitas
        string verification_status IDX
        softDeletes
    }
    sarpras_jadwals {
        bigint id PK
        bigint sarpras_id FK
        bigint klub_id FK
        date tanggal
        time jam_mulai
        time jam_selesai
    }
    kejuaraans {
        bigint id PK
        string nama
        string jenis IDX "kejuaraan|kegiatan"
        string tingkat IDX
        string penyelenggara
        bigint organisasi_id FK
        date tanggal_mulai
        date tanggal_selesai
        string verification_status IDX
        softDeletes
    }
    prestasis {
        bigint id PK
        bigint atlet_id FK
        bigint cabor_id FK
        bigint kejuaraan_id FK
        string medali IDX "emas|perak|perunggu|..."
        string verification_status IDX
        string sertifikat_path
        softDeletes
    }
    pembinaans {
        bigint id PK
        string nama_program
        decimal anggaran "15,2"
        date periode_mulai
        date periode_selesai
        string status IDX
        string verification_status IDX
        softDeletes
    }
    pembinaan_peserta {
        bigint id PK
        bigint pembinaan_id FK
        string peserta_type "Atlet|Klub|Sdm"
        bigint peserta_id
        string peran
        unique pembinaan_id_peserta_type_peserta_id
    }
    audit_logs {
        bigint id PK
        bigint user_id FK
        string event IDX
        string auditable_type IDX
        bigint auditable_id IDX
        json old_values
        json new_values
        timestamps
    }
    users {
        bigint id PK
        string name
        string email UK
        bigint klub_id FK
        bigint organisasi_id FK
        bigint cabor_id FK
    }
```

## Urutan Migrasi (Timestamp)

```
0001_01_01_000000_create_users_table.php (existing)
2026_09_22_053534_create_permission_tables.php (existing)
2026_09_22_053535_create_kecamatans_and_kelurahans_tables.php
2026_09_22_053536_create_organisasis_table.php
2026_09_22_053537_create_cabors_table.php
2026_09_22_053538_create_klubs_table.php
2026_09_22_053539_create_sdms_table.php  (sebelum atlets karena atlet.pelatih_id -> sdms)
2026_09_22_053540_create_atlets_table.php
2026_09_22_053541_create_sarpras_table.php (sarpras + sarpras_jadwals)
2026_09_22_053542_create_kejuaraans_table.php
2026_09_22_053543_create_prestasis_table.php
2026_09_22_053544_create_pembinaans_and_pembinaan_peserta_tables.php
2026_09_22_053545_create_audit_logs_table.php
2026_09_22_053546_add_context_columns_to_users_table.php (klub_id, organisasi_id, cabor_id)
```

Foreign key `cascadeOnDelete` untuk master hierarki (kecamatan->kelurahan, organisasi->cabor, cabor->klub), `nullOnDelete` untuk konteks pengguna & relasi opsional agar tidak hapus massal.

## Catatan Implementasi Model (next step)

- Semua model `use SoftDeletes`, `HasFactory` jika perlu.
- `VerificationStatus` PHP 8.1 Enum: `cases: Draft, MenungguVerifikasi, Terverifikasi, PerluPerbaikan, Ditolak` + cast di Model `protected $casts = ['verification_status' => VerificationStatus::class]`.
- Encrypted cast: `protected $casts = ['nik' => 'encrypted', 'no_hp' => 'encrypted']` untuk Atlet/SDM; di migration simpan sebagai TEXT.
- Sdms accessor `isExpired => expired_at?->isPast()` + scope `whereExpired`.
- Audit: Trait `Auditable` boot `created/updated/deleted` → insert `audit_logs` (atau spatie/activitylog).

## Validasi MySQL 8

- Engine InnoDB, Charset utf8mb4, Collation utf8mb4_unicode_ci (config/database.php).
- Decimal lat/long: `10,8` / `11,8` presisi ~1mm.
- Anggaran: `decimal(15,2)`.
- Tidak menjalankan `php artisan migrate` pada tahap ini — file siap untuk `migrate:fresh --seed` di Sprint 1.
