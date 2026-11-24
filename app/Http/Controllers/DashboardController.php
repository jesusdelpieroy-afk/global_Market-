<?php

namespace App\Http\Controllers;

use App\Models\Ajuste;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Promocion;
use App\Models\Venta;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    // Página principal del panel (ruta /admin con nombre dashboard.index)
    public function index(): View
    {
        $ajuste = Ajuste::actual();
        $total = Producto::count();
        $agotados = Producto::where('stock', '<=', 0)->count();

        $stats = [
            'total' => $total,
            'conStock' => $total - $agotados,
            'agotados' => $agotados,
            'promos' => Promocion::vigentes()->count(),
            'categorias' => Categoria::count(),
            'valor' => (float) Producto::selectRaw('COALESCE(SUM(precio * stock), 0) as v')->value('v'),
            'pendientes' => Venta::where('estado', 'pendiente')->count(),
        ];

        $recientes = Producto::with('categoria')->latest()->take(5)->get();
        $porRevisar = Producto::where('stock', '<=', Producto::STOCK_BAJO)->orderBy('stock')->take(6)->get();
        $pedidos = Venta::with('cliente')->latest()->take(5)->get();

        return view('principal.dashboard.index', compact('ajuste', 'stats', 'recientes', 'porRevisar', 'pedidos'));
    }
}
