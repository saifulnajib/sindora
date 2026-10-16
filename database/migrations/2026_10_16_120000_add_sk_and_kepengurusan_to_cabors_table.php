<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cabors', function (Blueprint $table) {
            $table->string('sk_nomor')->nullable()->after('logo_path');
            $table->date('sk_tgl_mulai')->nullable()->after('sk_nomor');
            $table->date('sk_tgl_selesai')->nullable()->after('sk_tgl_mulai');
            $table->string('sk_file_path')->nullable()->after('sk_tgl_selesai');
            $table->string('nama_ketua')->nullable()->after('sk_file_path');
            $table->string('kontak_pengurus')->nullable()->after('nama_ketua');
        });
    }

    public function down(): void
    {
        Schema::table('cabors', function (Blueprint $table) {
            $table->dropColumn([
                'sk_nomor',
                'sk_tgl_mulai',
                'sk_tgl_selesai',
                'sk_file_path',
                'nama_ketua',
                'kontak_pengurus',
            ]);
        });
    }
};
