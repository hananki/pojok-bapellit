<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah tipe kolom menjadi string agar fleksibel
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->change();
        });

        // 2. (Opsional) Jika sebelumnya ada user dengan role 'user', biarkan saja.
        // Sekarang database siap menerima role baru seperti 'pengelola_surat', dll.
    }

    public function down(): void
    {
        // Jika di-rollback, kembalikan ke ENUM semula
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'user') DEFAULT 'user'");
    }
};