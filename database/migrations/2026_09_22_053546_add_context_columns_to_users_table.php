<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('klub_id')->nullable()->after('email')
                ->constrained('klubs')->nullOnDelete();
            $table->foreignId('organisasi_id')->nullable()->after('klub_id')
                ->constrained('organisasis')->nullOnDelete();
            $table->foreignId('cabor_id')->nullable()->after('organisasi_id')
                ->constrained('cabors')->nullOnDelete()
                ->comment('konteks cabor untuk operator_organisasi');

            $table->index('klub_id');
            $table->index('organisasi_id');
            $table->index('cabor_id');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['klub_id']);
            $table->dropForeign(['organisasi_id']);
            $table->dropForeign(['cabor_id']);
            $table->dropColumn(['klub_id', 'organisasi_id', 'cabor_id']);
        });
    }
};
