<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kecamatans', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('kode')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('kelurahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kecamatan_id')->constrained('kecamatans')->cascadeOnDelete();
            $table->string('nama');
            $table->string('kode')->nullable()->unique();
            $table->timestamps();

            $table->index('kecamatan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelurahans');
        Schema::dropIfExists('kecamatans');
    }
};
