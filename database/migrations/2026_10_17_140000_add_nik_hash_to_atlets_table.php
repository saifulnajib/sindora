<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('atlets', function (Blueprint $table) {
            $table->string('nik_hash', 64)->nullable()->index()->after('nik');
        });

        // Populate nik_hash for existing records
        $atlets = DB::table('atlets')->select('id', 'nik')->whereNotNull('nik')->get();
        foreach ($atlets as $a) {
            try {
                $plain = Crypt::decryptString($a->nik);
                $hash = hash_hmac('sha256', trim($plain), config('app.key'));
                DB::table('atlets')->where('id', $a->id)->update(['nik_hash' => $hash]);
            } catch (\Throwable $e) {
                // If already plain or decryption fails, fallback
            }
        }
    }

    public function down(): void
    {
        Schema::table('atlets', function (Blueprint $table) {
            $table->dropColumn('nik_hash');
        });
    }
};
