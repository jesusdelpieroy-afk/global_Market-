<?php

use App\Http\Controllers\AjusteController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PortadaController;
use App\Http\Controllers\PreguntaFrecuenteController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\PromocionController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\VerificacionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tienda pública (clientes)
|--------------------------------------------------------------------------
*/
Route::get('/', [TiendaController::class, 'index'])->name('tienda.index');
// Datos en vivo del catálogo (la tienda los consulta sola cada pocos segundos)
Route::get('/catalogo.json', [TiendaController::class, 'catalogo'])->middleware('throttle:60,1')->name('tienda.catalogo');
Route::post('/pedidos', [TiendaController::class, 'pedido'])->middleware('throttle:20,1')->name('tienda.pedido');
// Pedido pagado: comprobante + código de operación → descuenta stock y genera el código de recojo
Route::post('/pedidos/pago', [TiendaController::class, 'pagar'])->middleware('throttle:10,1')->name('tienda.pago');
// Pase de recojo del cliente (código + QR)
Route::get('/pedido/{codigo}', [TiendaController::class, 'ticket'])->where('codigo', '[A-Za-z0-9\-]+')->middleware('throttle:60,1')->name('tienda.ticket');

/*
|--------------------------------------------------------------------------
| Acceso del encargado
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/admin/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');

/*
|--------------------------------------------------------------------------
| Panel del encargado (requiere sesión iniciada)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Vista raíz del panel: el dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    // CRUD (Route::resource crea 6 rutas por módulo: index, create, store, edit, update, destroy).
    // ->parameters(): los nombres en español terminados en consonante se pluralizan mal en inglés
    // (reto del Paso 14 de la guía), así que se indica el nombre del parámetro.
    Route::resource('categorias', CategoriaController::class)->except('show');
    Route::resource('productos', ProductoController::class)->except('show');
    Route::resource('promociones', PromocionController::class)->except('show')->parameters(['promociones' => 'promocion']);
    Route::resource('clientes', ClienteController::class)->except('show');
    Route::resource('roles', RolController::class)->except('show')->parameters(['roles' => 'rol']);
    Route::resource('usuarios', UsuarioController::class)->except('show')->parameters(['usuarios' => 'usuario']);
    Route::resource('preguntas', PreguntaFrecuenteController::class)->except('show')->parameters(['preguntas' => 'pregunta']);

    // Pedidos y ventas: nacen en la tienda o se registran aquí (create/store); no se editan (no hay edit): se confirman y, si hubo error, se anulan (destroy)
    Route::resource('ventas', VentaController::class)->only(['index', 'create', 'store', 'show', 'update', 'destroy']);

    // Verificación en tienda: el encargado escribe o escanea el código del cliente y entrega los productos
    Route::get('/verificar', [VerificacionController::class, 'index'])->name('verificar.index');
    Route::get('/verificar/{codigo}', [VerificacionController::class, 'show'])->where('codigo', '[A-Za-z0-9\-]+')->name('verificar.show');
    Route::post('/verificar/{codigo}/entregar', [VerificacionController::class, 'entregar'])->where('codigo', '[A-Za-z0-9\-]+')->name('verificar.entregar');
    Route::get('/ventas/{venta}/comprobante', [VerificacionController::class, 'comprobante'])->name('ventas.comprobante');

    // Configuraciones
    Route::resource('pagos', MetodoPagoController::class)->except('show')->parameters(['pagos' => 'pago']);
    Route::get('/portada', [PortadaController::class, 'edit'])->name('portada.edit');
    Route::put('/portada', [PortadaController::class, 'update'])->name('portada.update');
    Route::get('/ajustes', [AjusteController::class, 'edit'])->name('ajustes.edit');
    Route::put('/ajustes', [AjusteController::class, 'update'])->name('ajustes.update');
    Route::put('/ajustes/cuenta', [AjusteController::class, 'cuenta'])->name('ajustes.cuenta');
});
