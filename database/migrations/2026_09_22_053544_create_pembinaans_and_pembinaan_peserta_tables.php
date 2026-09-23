<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembinaans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_program');
            $table->text('deskripsi')->nullable();
            $table->text('target')->nullable();
            $table->decimal('anggaran', 15, 2)->nullable();
            $table->string('sumber_anggaran')->nullable();
            $table->string('tahun_anggaran')->nullable();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();
            $table->text('evaluasi')->nullable();
            $table->string('status')->default('draft')->index()
                ->comment('draft|berjalan|selesai|dibatalkan');
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->foreignId('organisasi_id')->nullable()->constrained('organisasis')->nullOnDelete();
            $table->foreignId('cabor_id')->nullable()->constrained('cabors')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['periode_mulai', 'periode_selesai']);
        });

        // Pivot polymorphic: pembinaan_peserta -> peserta bisa Atlet/Klub/SDM
        Schema::create('pembinaan_peserta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembinaan_id')->constrained('pembinaans')->cascadeOnDelete();
            $table->string('peserta_type')->comment('App\\Models\\Atlet|Klub|Sdm');
            $table->unsignedBigInteger('peserta_id');
            $table->string('peran')->nullable()->comment('peserta/pelatih/pendamping');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->unique(['pembinaan_id', 'peserta_type', 'peserta_id'], 'pembinaan_peserta_unique');
            $table->index(['peserta_type', 'peserta_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembinaan_peserta');
        Schema::dropIfExists('pembinaans');
    }
};
