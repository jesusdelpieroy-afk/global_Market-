<?php

namespace App\Http\Controllers;

use App\Models\PreguntaFrecuente;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PreguntaFrecuenteController extends Controller
{
    // READ: listado
    public function index(): View
    {
        $ajuste = \App\Models\Ajuste::actual();
        $preguntas = PreguntaFrecuente::orderBy('orden')->get();

        return view('configuraciones.preguntas.index', compact('preguntas', 'ajuste'));
    }

    // Muestra el formulario vacío
    public function create(): View
    {
        return view('configuraciones.preguntas.create', []);
    }

    // CREATE: valida, guarda y redirige (patrón PRG)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'pregunta' => 'required|max:200',
            'respuesta' => 'required|max:500',
            'orden' => 'nullable|integer|min:0',
        ]);
        $validated['orden'] = $validated['orden'] ?? 0;

        PreguntaFrecuente::create($validated);

        return to_route('preguntas.index')->with('ok', 'Pregunta registrada correctamente.');
    }

    // Muestra el formulario con los datos actuales (route model binding)
    public function edit(PreguntaFrecuente $pregunta): View
    {
        return view('configuraciones.preguntas.edit', array_merge(compact('pregunta'), []));
    }

    // UPDATE: valida, actualiza y redirige
    public function update(Request $request, PreguntaFrecuente $pregunta): RedirectResponse
    {
        $validated = $request->validate([
            'pregunta' => 'required|max:200',
            'respuesta' => 'required|max:500',
            'orden' => 'nullable|integer|min:0',
        ]);
        $validated['orden'] = $validated['orden'] ?? 0;

        $pregunta->update($validated);

        return to_route('preguntas.index')->with('ok', 'Pregunta actualizada correctamente.');
    }

    // DELETE: elimina respetando las reglas del diagrama
    public function destroy(PreguntaFrecuente $pregunta): RedirectResponse
    {
        $pregunta->delete();

        return to_route('preguntas.index')->with('ok', 'Pregunta eliminada correctamente.');
    }
}
