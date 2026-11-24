<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Venta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

// El encargado valida en la tienda el código (o QR) que muestra el cliente y entrega los productos.
class VerificacionController extends Controller
{
    // Pantalla de verificación: caja de código + lector de QR con la cámara
    public function index(Request $request): View|RedirectResponse
    {
        if (filled($request->query('codigo'))) {
            return to_route('verificar.show', Venta::normalizarCodigo($request->query('codigo')));
        }

        $recientes = Venta::with('cliente')->whereNotNull('entregado_at')->latest('entregado_at')->take(8)->get();

        return view('gestion.verificar.index', [
            'venta' => null, 'codigo' => null, 'recientes' => $recientes, 'ajuste' => Ajuste::actual(),
        ]);
    }

    // Resultado: válido / ya entregado / sin pago / cancelado / no existe
    public function show(string $codigo): View
    {
        $codigo = Venta::normalizarCodigo($codigo);
        $venta = Venta::with('cliente', 'entregadoPor', 'detalles.producto')->where('codigo', $codigo)->first();
        $recientes = Venta::with('cliente')->whereNotNull('entregado_at')->latest('entregado_at')->take(8)->get();

        return view('gestion.verificar.index', ['venta' => $venta, 'codigo' => $codigo, 'recientes' => $recientes, 'ajuste' => Ajuste::actual()]);
    }

    // Marca como entregado. Se bloquea la fila: si dos encargados escanean a la vez, solo uno entrega.
    public function entregar(Request $request, string $codigo): RedirectResponse
    {
        $codigo = Venta::normalizarCodigo($codigo);

        $resultado = DB::transaction(function () use ($codigo, $request) {
            $venta = Venta::lockForUpdate()->where('codigo', $codigo)->first();

            if (! $venta || ! $venta->vigenteParaEntrega()) {
                return false;
            }

            $venta->update(['entregado_at' => now(), 'entregado_por' => $request->user()->id]);

            return $venta;
        });

        if (! $resultado) {
            return to_route('verificar.show', $codigo)->with('error', 'Este código ya no es válido para entregar.');
        }

        return to_route('verificar.show', $codigo)->with('ok', "Pedido {$codigo} entregado correctamente.");
    }

    // Imagen del comprobante (guardada en privado: solo se ve con sesión iniciada)
    public function comprobante(Venta $venta): BinaryFileResponse
    {
        abort_unless($venta->comprobante && Storage::disk('local')->exists($venta->comprobante), 404);

        return response()->file(Storage::disk('local')->path($venta->comprobante));
    }
}
