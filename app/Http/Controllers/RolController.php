<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RolController extends Controller
{
    // READ: listado
    public function index(): View
    {
        $ajuste = \App\Models\Ajuste::actual();
        $roles = Rol::withCount('usuarios')->orderBy('nombre')->get();

        return view('configuraciones.roles.index', compact('roles', 'ajuste'));
    }

    // Muestra el formulario vacío
    public function create(): View
    {
        return view('configuraciones.roles.create', []);
    }

    // CREATE: valida, guarda y redirige (patrón PRG)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|max:50|unique:roles',
            'descripcion' => 'nullable|max:255',
        ]);

        Rol::create($validated);

        return to_route('roles.index')->with('ok', 'Rol registrado correctamente.');
    }

    // Muestra el formulario con los datos actuales (route model binding)
    public function edit(Rol $rol): View
    {
        return view('configuraciones.roles.edit', array_merge(compact('rol'), []));
    }

    // UPDATE: valida, actualiza y redirige
    public function update(Request $request, Rol $rol): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'max:50', Rule::unique('roles', 'nombre')->ignore($rol->id)],
            'descripcion' => 'nullable|max:255',
        ]);

        $rol->update($validated);

        return to_route('roles.index')->with('ok', 'Rol actualizado correctamente.');
    }

    // DELETE: elimina respetando las reglas del diagrama
    public function destroy(Rol $rol): RedirectResponse
    {
        // Regla del diagrama (restrictOnDelete): no se borra lo que otros registros necesitan
        if ($rol->usuarios()->exists()) {
            return to_route('roles.index')->with('error', 'No se puede eliminar: hay usuarios con este rol.');
        }

        $rol->delete();

        return to_route('roles.index')->with('ok', 'Rol eliminado correctamente.');
    }
}
