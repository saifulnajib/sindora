<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlet_id')->constrained('atlets')->cascadeOnDelete();
            $table->foreignId('cabor_id')->constrained('cabors')->cascadeOnDelete();
            $table->foreignId('kejuaraan_id')->constrained('kejuaraans')->cascadeOnDelete();

            $table->string('kategori_kelas')->nullable()->comment('kelas tanding saat kejuaraan');
            $table->string('medali')->nullable()->index()
                ->comment('emas|perak|perunggu|juara_1|juara_2|juara_3|harapan|non_medali');
            $table->unsignedSmallInteger('peringkat')->nullable();
            $table->date('tanggal')->nullable()->comment('tanggal perolehan, fallback ke kejuaraan tanggal_mulai');

            $table->string('sertifikat_path')->nullable();
            $table->text('keterangan')->nullable();

            // Workflow verifikasi (transaksi penting)
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['atlet_id', 'kejuaraan_id']);
            $table->index(['cabor_id', 'medali']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestasis');
    }
};
