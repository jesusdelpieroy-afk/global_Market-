<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Medios de pago que el encargado muestra al cliente (Yape, Plin, cuentas BCP, etc.)
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);              // Ej.: Yape, BCP Soles
            $table->string('titular', 100)->nullable(); // A nombre de quién está
            $table->string('numero', 40)->nullable();   // Celular o N.º de cuenta
            $table->string('cci', 40)->nullable();      // Cuenta interbancaria (opcional)
            $table->string('instrucciones', 200)->nullable();
            $table->string('imagen')->nullable();       // Foto del código QR (Yape/Plin)
            $table->boolean('activo')->default(true);
            $table->unsignedInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodos_pago');
    }
};
