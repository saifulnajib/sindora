<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prestasis', function (Blueprint $table) {
            $table->string('nomor_sertifikat')->nullable()->after('sertifikat_path')->comment('nomor sertifikat resmi');
            $table->index('nomor_sertifikat');
        });
    }

    public function down(): void
    {
        Schema::table('prestasis', function (Blueprint $table) {
            $table->dropIndex(['nomor_sertifikat']);
            $table->dropColumn('nomor_sertifikat');
        });
    }
};
