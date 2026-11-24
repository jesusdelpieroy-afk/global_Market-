<?php

namespace Database\Seeders;

use App\Models\Ajuste;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\PreguntaFrecuente;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Rol;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    // Datos de prueba (database/seeders/data/datos_prueba.php): php artisan migrate:fresh --seed
    // Se crean respetando los niveles de dependencia: primero las tablas sin claves foráneas.
    public function run(): void
    {
        $d = require database_path('seeders/data/datos_prueba.php');

        // Nivel 1
        $roles = collect($d['roles'])->mapWithKeys(fn ($r) => [$r['nombre'] => Rol::create($r)]);
        $categorias = collect($d['categorias'])->mapWithKeys(fn ($c) => [$c['nombre'] => Categoria::create($c)]);
        $clientes = collect($d['clientes'])->map(fn ($c) => Cliente::create($c));
        Ajuste::actual()->update($d['ajustes']);
        foreach ($d['preguntas'] as $i => [$pregunta, $respuesta]) {
            PreguntaFrecuente::create(['pregunta' => $pregunta, 'respuesta' => $respuesta, 'orden' => $i + 1]);
        }

        // Nivel 2: dependen del nivel 1 (la contraseña se guarda cifrada por el cast «hashed»)
        $usuarios = collect($d['usuarios'])->mapWithKeys(fn ($u) => [$u['email'] => User::create([
            'name' => $u['name'], 'email' => $u['email'], 'telefono' => $u['telefono'],
            'password' => $d['password'], 'rol_id' => $roles[$u['rol']]->id,
        ])]);
        $productos = collect($d['productos'])->mapWithKeys(fn ($p) => [$p['nombre'] => Producto::create([
            'categoria_id' => $categorias[$p['categoria']]->id, 'nombre' => $p['nombre'], 'descripcion' => $p['descripcion'],
            'precio' => $p['precio'], 'stock' => $p['stock'], 'imagen' => $p['imagen'] ?? null,
        ])]);

        // Nivel 3: promociones con su tabla pivote, y ventas
        foreach ($d['promociones'] as $p) {
            $promocion = Promocion::create(collect($p)->except('productos')->all());
            $promocion->productos()->sync(collect($p['productos'])->map(fn ($n) => $productos[$n]->id)->all());
        }

        foreach ($d['ventas'] as $v) {
            $venta = Venta::create([
                'cliente_id' => $v['cliente'] ? $clientes->firstWhere('dni', $v['cliente'])->id : null,
                'user_id' => $v['usuario'] ? $usuarios[$v['usuario']]->id : null,
                'fecha' => $v['fecha'],
                'estado' => $v['estado'],
            ]);

            // Nivel 4: el detalle depende de la venta y del producto
            foreach ($v['items'] as [$nombre, $cantidad, $precio]) {
                $detalle = new DetalleVenta([
                    'producto_id' => $productos[$nombre]->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                ]);
                $detalle->subtotal = $detalle->calcularSubtotal();
                $venta->detalles()->save($detalle);
            }

            // total = suma de subtotales; igv = total × 18 / 118 (el stock ya viene descontado en los datos)
            $venta->calcularTotal();
            $venta->save();
        }
    }
}
