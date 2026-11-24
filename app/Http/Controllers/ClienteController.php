<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    // READ: listado
    public function index(): View
    {
        $ajuste = \App\Models\Ajuste::actual();
        $clientes = Cliente::withCount('ventas')->orderBy('nombre')->get();

        return view('gestion.clientes.index', compact('clientes', 'ajuste'));
    }

    // Muestra el formulario vacío
    public function create(): View
    {
        return view('gestion.clientes.create', []);
    }

    // CREATE: valida, guarda y redirige (patrón PRG)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|max:150',
            'dni' => 'nullable|digits:8|unique:clientes',
            'telefono' => 'nullable|max:15',
        ]);

        Cliente::create($validated);

        return to_route('clientes.index')->with('ok', 'Cliente registrado correctamente.');
    }

    // Muestra el formulario con los datos actuales (route model binding)
    public function edit(Cliente $cliente): View
    {
        return view('gestion.clientes.edit', array_merge(compact('cliente'), []));
    }

    // UPDATE: valida, actualiza y redirige
    public function update(Request $request, Cliente $cliente): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => 'required|max:150',
            'dni' => ['nullable', 'digits:8', Rule::unique('clientes', 'dni')->ignore($cliente->id)],
            'telefono' => 'nullable|max:15',
        ]);

        $cliente->update($validated);

        return to_route('clientes.index')->with('ok', 'Cliente actualizado correctamente.');
    }

    // DELETE: elimina respetando las reglas del diagrama
    public function destroy(Cliente $cliente): RedirectResponse
    {
        // Regla del diagrama: un cliente con pedidos o ventas no se elimina
        if ($cliente->ventas()->exists()) {
            return to_route('clientes.index')->with('error', 'No se puede eliminar: el cliente tiene pedidos o ventas registradas.');
        }

        $cliente->delete();

        return to_route('clientes.index')->with('ok', 'Cliente eliminado correctamente.');
    }
}
