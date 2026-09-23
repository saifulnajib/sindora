<?php

return [
    /*
    |--------------------------------------------------------------------------
    | SINDORA — Sistem Informasi Keolahragaan Tanjungpinang
    | Sprint 1 config: bobot Cabor Unggulan, bbox GIS, license H thresholds
    |--------------------------------------------------------------------------
    */

    // Bobot Cabor Unggulan (used for scoring / ranking in Dashboard Eksekutif & Laporan)
    // Sum should be 100; adjusts priority of cabor excellence.
    'bobot_cabor_unggulan' => [
        'prestasi_nasional' => 40,   // medali PON/Kejuaraan Nasional
        'prestasi_provinsi' => 25,   // PORPROV / Kejurprov
        'pembinaan_aktif' => 20,     // jumlah atlet terdaftar & aktif latihan
        'sarpras_dukung' => 10,      // ketersediaan sarpras cabor
        'sdm_bersertifikat' => 5,    // pelatih/wasit bersertifikat
    ],

    // BBox Tanjungpinang (WGS84) for GIS / Leaflet map defaults
    // Source approx: Tanjungpinang city boundaries
    'bbox_tanjungpinang' => [
        // [lng_min, lat_min, lng_max, lat_max]
        'wgs84' => [104.30, 0.85, 104.55, 1.05],
        'center' => [
            'lat' => 0.917,
            'lng' => 104.45,
        ],
        'zoom_default' => 12,
        'zoom_min' => 10,
        'zoom_max' => 17,
        // Tile provider default
        'tile_url' => 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
        'attribution' => '&copy; OpenStreetMap contributors',
    ],

    // License H thresholds (Sertifikasi / Perpanjangan lisensi SDM)
    // Defines warning windows and hard expiry behaviour for pelatih/wasit/tenaga
    'license_h' => [
        'peringatan_hari' => env('SINDORA_LICENSE_WARNING_DAYS', 90), // H-90 mulai muncul badge kuning
        'kritis_hari' => env('SINDORA_LICENSE_CRITICAL_DAYS', 30),    // H-30 badge merah + notif
        'expired_grace_hari' => 0, // no grace; expired immediately
        'thresholds' => [
            'aman' => ['min' => 91, 'label' => 'Aman', 'color' => 'green', 'badge' => 'bg-green-100 text-green-800'],
            'peringatan' => ['min' => 31, 'max' => 90, 'label' => 'Perlu Perpanjangan', 'color' => 'yellow', 'badge' => 'bg-yellow-100 text-yellow-800'],
            'kritis' => ['min' => 1, 'max' => 30, 'label' => 'Segera Perpanjang', 'color' => 'orange', 'badge' => 'bg-orange-100 text-orange-800'],
            'expired' => ['max' => 0, 'label' => 'Kadaluarsa', 'color' => 'red', 'badge' => 'bg-red-100 text-red-800'],
        ],
    ],

    // Generic app metadata
    'app' => [
        'nama' => 'SINDORA',
        'nama_lengkap' => 'Sistem Informasi Keolahragaan Tanjungpinang',
        'instansi' => 'Dispora Kota Tanjungpinang',
        'tahun' => 2026,
        'versi_sprint' => 'Sprint 1 — 16 tables, 5 roles, 18 wilayah',
    ],

    // Wilayah seed counts (for Dashboard widget verification)
    'wilayah' => [
        'kecamatan_count' => 4,
        'kelurahan_count' => 18,
        'kota' => 'Tanjungpinang',
        'provinsi' => 'Kepulauan Riau',
    ],
];
