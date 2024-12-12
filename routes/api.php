<?php

use App\Http\Controllers\Articulos\ArticuloController;
use App\Http\Controllers\Articulos\ArticuloWalletController;
use App\Http\Controllers\Articulos\BodegaArticuloController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Clientes\ClienteController;
use App\Http\Controllers\Configuracion\BodegaController;
use App\Http\Controllers\Configuracion\CategoriaController;
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\IvaController;
use App\Http\Controllers\Configuracion\MetodoPagoController;
use App\Http\Controllers\Configuracion\ProveedorController;
use App\Http\Controllers\Configuracion\SedeController;
use App\Http\Controllers\Configuracion\SedeDeliverieController;
use App\Http\Controllers\Configuracion\SegmentoClienteController;
use App\Http\Controllers\Configuracion\UnidadController;
use App\Http\Controllers\Facturas\FacturaController;
use App\Http\Controllers\Generales\GeneralesController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Usuarios\UsuarioController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([

    // 'middleware' => 'auth:api',
    'prefix' => 'auth',
    // 'middleware' => ['auth:api', 'role:Super-Admin'],

], function ($router) {
    Route::post('/register', [AuthController::class, 'register'])->name('register');
    Route::post('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/me', [AuthController::class, 'me'])->name('me');
});

Route::group([

    'middleware' => 'auth:api',
], function ($router) {
    Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
});

Route::group([
    'prefix' => 'generales',
    'middleware' => 'auth:api',

], function ($router) {
    Route::post('/departamentos', [GeneralesController::class, 'departamentos']);
    Route::post('/municipios', [GeneralesController::class, 'municipios']);
    Route::post('/tipo-documentos', [GeneralesController::class, 'tipodocs']);
    Route::post('/generos', [GeneralesController::class, 'generos']);
    Route::post('/roles', [GeneralesController::class, 'roles']);
    Route::post('/empresas', [GeneralesController::class, 'empresas']);
    Route::post('/configuraciones', [GeneralesController::class, 'configuraciones']);
    Route::post('/configuraciones/articulos', [GeneralesController::class, 'articulos']);
});

Route::group([
    'prefix' => 'configuracion',
    'middleware' => 'auth:api',
], function ($router) {
    Route::resource("roles", RolePermissionController::class);
    Route::post('/users/{id}', [UsuarioController::class, 'update']);
    Route::patch('/users/{id}/cambiar-estado', [UsuarioController::class, 'cambiarEstadoUser']);
    Route::resource("users", UsuarioController::class);
    Route::patch('/sedes/{id}/cambiar-estado', [SedeController::class, 'cambiarEstado']);
    Route::resource("sedes", SedeController::class);
    Route::patch('/empresas/{id}/cambiar-estado', [EmpresaController::class, 'cambiarEstado']);
    Route::resource("empresas", EmpresaController::class);
    Route::patch('/bodegas/{id}/cambiar-estado', [BodegaController::class, 'cambiarEstado']);
    Route::resource("bodegas", BodegaController::class);
    Route::patch('/sede-deliveries/{id}/cambiar-estado', [SedeDeliverieController::class, 'cambiarEstado']);
    Route::resource("sede-deliveries", SedeDeliverieController::class);
    Route::patch('/metodo-pagos/{id}/cambiar-estado', [MetodoPagoController::class, 'cambiarEstado']);
    Route::resource("metodo-pagos", MetodoPagoController::class);
    Route::patch('/segmento_cliente/{id}/cambiar-estado', [SegmentoClienteController::class, 'cambiarEstado']);
    Route::resource("segmento_cliente", SegmentoClienteController::class);
    Route::post('/categorias/{id}', [CategoriaController::class, 'update']);
    Route::patch('/categorias/{id}/cambiar-estado', [CategoriaController::class, 'cambiarEstado']);
    Route::resource("categorias", CategoriaController::class);
    Route::post('/proveedores/{id}', [ProveedorController::class, 'update']);
    Route::patch('/proveedores/{id}/cambiar-estado', [ProveedorController::class, 'cambiarEstado']);
    Route::resource("proveedores", ProveedorController::class);
    Route::patch('/unidades/{id}/cambiar-estado', [UnidadController::class, 'cambiarEstado']);
    Route::resource("unidades", UnidadController::class);

    Route::post('/unidades/add-transformacion', [UnidadController::class, 'add_transformacion']);
    Route::delete('/unidades/delete-transformacion/{i}', [UnidadController::class, 'delete_transformacion']);

    Route::patch('/iva/{id}/cambiar-estado', [IvaController::class, 'cambiarEstado']);
    Route::resource("iva", IvaController::class);
});

Route::group([
    'middleware' => 'auth:api',
], function ($router) {
    Route::post('/articulos/index', [ArticuloController::class, 'index']);
    Route::post('/articulos/{id}', [ArticuloController::class, 'update']);
    Route::patch('/articulos/{id}/cambiar-estado', [ArticuloController::class, 'cambiarEstado']);
    Route::post('/articulos/import/excel', [ArticuloController::class, 'import_articulo']);
    Route::get('/articulos/generar-sku/{categoria_id}', [ArticuloController::class, 'generarSku']);

    Route::get('/articulos/buscar-articulos', [ArticuloController::class, 'buscarArticulos']);
    Route::resource("articulos", ArticuloController::class);
    Route::resource("articulos-wallets", ArticuloWalletController::class);
    Route::resource("bodegas-articulos", BodegaArticuloController::class);

});

Route::get('/excel/export-articulo', [ArticuloController::class, 'export_articulo']);

Route::group([
    'middleware' => 'auth:api',
], function ($router) {

    Route::post('/clientes/index', [ClienteController::class, 'index']);
    Route::patch('/clientes/{id}/cambiar-estado', [ClienteController::class, 'cambiarEstado']);
    Route::resource("clientes", ClienteController::class)->except(['show']);

    Route::post('/clientes/import/excel', [ClienteController::class, 'import_clientes']);

    Route::get('/clientes/buscar-clientes', [ClienteController::class, 'buscarClientes']);
});
Route::get('/excel/export-clientes', [ClienteController::class, 'export_clientes']);

Route::group([
    'middleware' => 'auth:api',
], function ($router) {

    Route::post('/facturas/index', [FacturaController::class, 'index']);
    Route::patch('/facturas/{id}/cambiar-estado', [FacturaController::class, 'cambiarEstado']);
    Route::resource("facturas", FacturaController::class);

});
