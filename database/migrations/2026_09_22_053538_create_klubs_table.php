<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('klubs', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->foreignId('cabor_id')->constrained('cabors')->cascadeOnDelete();
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahans')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete()
                ->comment('Denormalized for fast filter, sync from kelurahan');

            $table->text('alamat')->nullable();
            $table->string('ketua')->nullable();
            $table->string('kontak_hp')->nullable();
            $table->string('kontak_email')->nullable();

            // Profil & Legalitas
            $table->string('logo_path')->nullable();
            $table->string('nomor_sk')->nullable();
            $table->date('tanggal_sk')->nullable();
            $table->string('dokumen_legalitas_path')->nullable();
            $table->json('jadwal_latihan')->nullable()->comment('JSON: [{hari, jam_mulai, jam_selesai, lokasi}]');
            $table->text('deskripsi')->nullable();

            // Koordinat untuk GIS (opsional, utama di Sarpras tapi klub juga bisa dipetakan)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Workflow verifikasi
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['cabor_id', 'kelurahan_id']);
            $table->index('kecamatan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('klubs');
    }
};
