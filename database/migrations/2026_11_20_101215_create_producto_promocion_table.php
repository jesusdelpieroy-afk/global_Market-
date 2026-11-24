<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Relación muchos a muchos: tabla pivote en singular y orden alfabético
        Schema::create('producto_promocion', function (Blueprint $table) {
            $table->foreignId('producto_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promocion_id')->constrained('promociones')->cascadeOnDelete();
            $table->primary(['producto_id', 'promocion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_promocion');
    }
};
