<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sarpras', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis')->nullable()->comment('lapangan/gedung/kolam/stadion/dll');
            $table->text('alamat')->nullable();
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahans')->nullOnDelete();
            $table->foreignId('kecamatan_id')->nullable()->constrained('kecamatans')->nullOnDelete();
            $table->foreignId('klub_id')->nullable()->constrained('klubs')->nullOnDelete()
                ->comment('pengelola / pemilik');
            $table->foreignId('cabor_id')->nullable()->constrained('cabors')->nullOnDelete();

            // Koordinat presisi MySQL decimal untuk GIS
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->string('kondisi')->default('baik')->index()
                ->comment('baik|rusak_ringan|rusak_berat');
            $table->integer('kapasitas')->nullable();
            $table->string('foto_path')->nullable();
            $table->text('deskripsi')->nullable();
            $table->json('fasilitas')->nullable();

            // Workflow
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['jenis', 'kondisi']);
            $table->index('kelurahan_id');
        });

        // Jadwal pemanfaatan sarpras (opsional, untuk deteksi bentrok)
        Schema::create('sarpras_jadwals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sarpras_id')->constrained('sarpras')->cascadeOnDelete();
            $table->foreignId('klub_id')->nullable()->constrained('klubs')->nullOnDelete();
            $table->string('hari')->nullable()->comment('senin..minggu atau tanggal spesifik');
            $table->date('tanggal')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('kegiatan')->nullable();
            $table->timestamps();

            $table->index(['sarpras_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sarpras_jadwals');
        Schema::dropIfExists('sarpras');
    }
};
