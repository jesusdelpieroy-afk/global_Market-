<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Código único que el cliente muestra en la tienda (se genera al subir el comprobante)
            $table->string('codigo', 20)->nullable()->unique()->after('id');
            $table->string('metodo_pago', 60)->nullable()->after('estado');
            $table->string('codigo_operacion', 40)->nullable()->index()->after('metodo_pago');
            $table->string('comprobante')->nullable()->after('codigo_operacion'); // imagen en storage privado
            $table->dateTime('comprobante_at')->nullable()->after('comprobante');
            // Entrega en tienda: se rellena cuando el encargado valida el código
            $table->dateTime('entregado_at')->nullable()->after('comprobante_at');
            $table->foreignId('entregado_por')->nullable()->after('entregado_at')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('entregado_por');
            $table->dropUnique(['codigo']);
            $table->dropIndex(['codigo_operacion']);
            $table->dropColumn(['codigo', 'metodo_pago', 'codigo_operacion', 'comprobante', 'comprobante_at', 'entregado_at']);
        });
    }
};
