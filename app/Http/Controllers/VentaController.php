<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Venta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

// Las ventas nacen de dos maneras: el cliente envía su pedido desde la tienda (queda «pendiente»,
// ver TiendaController@pedido) o el encargado registra una venta de mostrador desde el panel (store()).
class VentaController extends Controller
{
    // IGV del Perú: el precio de venta ya lo incluye, por eso se separa del total (total × 18 / 118)
    public const IGV = 18;

    // READ: listado (con cliente y vendedor cargados: sin problema N+1)
    public function index(): View
    {
        $ventas = Venta::with(['cliente', 'usuario'])->withCount('detalles')->latest('fecha')->latest('id')->get();
        $ajuste = Ajuste::actual();

        return view('gestion.ventas.index', compact('ventas', 'ajuste'));
    }

    // Formulario para registrar una venta (detalle dinámico)
    public function create(): View
    {
        $ajuste = Ajuste::actual();
        $clientes = Cliente::orderBy('nombre')->get();

        // El precio mostrado ya incluye la promoción vigente; las promociones se consultan una sola vez
        $promos = Promocion::vigentes()->with('productos:id')->get();
        $productos = Producto::where('stock', '>', 0)->orderBy('nombre')->get()
            ->each(fn (Producto $p) => $p->precio_venta = $p->precioFinal($promos));

        return view('gestion.ventas.create', compact('clientes', 'productos', 'ajuste'));
    }

    // CREATE: guarda la venta con su detalle dentro de una transacción (todo o nada)
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|distinct|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1|max:999',
        ], [
            'items.required' => 'Debe agregar al menos un producto.',
            'items.min' => 'Debe agregar al menos un producto.',
            'items.*.producto_id.required' => 'Elija un producto en cada fila.',
            'items.*.producto_id.distinct' => 'No repita el mismo producto: aumente la cantidad.',
        ]);

        $venta = DB::transaction(function () use ($request) {
            // 1. Crear la venta (total e IGV se calculan al final)
            $venta = Venta::create([
                'cliente_id' => $request->cliente_id,
                'user_id' => auth()->id(), // el vendedor es quien tiene la sesión iniciada
                'fecha' => now(),          // hora de Perú (config/app.php: America/Lima)
                'estado' => 'confirmado',  // venta de mostrador: se confirma y descuenta el stock al registrarla
                'total' => 0,
                'igv' => 0,
            ]);

            $promos = Promocion::vigentes()->with('productos:id')->get();
            $total = 0;

            // 2. Registrar cada producto vendido
            foreach ($request->items as $item) {
                // lockForUpdate(): bloquea la fila hasta terminar; dos cajeros no venden la misma última unidad
                $producto = Producto::lockForUpdate()->find($item['producto_id']);

                if ($producto->stock < $item['cantidad']) {
                    // La excepción deshace todo lo guardado en la transacción (rollback)
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$producto->nombre} (disponible: {$producto->stock}).",
                    ]);
                }

                // El precio sale de la base de datos, nunca del formulario
                $precio = $producto->precioFinal($promos);
                $subtotal = round($precio * $item['cantidad'], 2);

                $venta->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ]);

                // 3. Descontar el stock
                $producto->actualizarStock((int) $item['cantidad']);
                $total += $subtotal;
            }

            // 4. Guardar total e IGV (el precio ya incluye el 18 %)
            $venta->update([
                'total' => round($total, 2),
                'igv' => round($total * self::IGV / (100 + self::IGV), 2),
            ]);

            return $venta;
        });

        return to_route('ventas.show', $venta)->with('ok', "Venta #{$venta->id} registrada correctamente.");
    }

    // READ: detalle de la venta (la composición Venta ◆— DetalleVenta)
    public function show(Venta $venta): View
    {
        $venta->load('cliente', 'usuario', 'detalles.producto');
        $ajuste = Ajuste::actual();

        return view('gestion.ventas.show', compact('venta', 'ajuste'));
    }

    // UPDATE: cambia el estado de un pedido. Al confirmar se descuenta el stock; al revertir se repone.
    public function update(Request $request, Venta $venta): RedirectResponse
    {
        $nuevo = $request->validate(['estado' => 'required|in:'.implode(',', Venta::ESTADOS)])['estado'];
        $antes = $venta->estado;

        if ($nuevo === $antes) {
            return back()->with('ok', 'El pedido ya tenía ese estado.');
        }

        try {
            DB::transaction(function () use ($venta, $nuevo, $antes) {
                $venta->load('detalles.producto');

                if ($nuevo === 'confirmado') {
                    foreach ($venta->detalles as $d) {
                        if (! $d->validarStock()) { // método del diagrama
                            throw new \DomainException("Stock insuficiente de «{$d->producto->nombre}».");
                        }
                    }
                    foreach ($venta->detalles as $d) {
                        $d->producto->actualizarStock($d->cantidad); // método del diagrama
                    }
                    $venta->user_id = auth()->id();
                } elseif ($antes === 'confirmado') {
                    foreach ($venta->detalles as $d) {
                        $d->producto->reponerStock($d->cantidad);
                    }
                }

                $venta->estado = $nuevo;
                $venta->save();
            });
        } catch (\DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('ok', "Pedido #{$venta->id} marcado como {$nuevo}.");
    }

    // DELETE: anular = devolver el stock (si estaba confirmada) y eliminar; los detalles se borran con la venta
    public function destroy(Venta $venta): RedirectResponse
    {
        DB::transaction(function () use ($venta) {
            if ($venta->estado === 'confirmado') {
                foreach ($venta->detalles()->with('producto')->get() as $d) {
                    $d->producto->reponerStock($d->cantidad);
                }
            }
            // cascadeOnDelete() de la migración elimina los detalles
            $venta->delete();
        });

        return to_route('ventas.index')->with('ok', 'Venta anulada. El stock fue devuelto.');
    }
}
