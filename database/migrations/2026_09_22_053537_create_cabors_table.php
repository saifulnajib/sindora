<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisasi_id')->constrained('organisasis')->cascadeOnDelete();
            $table->string('nama'); // e.g. Pencak Silat, Sepak Bola
            $table->string('kode')->nullable()->unique();
            $table->string('kategori')->nullable()->comment('prestasi/massal/dll');
            $table->text('deskripsi')->nullable();
            $table->string('logo_path')->nullable();

            // Workflow
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index('organisasi_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cabors');
    }
};
