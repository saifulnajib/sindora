<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sdms', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('tipe')->index()->comment('pelatih|wasit|tenaga');
            $table->text('nik')->nullable()->comment('encrypted cast');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable()->comment('L|P');
            $table->text('alamat')->nullable();
            $table->text('no_hp')->nullable()->comment('encrypted/masked');
            $table->string('email')->nullable();

            $table->foreignId('cabor_id')->nullable()->constrained('cabors')->nullOnDelete();
            $table->foreignId('klub_id')->nullable()->constrained('klubs')->nullOnDelete();
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahans')->nullOnDelete();

            $table->string('foto_path')->nullable();

            // Lisensi & Kompetensi
            $table->string('nomor_lisensi')->nullable();
            $table->string('level')->nullable()->comment('daerah/provinsi/nasional/internasional');
            $table->string('kategori_tenaga')->nullable()->comment('medis/psikolog/gizi/dll untuk tipe=tenaga');
            $table->string('spesialisasi')->nullable();
            $table->date('tanggal_terbit')->nullable();
            $table->date('expired_at')->nullable()->index()->comment('masa_berlaku lisensi, untuk notifikasi');
            $table->string('sertifikat_path')->nullable();
            $table->text('deskripsi')->nullable();

            // Workflow
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['tipe', 'cabor_id']);
            $table->index(['tipe', 'expired_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sdms');
    }
};
