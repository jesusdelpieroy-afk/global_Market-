<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Huella (SHA-256) de la imagen del comprobante: la misma foto no se puede usar en dos pedidos
            $table->string('comprobante_hash', 64)->nullable()->unique()->after('comprobante');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropUnique(['comprobante_hash']);
            $table->dropColumn('comprobante_hash');
        });
    }
};
