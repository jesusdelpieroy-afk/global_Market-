<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Nivel 2: users apunta a roles (Rol 1 — 0..* Usuario)
            $table->foreignId('rol_id')->after('id')
                ->constrained('roles')->restrictOnDelete();
            $table->string('telefono', 15)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rol_id');
            $table->dropColumn('telefono');
        });
    }
};
