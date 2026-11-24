<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AjusteController extends Controller
{
    public function edit(): View
    {
        return view('configuraciones.ajustes.edit', ['ajuste' => Ajuste::actual()]);
    }

    // Datos de la tienda
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|max:80',
            'descripcion_corta' => 'nullable|max:60',
            'whatsapp' => 'required|digits_between:8,15',
            'moneda' => 'required|max:4',
            'direccion' => 'nullable|max:120',
            'horario' => 'nullable|max:100',
        ]);

        Ajuste::actual()->update($validated);

        return to_route('ajustes.edit')->with('ok', 'Datos de la tienda guardados correctamente.');
    }

    // Cuenta de acceso del usuario que ha ingresado
    public function cuenta(Request $request): RedirectResponse
    {
        $usuario = $request->user();

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($usuario->id)],
            'password' => 'nullable|min:6|confirmed',
        ]);

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $usuario->update($validated); // la contraseña se cifra sola (cast "hashed")

        return to_route('ajustes.edit')->with('ok', 'Cuenta de acceso guardada correctamente.');
    }
}
