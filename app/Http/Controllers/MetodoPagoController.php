<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\MetodoPago;
use App\Support\Imagen;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

// Medios de pago que verá el cliente al comprar: Yape/Plin (foto del QR), cuentas BCP, etc.
class MetodoPagoController extends Controller
{
    private function reglas(): array
    {
        return [
            'nombre' => 'required|max:60',
            'titular' => 'nullable|max:100',
            'numero' => 'nullable|max:40',
            'cci' => 'nullable|max:40',
            'instrucciones' => 'nullable|max:200',
            'orden' => 'nullable|integer|min:0',
            'imagen' => 'nullable|image|max:4096',
        ];
    }

    public function index(): View
    {
        $metodos = MetodoPago::orderBy('orden')->orderBy('id')->get();

        return view('configuraciones.pagos.index', compact('metodos') + ['ajuste' => Ajuste::actual()]);
    }

    public function create(): View
    {
        return view('configuraciones.pagos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->reglas());
        $datos = collect($validated)->except('imagen')->all() + ['activo' => $request->boolean('activo'), 'orden' => $validated['orden'] ?? 0];

        if ($request->hasFile('imagen')) {
            $datos['imagen'] = Imagen::guardar($request->file('imagen'), 'pagos');
        }

        MetodoPago::create($datos);

        return to_route('pagos.index')->with('ok', 'Método de pago registrado correctamente.');
    }

    public function edit(MetodoPago $pago): View
    {
        return view('configuraciones.pagos.edit', ['pago' => $pago]);
    }

    public function update(Request $request, MetodoPago $pago): RedirectResponse
    {
        $validated = $request->validate($this->reglas());
        $datos = collect($validated)->except('imagen')->all() + ['activo' => $request->boolean('activo'), 'orden' => $validated['orden'] ?? 0];

        if ($request->hasFile('imagen')) {
            Imagen::borrar($pago->imagen);
            $datos['imagen'] = Imagen::guardar($request->file('imagen'), 'pagos');
        } elseif ($request->boolean('quitar_imagen')) {
            Imagen::borrar($pago->imagen);
            $datos['imagen'] = null;
        }

        $pago->update($datos);

        return to_route('pagos.index')->with('ok', 'Método de pago actualizado correctamente.');
    }

    public function destroy(MetodoPago $pago): RedirectResponse
    {
        Imagen::borrar($pago->imagen);
        $pago->delete();

        return to_route('pagos.index')->with('ok', 'Método de pago eliminado correctamente.');
    }
}
