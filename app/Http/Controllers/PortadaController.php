<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Support\Imagen;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PortadaController extends Controller
{
    public function edit(): View
    {
        return view('configuraciones.portada.edit', ['ajuste' => Ajuste::actual()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'portada_titulo' => 'required|max:80',
            'portada_texto' => 'nullable|max:260',
            'imagen1' => 'nullable|image|max:4096',
            'imagen2' => 'nullable|image|max:4096',
        ]);

        $ajuste = Ajuste::actual();
        $datos = ['portada_titulo' => $validated['portada_titulo'], 'portada_texto' => $validated['portada_texto'] ?? null];

        foreach ([1, 2] as $n) {
            $campo = "portada_imagen{$n}";
            if ($request->hasFile("imagen{$n}")) {
                Imagen::borrar($ajuste->$campo);
                $datos[$campo] = Imagen::guardar($request->file("imagen{$n}"), 'portada');
            } elseif ($request->boolean("original{$n}")) { // volver a la foto original del local
                Imagen::borrar($ajuste->$campo);
                $datos[$campo] = null;
            }
        }

        $ajuste->update($datos);

        return to_route('portada.edit')->with('ok', 'Portada guardada correctamente.');
    }
}
