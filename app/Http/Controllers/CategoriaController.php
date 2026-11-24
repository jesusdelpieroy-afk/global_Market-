<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoriaController extends Controller
{
    // READ: listado
    public function index(): View
    {
        $ajuste = \App\Models\Ajuste::actual();
        $categorias = Categoria::withCount('productos')->orderBy('nombre')->get();

        return view('gestion.categorias.index', compact('categorias', 'ajuste'));
    }

    // Muestra el formulario vacío
    public function create(): View
    {
        return view('gestion.categorias.create', []);
    }

    // CREATE: valida, guarda y redirige (patrón PRG)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|max:80|unique:categorias',
            'descripcion' => 'nullable|max:255',
        ]);

        Categoria::create($validated);

        return to_route('categorias.index')->with('ok', 'Categoría registrada correctamente.');
    }

    // Muestra el formulario con los datos actuales (route model binding)
    public function edit(Categoria $categoria): View
    {
        return view('gestion.categorias.edit', array_merge(compact('categoria'), []));
    }

    // UPDATE: valida, actualiza y redirige
    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'max:80', Rule::unique('categorias', 'nombre')->ignore($categoria->id)],
            'descripcion' => 'nullable|max:255',
        ]);

        $categoria->update($validated);

        return to_route('categorias.index')->with('ok', 'Categoría actualizada correctamente.');
    }

    // DELETE: elimina respetando las reglas del diagrama
    public function destroy(Categoria $categoria): RedirectResponse
    {
        // Regla del diagrama (restrictOnDelete): no se borra lo que otros registros necesitan
        if ($categoria->productos()->exists()) {
            return to_route('categorias.index')->with('error', 'No se puede eliminar: tiene productos asociados.');
        }

        $categoria->delete();

        return to_route('categorias.index')->with('ok', 'Categoría eliminada correctamente.');
    }
}
