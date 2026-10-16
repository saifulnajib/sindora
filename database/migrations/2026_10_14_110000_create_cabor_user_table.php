<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cabor_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cabor_id')->constrained('cabors')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['user_id', 'cabor_id']);
        });

        // Migrate any existing users with cabor_id into cabor_user pivot table
        $existing = \Illuminate\Support\Facades\DB::table('users')
            ->whereNotNull('cabor_id')
            ->select('id', 'cabor_id')
            ->get();

        foreach ($existing as $row) {
            \Illuminate\Support\Facades\DB::table('cabor_user')->insertOrIgnore([
                'user_id' => $row->id,
                'cabor_id' => $row->cabor_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cabor_user');
    }
};
