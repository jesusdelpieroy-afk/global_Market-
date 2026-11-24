<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Una sola fila: datos de la tienda y portada
        Schema::create('ajustes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 80);
            $table->string('descripcion_corta', 60)->nullable();
            $table->string('whatsapp', 20);
            $table->string('moneda', 4)->default('S/');
            $table->string('direccion', 120)->nullable();
            $table->string('horario', 100)->nullable();
            $table->string('portada_titulo', 80);
            $table->string('portada_texto', 260)->nullable();
            $table->string('portada_imagen1')->nullable();
            $table->string('portada_imagen2')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ajustes');
    }
};
