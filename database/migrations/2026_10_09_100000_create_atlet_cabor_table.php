<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atlet_cabor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('atlet_id')->constrained('atlets')->cascadeOnDelete();
            $table->foreignId('cabor_id')->constrained('cabors')->cascadeOnDelete();
            $table->foreignId('klub_id')->nullable()->constrained('klubs')->nullOnDelete();
            $table->foreignId('pelatih_id')->nullable()->constrained('sdms')->nullOnDelete();
            $table->string('kelas_tanding')->nullable();
            $table->string('status_pembinaan')->nullable()->default('daerah');
            $table->timestamps();

            $table->unique(['atlet_id', 'cabor_id']);
            $table->index(['cabor_id', 'klub_id']);
            $table->index('pelatih_id');
        });

        // Migrate existing atlets data to atlet_cabor
        $existingAtlets = DB::table('atlets')
            ->whereNotNull('cabor_id')
            ->select('id', 'cabor_id', 'klub_id', 'pelatih_id', 'kelas_tanding', 'status_pembinaan')
            ->get();

        foreach ($existingAtlets as $atlet) {
            DB::table('atlet_cabor')->insert([
                'atlet_id' => $atlet->id,
                'cabor_id' => $atlet->cabor_id,
                'klub_id' => $atlet->klub_id,
                'pelatih_id' => $atlet->pelatih_id,
                'kelas_tanding' => $atlet->kelas_tanding,
                'status_pembinaan' => $atlet->status_pembinaan ?? 'daerah',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Make cabor_id nullable on atlets
        Schema::table('atlets', function (Blueprint $table) {
            $table->unsignedBigInteger('cabor_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atlet_cabor');
    }
};
