<?php

use App\Http\Controllers\AtletController;
use App\Http\Controllers\CaborController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GisController;
use App\Http\Controllers\KalenderController;
use App\Http\Controllers\KecamatanController;
use App\Http\Controllers\KejuaraanController;
use App\Http\Controllers\KelurahanController;
use App\Http\Controllers\KlubController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrganisasiController;
use App\Http\Controllers\PembinaanController;
use App\Http\Controllers\PrestasiController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SarprasController;
use App\Http\Controllers\SarprasJadwalController;
use App\Http\Controllers\SdmController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VerifikasiController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [LandingController::class, 'index'])->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Sprint 2: Manajemen Pengguna + Master Data (authorize via Policy inside controllers)
    Route::resource('users', UserController::class);
    Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    Route::resource('kecamatans', KecamatanController::class);
    Route::resource('kelurahans', KelurahanController::class);
    Route::resource('organisasis', OrganisasiController::class);
    Route::resource('cabors', CaborController::class);
    Route::resource('klubs', KlubController::class);
    Route::resource('atlets', AtletController::class);
    Route::resource('sdms', SdmController::class);
    Route::resource('sarpras', SarprasController::class)->parameters(['sarpras' => 'sarpras']);

    // Sprint 5: Kejuaraan & Prestasi & Pembinaan
    Route::resource('kejuaraans', KejuaraanController::class);
    Route::resource('prestasis', PrestasiController::class);
    Route::resource('pembinaans', PembinaanController::class);
    Route::post('pembinaans/{pembinaan}/peserta', [PembinaanController::class, 'syncPeserta'])->name('pembinaans.peserta.sync');

    // Sprint 4: Workflow Verifikasi
    Route::prefix('verifikasi')->name('verifikasi.')->group(function () {
        Route::get('/queue', [VerifikasiController::class, 'index'])->name('queue')->middleware('permission:verifikasi.view');
        Route::post('/submit', [VerifikasiController::class, 'submit'])->name('submit')->middleware('permission:klub.manage|atlet.manage|sdm.manage|sarpras.manage|kejuaraan.manage|prestasi.manage|pembinaan.manage');
        Route::post('/{entity}/{id}/approve', [VerifikasiController::class, 'approve'])->name('approve')->middleware('permission:verifikasi.manage');
        Route::post('/{entity}/{id}/reject', [VerifikasiController::class, 'reject'])->name('reject')->middleware('permission:verifikasi.manage');
        Route::post('/{entity}/{id}/request-revision', [VerifikasiController::class, 'requestRevision'])->name('requestRevision')->middleware('permission:verifikasi.manage');
    });

    // Sprint 5.5: Kalender Kegiatan
    Route::get('/kalender', [KalenderController::class, 'index'])->name('kalender.index');

    // Sprint 8: WebGIS — Peta Olahraga
    Route::get('/gis', [GisController::class, 'index'])->name('gis.index');

    // Sprint 5 3.2: Jadwal Pemanfaatan Sarpras
    Route::resource('sarpras-jadwals', SarprasJadwalController::class);

    // Sprint 5 4.5: Notifikasi
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

require __DIR__.'/auth.php';
