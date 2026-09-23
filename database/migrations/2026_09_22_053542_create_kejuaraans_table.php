<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kejuaraans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('jenis')->default('kejuaraan')->index()
                ->comment('kejuaraan|kegiatan');
            $table->string('tingkat')->nullable()->index()
                ->comment('kecamatan|kabupaten_kota|provinsi|nasional|internasional');
            $table->string('penyelenggara')->nullable();
            $table->foreignId('organisasi_id')->nullable()->constrained('organisasis')->nullOnDelete();
            $table->foreignId('cabor_id')->nullable()->constrained('cabors')->nullOnDelete();
            $table->text('deskripsi')->nullable();
            $table->string('lokasi')->nullable();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->string('poster_path')->nullable();

            // Workflow (opsional, untuk verifikasi event)
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['tingkat', 'tanggal_mulai']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kejuaraans');
    }
};
