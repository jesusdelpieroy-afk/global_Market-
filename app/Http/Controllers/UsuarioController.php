<?php

namespace App\Http\Controllers;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    // READ: listado
    public function index(): View
    {
        $ajuste = \App\Models\Ajuste::actual();
        $usuarios = User::with('rol')->orderBy('name')->get();

        return view('configuraciones.usuarios.index', compact('usuarios', 'ajuste'));
    }

    // Muestra el formulario vacío
    public function create(): View
    {
        return view('configuraciones.usuarios.create', ['roles' => Rol::orderBy('nombre')->get()]);
    }

    // CREATE: valida, guarda y redirige (patrón PRG)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'telefono' => 'nullable|max:15',
            'rol_id' => 'required|exists:roles,id',
            // confirmed: exige el campo password_confirmation con el mismo valor
            'password' => 'required|min:6|confirmed',
        ]);

        // El cast 'hashed' del modelo User encripta la contraseña al guardar
        User::create($validated);

        return to_route('usuarios.index')->with('ok', 'Usuario registrado correctamente.');
    }

    // Muestra el formulario con los datos actuales (route model binding)
    public function edit(User $usuario): View
    {
        return view('configuraciones.usuarios.edit', array_merge(compact('usuario'), ['roles' => Rol::orderBy('nombre')->get()]));
    }

    // UPDATE: valida, actualiza y redirige
    public function update(Request $request, User $usuario): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'telefono' => 'nullable|max:15',
            'rol_id' => 'required|exists:roles,id',
            'password' => 'nullable|min:6|confirmed',
        ]);
        // Al editar, si la contraseña queda vacía se conserva la actual
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return to_route('usuarios.index')->with('ok', 'Usuario actualizado correctamente.');
    }

    // DELETE: elimina respetando las reglas del diagrama
    public function destroy(User $usuario): RedirectResponse
    {
        if ($usuario->id === auth()->id()) {
            return to_route('usuarios.index')->with('error', 'No puedes eliminar tu propio usuario.');
        }

        // Regla del diagrama: un usuario que registró ventas no se elimina
        if ($usuario->ventas()->exists()) {
            return to_route('usuarios.index')->with('error', 'No se puede eliminar: el usuario tiene ventas registradas.');
        }

        $usuario->delete();

        return to_route('usuarios.index')->with('ok', 'Usuario eliminado correctamente.');
    }
}
