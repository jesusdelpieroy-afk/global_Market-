<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Promocion;
use App\Support\Imagen;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PromocionController extends Controller
{
    // READ: listado
    public function index(): View
    {
        $ajuste = \App\Models\Ajuste::actual();
        $promociones = Promocion::withCount('productos')->latest()->get();

        return view('gestion.promociones.index', compact('promociones', 'ajuste'));
    }

    // Muestra el formulario vacío
    public function create(): View
    {
        return view('gestion.promociones.create', ['productos' => Producto::orderBy('nombre')->get()]);
    }

    // CREATE: valida, guarda y redirige (patrón PRG)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|max:60',
            'descripcion' => 'nullable|max:160',
            'porcentaje' => 'required|integer|min:1|max:90',
            'aplica_todos' => 'required|boolean',
            'vence' => 'nullable|date',
            'activa' => 'required|boolean',
            'productos' => 'nullable|array',
            'productos.*' => 'exists:productos,id',
            'imagen' => 'nullable|image|max:4096',
        ]);
        $validated = $this->subirFoto($request, $validated, 'promociones');
        $ids = $validated['aplica_todos'] ? [] : ($validated['productos'] ?? []);
        unset($validated['productos']);

        $registro = Promocion::create($validated);
        $registro->productos()->sync($ids);

        return to_route('promociones.index')->with('ok', 'Promoción registrada correctamente.');
    }

    // Muestra el formulario con los datos actuales (route model binding)
    public function edit(Promocion $promocion): View
    {
        return view('gestion.promociones.edit', array_merge(compact('promocion'), ['productos' => Producto::orderBy('nombre')->get()]));
    }

    // UPDATE: valida, actualiza y redirige
    public function update(Request $request, Promocion $promocion): RedirectResponse
    {
        $validated = $request->validate([
            'titulo' => 'required|max:60',
            'descripcion' => 'nullable|max:160',
            'porcentaje' => 'required|integer|min:1|max:90',
            'aplica_todos' => 'required|boolean',
            'vence' => 'nullable|date',
            'activa' => 'required|boolean',
            'productos' => 'nullable|array',
            'productos.*' => 'exists:productos,id',
            'imagen' => 'nullable|image|max:4096',
        ]);
        $validated = $this->subirFoto($request, $validated, 'promociones', $promocion->imagen);
        $ids = $validated['aplica_todos'] ? [] : ($validated['productos'] ?? []);
        unset($validated['productos']);

        $promocion->update($validated);
        $registro = $promocion;
        $registro->productos()->sync($ids);

        return to_route('promociones.index')->with('ok', 'Promoción actualizada correctamente.');
    }

    // DELETE: elimina respetando las reglas del diagrama
    public function destroy(Promocion $promocion): RedirectResponse
    {
        Imagen::borrar($promocion->imagen);
        $promocion->delete();

        return to_route('promociones.index')->with('ok', 'Promoción eliminada correctamente.');
    }

    // Guarda la foto subida (y borra la anterior al editar); si no hay foto nueva, no toca la actual
    private function subirFoto(Request $request, array $validated, string $carpeta, ?string $anterior = null): array
    {
        unset($validated['imagen']);

        if ($request->hasFile('imagen')) {
            Imagen::borrar($anterior);
            $validated['imagen'] = Imagen::guardar($request->file('imagen'), $carpeta);
        }

        return $validated;
    }
}
