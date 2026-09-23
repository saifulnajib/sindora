<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atlets', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->text('nik')->comment('encrypted cast, TEXT for encrypted value');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('jenis_kelamin')->nullable()->comment('L|P');
            $table->text('alamat')->nullable();
            $table->text('no_hp')->nullable();
            $table->string('email')->nullable();

            // Relasi
            $table->foreignId('klub_id')->nullable()->constrained('klubs')->nullOnDelete();
            $table->foreignId('cabor_id')->constrained('cabors')->cascadeOnDelete();
            $table->foreignId('pelatih_id')->nullable()->constrained('sdms')->nullOnDelete()
                ->comment('FK to sdms where tipe=pelatih');
            $table->foreignId('kelurahan_id')->nullable()->constrained('kelurahans')->nullOnDelete();

            $table->string('kelas_tanding')->nullable()->comment('kelas/ nomor pertandingan');
            $table->string('status_pembinaan')->default('Daerah')->index()
                ->comment('Daerah|Provinsi|Nasional');
            $table->decimal('berat_badan', 5, 2)->nullable();
            $table->decimal('tinggi_badan', 5, 2)->nullable();
            $table->string('foto_path')->nullable();

            // Workflow
            $table->string('verification_status')->default('draft')->index();
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['klub_id', 'cabor_id']);
            $table->index('pelatih_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atlets');
    }
};
