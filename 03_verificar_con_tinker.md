# Verificación con Tinker (guía 11, Paso 9)

```
php artisan migrate:fresh --seed
php artisan tinker
```
```php
$p = App\Models\Producto::first();
$p->categoria->nombre;                       // relación belongsTo
App\Models\Categoria::first()->productos()->count();   // relación hasMany
$cli = App\Models\Cliente::create(['nombre' => 'Luis Quispe']);
$v = App\Models\Venta::create(['cliente_id' => $cli->id, 'fecha' => now()]);
$d = $v->detalles()->create(['producto_id' => $p->id, 'cantidad' => 2, 'precio_unitario' => 24.90, 'subtotal' => 49.80]);
$d->calcularSubtotal();   // 49.8
$d->validarStock();       // true
$v->calcularTotal();      // 49.8   (IGV 18 % incluido en $v->igv)
$v->save();
$p->categoria->delete();  // ✗ error 1451: la categoría tiene productos (restrict)
$v->delete();             // ✓ borra la venta...
App\Models\DetalleVenta::count();  // 0 ...y sus detalles (cascade = composición)
```
