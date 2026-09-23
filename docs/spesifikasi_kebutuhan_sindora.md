# Spesifikasi Kebutuhan Sistem SINDORA
**Sistem Informasi Data Olahraga Daerah Kota Tanjungpinang**

## 1. Visi dan Tujuan Proyek
Aplikasi berbasis web untuk menghimpun, mengelola, memverifikasi, menyajikan, dan menganalisis data olahraga secara terintegrasi. 
**Konsep Utama:** DATA -> INFORMASI -> ANALISIS -> KEBIJAKAN -> PRESTASI.

## 2. Hak Akses & Peran Pengguna (RBAC)
Sistem menggunakan *Role-Based Access Control* dengan 5 tingkatan:
1. **Super Admin / Admin Dispora**: Hak akses penuh (Master Data, Pengaturan Sistem, Manajemen Pengguna, Audit Trail).
2. **Verifikator Dispora**: Validasi data (Setujui, Tolak, Minta Revisi) atas inputan dari organisasi/klub.
3. **Operator Organisasi (KONI/KORMI/Pengkot)**: Mengelola cabor di bawah naungannya, mendaftarkan agenda kegiatan/kejuaraan.
4. **Operator Klub/Perkumpulan**: Entri data klub (profil, pelatih, atlet, sarana latihan, pengajuan prestasi).
5. **Pimpinan/Eksekutif (Viewer)**: Akses *read-only* ke Dashboard Eksekutif, Analitik, Pemetaan (GIS), dan Laporan.

## 3. Entitas Data Utama (Relasional)
Berikut adalah gambaran relasi data (*Entity Relationship*) yang harus dibangun:
*   **Wilayah**: Kecamatan -> Kelurahan.
*   **Organisasi Olahraga**: Data KONI, KORMI, NPCI, BAPOMI, dll.
*   **Cabang Olahraga (Cabor)**: Berelasi dengan Organisasi Pembina.
*   **Klub/Perkumpulan**: Berelasi dengan Cabor dan Wilayah. Memiliki profil, legalitas, jadwal latihan.
*   **Atlet**: Berelasi dengan Klub, Cabor, dan Pelatih. Memiliki data personal, kelas tanding, dan status pembinaan (Daerah/Provinsi/Nasional).
*   **SDM Olahraga**: Terdiri dari Pelatih, Wasit/Juri, dan Tenaga Keolahragaan (Medis, Psikolog, dll). Memiliki data lisensi, level, masa berlaku lisensi (butuh notifikasi kadaluarsa).
*   **Sarana & Prasarana (Sarpras)**: Data fasilitas, koordinat (Lat/Long), kondisi fisik, dan jadwal pemanfaatan.
*   **Kejuaraan & Kegiatan**: Data event/kalender olahraga.
*   **Prestasi**: Tabel transaksi yang menghubungkan Atlet, Cabor, Kejuaraan, dan perolehan medali.
*   **Pembinaan**: Data program, target, anggaran, dan evaluasi.

## 4. Modul dan Fitur Fungsional

### A. Modul Dashboard & Analitik
*   **Widget Ringkasan**: Total Atlet, Pelatih, Wasit, Sarpras, Klub.
*   **Grafik Analitik**: Tren perolehan medali, rasio pelatih:atlet, distribusi atlet per kecamatan/cabor.
*   **Cabor Unggulan**: Pemeringkatan cabor berdasarkan algoritma jumlah medali, ketersediaan sarpras, dan SDM.

### B. Modul Manajemen Data (CRUD & Input)
*   Form input untuk semua entitas di atas (Atlet, Pelatih, Wasit, Tenaga Medis, Klub, Sarpras).
*   **Notifikasi Lisensi**: Sistem otomatis menandai/memberi peringatan untuk lisensi SDM yang akan habis masa berlakunya.
*   **Audit Trail**: Mencatat log aktivitas (siapa, kapan, data apa yang diubah).

### C. Modul Alur Verifikasi (Workflow)
*   Status Data: `Draft`, `Menunggu Verifikasi`, `Terverifikasi`, `Perlu Perbaikan`, `Ditolak`.
*   Terdapat form catatan/alasan dari Verifikator jika data berstatus `Perlu Perbaikan` atau `Ditolak`.

### D. Modul Peta Olahraga (WebGIS Ringan)
*   **Peta Tematik**: Menggunakan library seperti Leaflet.js atau Mapbox.
*   **Layer Data**: Menampilkan titik koordinat (*marker*) untuk lokasi Sarpras dan penyebaran Klub.
*   **Interaktivitas**: *Popup* detail saat marker diklik (menampilkan kapasitas sarpras, kondisi, atau jumlah atlet klub).
*   **Filter GIS**: Berdasarkan Cabor, Kecamatan, atau Kondisi Sarpras.

### E. Modul Pelaporan (Export)
*   Laporan profil keolahragaan daerah.
*   Filter rentang waktu dan jenis data.
*   Export output ke PDF dan Excel (.xlsx).

## 5. Keamanan & Non-Fungsionalitas
*   **Privasi Data**: Enkripsi/Masking data sensitif atlet (NIK, No. HP, Alamat lengkap) untuk *role* selain Admin/Operator Klub terkait.
*   **Responsivitas**: Desain UI/UX *Mobile-first* untuk Dashboard Eksekutif, dan *Desktop-optimized* untuk formulir input tabel besar.
*   **Soft Deletes**: Data tidak dihapus permanen dari database, melainkan disembunyikan (*is_deleted = true*).

## 6. Tahapan Eksekusi (Roadmap Antigravity)
1.  **Tahap 1 (Base DB & RBAC)**: Setup autentikasi, otorisasi 5 level *role*, dan *scaffolding* CRUD dasar (Cabor, Organisasi, Wilayah, Klub, Atlet, Pelatih, Sarpras).
2.  **Tahap 2 (Workflow & Transaksional)**: Pembuatan modul Verifikasi Data (status workflow), Input Prestasi, Kejuaraan, dan Pembinaan.
3.  **Tahap 3 (Dashboard & Pelaporan)**: Pembuatan algoritma chart/statistik, peringatan lisensi, dan fitur Export PDF/Excel.
4.  **Tahap 4 (GIS & Integrasi)**: Implementasi Peta Spasial (WebGIS) dan integrasi parameter Desain Olahraga Daerah (DOD).