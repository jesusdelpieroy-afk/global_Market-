<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Deja cargado el QR de Yape (public/uploads/pagos/qr-yape.png) si aún no hay ningún método de pago.
    // Se puede cambiar o quitar desde el panel: Configuraciones › Métodos de pago.
    public function up(): void
    {
        if (DB::table('metodos_pago')->count() === 0) {
            DB::table('metodos_pago')->insert([
                'nombre' => 'Yape',
                'instrucciones' => 'Escanea el QR con Yape y paga el total de tu pedido.',
                'imagen' => 'uploads/pagos/qr-yape.png',
                'activo' => true,
                'orden' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('metodos_pago')->where('imagen', 'uploads/pagos/qr-yape.png')->delete();
    }
};
