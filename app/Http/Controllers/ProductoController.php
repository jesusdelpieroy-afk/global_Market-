<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Support\Imagen;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    // READ: listado con búsqueda por nombre, filtro por categoría y «stock bajo» (se combinan solos)
    public function index(Request $request): View
    {
        $ajuste = \App\Models\Ajuste::actual();

        $productos = Producto::with('categoria')          // 1 sola consulta extra para las categorías (evita N+1)
            ->when($request->search, function ($query, $search) {          // solo si escribió algo
                $query->where('nombre', 'like', "%{$search}%");
            })
            ->when($request->categoria_id, function ($query, $categoriaId) { // solo si eligió categoría
                $query->where('categoria_id', $categoriaId);
            })
            ->when($request->boolean('stock_bajo'), function ($query) {      // solo si marcó «Stock bajo»
                $query->where('stock', '<=', Producto::STOCK_BAJO);
            })
            ->orderBy('nombre')
            ->get();

        // Para llenar el <select> del filtro
        $categorias = Categoria::orderBy('nombre')->get();

        return view('gestion.productos.index', compact('productos', 'categorias', 'ajuste'));
    }

    // Muestra el formulario vacío
    public function create(): View
    {
        return view('gestion.productos.create', ['categorias' => Categoria::orderBy('nombre')->get()]);
    }

    // CREATE: valida, guarda y redirige (patrón PRG)
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|max:120',
            'descripcion' => 'nullable|max:200',
            'precio' => 'required|numeric|min:0|max:99999999',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $validated = $this->subirFoto($request, $validated, 'productos');

        Producto::create($validated);

        return to_route('productos.index')->with('ok', 'Producto registrado correctamente.');
    }

    // Muestra el formulario con los datos actuales (route model binding)
    public function edit(Producto $producto): View
    {
        return view('gestion.productos.edit', array_merge(compact('producto'), ['categorias' => Categoria::orderBy('nombre')->get()]));
    }

    // UPDATE: valida, actualiza y redirige
    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'categoria_id' => 'required|exists:categorias,id',
            'nombre' => 'required|max:120',
            'descripcion' => 'nullable|max:200',
            'precio' => 'required|numeric|min:0|max:99999999',
            'stock' => 'required|integer|min:0',
            'imagen' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        $validated = $this->subirFoto($request, $validated, 'productos', $producto->imagen);

        $producto->update($validated);

        return to_route('productos.index')->with('ok', 'Producto actualizado correctamente.');
    }

    // DELETE: elimina respetando las reglas del diagrama
    public function destroy(Producto $producto): RedirectResponse
    {
        // Regla del diagrama (restrictOnDelete): no se borra lo que otros registros necesitan
        if ($producto->detallesVenta()->exists()) {
            return to_route('productos.index')->with('error', 'No se puede eliminar: el producto ya figura en pedidos o ventas.');
        }

        Imagen::borrar($producto->imagen);
        $producto->delete();

        return to_route('productos.index')->with('ok', 'Producto eliminado correctamente.');
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
