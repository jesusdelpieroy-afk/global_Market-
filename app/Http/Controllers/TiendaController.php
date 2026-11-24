<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\MetodoPago;
use App\Models\PreguntaFrecuente;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Venta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TiendaController extends Controller
{
    // Página de CLIENTES: catálogo, buscador, carrito y pedido por WhatsApp
    public function index(): View
    {
        $a = Ajuste::actual();
        $data = $this->datosTienda($a);

        $boot = [
            'base' => rtrim(asset('/'), '/').'/',
            'pedidoUrl' => route('tienda.pedido'),
            'pagoUrl' => route('tienda.pago'),
            'catalogoUrl' => route('tienda.catalogo'),
            'version' => $this->version($data),
            'data' => $data,
        ];

        return view('tienda.index', compact('a', 'boot'));
    }

    // Los clientes que están en la página consultan esto cada pocos segundos: si el encargado cambió
    // algo (precio, stock, promos, portada, métodos de pago...) reciben los datos nuevos sin recargar.
    // Con ?v=<versión> solo se envía el catálogo completo cuando de verdad cambió.
    public function catalogo(Request $request): JsonResponse
    {
        $data = $this->datosTienda(Ajuste::actual());
        $version = $this->version($data);

        $resp = $request->query('v') === $version
            ? ['changed' => false, 'version' => $version]
            : ['changed' => true, 'version' => $version, 'data' => $data];

        return response()->json($resp)->header('Cache-Control', 'no-store');
    }

    private function version(array $data): string
    {
        return md5(json_encode($data));
    }

    // Misma estructura de datos que usaba el prototipo en localStorage, ahora desde MySQL
    private function datosTienda(Ajuste $a): array
    {
        $data = [
            'config' => [
                'name' => $a->nombre,
                'tagline' => $a->descripcion_corta,
                'whatsapp' => $a->whatsapp,
                'currency' => $a->moneda,
                'heroTitle' => $a->portada_titulo,
                'heroText' => $a->portada_texto,
                'heroImage1' => $a->portadaUrl(1),
                'heroImage2' => $a->portadaUrl(2),
                'address' => $a->direccion,
                'hours' => $a->horario,
                'payments' => (Schema::hasTable('metodos_pago') ? MetodoPago::activos()->get() : collect())->map(fn ($m) => [
                    'id' => (string) $m->id,
                    'name' => $m->nombre,
                    'holder' => $m->titular ?? '',
                    'number' => $m->numero ?? '',
                    'cci' => $m->cci ?? '',
                    'notes' => $m->instrucciones ?? '',
                    'image' => $m->imagenUrl() ?? '',
                ])->all(),
                'faqs' => PreguntaFrecuente::orderBy('orden')->get()
                    ->map(fn ($f) => ['q' => $f->pregunta, 'a' => $f->respuesta])->all(),
            ],
            'categories' => Categoria::orderBy('id')->pluck('nombre')->all(),
            'products' => Producto::with('categoria')->orderBy('id')->get()->map(fn ($p) => [
                'id' => (string) $p->id,
                'name' => $p->nombre,
                'price' => (float) $p->precio,
                'category' => $p->categoria->nombre,
                'desc' => $p->descripcion ?? '',
                'image' => $p->imagenUrl() ?? '',
                'stock' => $p->stock,
            ])->all(),
            'promos' => Promocion::vigentes()->with('productos:id')->get()->map(fn ($x) => [
                'id' => 'pr'.$x->id,
                'title' => $x->titulo,
                'desc' => $x->descripcion ?? '',
                'percent' => $x->porcentaje,
                'all' => $x->aplica_todos,
                'productIds' => $x->productos->map(fn ($p) => (string) $p->id)->all(),
                'until' => $x->vence?->toDateString() ?? '',
                'image' => $x->imagenUrl() ?? '',
                'active' => true,
            ])->all(),
        ];

        return $data;
    }

    // Registra el pedido en la base de datos (queda «pendiente» hasta que el encargado lo confirme)
    public function pedido(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'nullable|max:150',
            'items' => 'required|array|min:1|max:50',
            'items.*.id' => 'required|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1|max:999',
        ]);

        $venta = DB::transaction(function () use ($validated) {
            $cliente = filled($validated['nombre'] ?? null)
                ? Cliente::firstOrCreate(['nombre' => trim($validated['nombre'])])
                : null;

            $venta = Venta::create(['cliente_id' => $cliente?->id, 'fecha' => now(), 'estado' => 'pendiente']);

            foreach ($validated['items'] as $item) {
                $producto = Producto::find($item['id']);
                $cantidad = min((int) $item['cantidad'], $producto->stock);
                if ($cantidad < 1) {
                    continue; // agotado: no se registra
                }

                $detalle = new DetalleVenta([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $producto->precioFinal(), // ya con la promoción vigente
                ]);
                $detalle->subtotal = $detalle->calcularSubtotal();
                $venta->detalles()->save($detalle);
            }

            $venta->calcularTotal();
            $venta->save();

            return $venta;
        });

        if ($venta->detalles()->count() === 0) {
            $venta->delete();

            return response()->json(['ok' => false, 'message' => 'Sin productos disponibles.'], 422);
        }

        return response()->json(['ok' => true, 'pedido' => $venta->id]);
    }

    // Pedido PAGADO: el cliente sube la foto de su comprobante y escribe el código de operación.
    // Recién aquí se descuenta el stock del catálogo y se genera el código único de recojo.
    public function pagar(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|max:150',
            'telefono' => 'nullable|digits_between:6,15',
            'metodo_pago_id' => 'nullable|exists:metodos_pago,id',
            'codigo_operacion' => ['required', 'min:4', 'max:40', 'regex:/^[A-Za-z0-9\-\s]+$/'],
            'comprobante' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'items' => 'required|array|min:1|max:50',
            'items.*.id' => 'required|distinct|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1|max:999',
        ], [
            'nombre.required' => 'Escribe tu nombre para identificar tu pedido en la tienda.',
            'codigo_operacion.required' => 'Escribe el código de operación que aparece en tu comprobante.',
            'codigo_operacion.regex' => 'El código de operación solo lleva letras, números y guiones.',
            'comprobante.required' => 'Sube la foto o captura de tu comprobante de pago.',
            'comprobante.image' => 'El comprobante debe ser una imagen.',
            'comprobante.mimes' => 'El comprobante debe ser JPG, PNG o WEBP.',
            'comprobante.max' => 'La imagen es muy pesada (máximo 5 MB).',
        ]);

        $operacion = strtoupper(preg_replace('/\s+/', '', $validated['codigo_operacion']));

        // Un mismo código de operación no sirve para dos pedidos (evita reutilizar un comprobante)
        if (Venta::where('codigo_operacion', $operacion)->exists()) {
            throw ValidationException::withMessages([
                'codigo_operacion' => 'Ese código de operación ya fue usado en otro pedido. Revisa que sea el de tu pago.',
            ]);
        }

        // La misma imagen (byte a byte) tampoco puede usarse dos veces
        $huella = hash_file('sha256', $request->file('comprobante')->getRealPath());
        if (Venta::where('comprobante_hash', $huella)->exists()) {
            throw ValidationException::withMessages([
                'comprobante' => 'Esta foto de comprobante ya fue usada en otro pedido. Sube la constancia de tu propio pago.',
            ]);
        }

        $metodo = isset($validated['metodo_pago_id']) ? MetodoPago::find($validated['metodo_pago_id']) : null;
        $ruta = $request->file('comprobante')->store('comprobantes', 'local'); // privado: solo lo ve el encargado

        try {
            $venta = DB::transaction(function () use ($validated, $operacion, $metodo, $ruta, $huella) {
                $cliente = Cliente::firstOrCreate(
                    ['nombre' => trim($validated['nombre'])],
                    ['telefono' => $validated['telefono'] ?? null],
                );

                $venta = Venta::create([
                    'codigo' => Venta::generarCodigo(),
                    'cliente_id' => $cliente->id,
                    'fecha' => now(),
                    'estado' => 'confirmado', // pagado: el stock se descuenta ahora
                    'metodo_pago' => $metodo?->nombre,
                    'codigo_operacion' => $operacion,
                    'comprobante' => $ruta,
                    'comprobante_hash' => $huella,
                    'comprobante_at' => now(),
                ]);

                $promos = Promocion::vigentes()->with('productos:id')->get();

                // Se bloquean las filas en orden de id: dos clientes comprando a la vez no se pisan ni se traban
                $items = collect($validated['items'])->sortBy('id');
                foreach ($items as $item) {
                    $producto = Producto::lockForUpdate()->find($item['id']);

                    if ($producto->stock < $item['cantidad']) {
                        // Rollback total: no se cobra stock que no existe
                        throw ValidationException::withMessages([
                            'items' => $producto->stock > 0
                                ? "Solo quedan {$producto->stock} unidad(es) de «{$producto->nombre}». Ajusta tu carrito."
                                : "«{$producto->nombre}» se agotó mientras comprabas. Quítalo del carrito.",
                        ]);
                    }

                    $precio = $producto->precioFinal($promos);
                    $venta->detalles()->create([
                        'producto_id' => $producto->id,
                        'cantidad' => (int) $item['cantidad'],
                        'precio_unitario' => $precio,
                        'subtotal' => round($precio * $item['cantidad'], 2),
                    ]);
                    $producto->actualizarStock((int) $item['cantidad']); // descuenta del catálogo
                }

                $venta->calcularTotal();
                $venta->save();

                return $venta;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($ruta); // no dejar imágenes huérfanas si algo falló
            throw $e;
        }

        return response()->json([
            'ok' => true,
            'codigo' => $venta->codigo,
            'total' => (float) $venta->total,
            'ticketUrl' => route('tienda.ticket', $venta->codigo),
            'verificarUrl' => route('verificar.show', $venta->codigo),
            // stock actualizado para que el catálogo se refresque sin recargar la página
            'stocks' => Producto::whereIn('id', $venta->detalles()->pluck('producto_id'))->pluck('stock', 'id'),
        ]);
    }

    // Pase de recojo del cliente: muestra el código y el QR que escaneará el encargado
    public function ticket(string $codigo): View
    {
        $venta = Venta::with('cliente', 'detalles.producto')
            ->where('codigo', Venta::normalizarCodigo($codigo))->firstOrFail();

        return view('tienda.ticket', [
            'venta' => $venta,
            'a' => Ajuste::actual(),
            'verificarUrl' => route('verificar.show', $venta->codigo),
        ]);
    }
}
