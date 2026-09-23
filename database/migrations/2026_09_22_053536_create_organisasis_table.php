<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organisasis', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); // e.g. KONI, KORMI, NPCI, BAPOMI
            $table->string('singkatan')->nullable();
            $table->string('jenis')->nullable()->comment('KONI/KORMI/NPCI/BAPOMI/dll');
            $table->text('alamat')->nullable();
            $table->string('ketua')->nullable();
            $table->string('kontak_hp')->nullable();
            $table->string('kontak_email')->nullable();
            $table->string('logo_path')->nullable();
            $table->text('deskripsi')->nullable();

            // Workflow verifikasi
            $table->string('verification_status')->default('draft')->index()
                ->comment('draft|menunggu_verifikasi|terverifikasi|perlu_perbaikan|ditolak');
            $table->text('catatan_verifikator')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index('jenis');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organisasis');
    }
};
