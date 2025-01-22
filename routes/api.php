<?php

use App\Http\Controllers\Articulos\ArticuloController;
use App\Http\Controllers\Articulos\ArticuloWalletController;
use App\Http\Controllers\Articulos\BodegaArticuloController;
use App\Http\Controllers\Articulos\ConversionController;
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
use App\Http\Controllers\Kardex\KardexController;
use App\Http\Controllers\Kpi\KpiController;
use App\Http\Controllers\Movimientos\MovimientoController;
use App\Http\Controllers\Movimientos\PlantillaController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\Solicitudes\SolicitudController;
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
    Route::post('/verificar', [AuthController::class, 'verificar'])->name('verificar');
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
    Route::post('/configuraciones/conversiones', [GeneralesController::class, 'config']);
});

Route::group([
    'prefix' => 'configuracion',
    'middleware' => 'auth:api',
], function ($router) {
    Route::resource("roles", RolePermissionController::class);
    Route::post('/users/{id}', [UsuarioController::class, 'update']);
    Route::post('/users/edit-perfil/{id}', [UsuarioController::class, 'edit_perfil']);
    Route::post('/users/edit-email/{id}', [UsuarioController::class, 'edit_email']);
    Route::post('/users/edit-password/{id}', [UsuarioController::class, 'edit_password']);
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

    Route::post('/conversiones/index', [ConversionController::class, 'index']);
    Route::patch('/conversiones/{id}/cambiar-estado', [ConversionController::class, 'cambiarEstado']);
    Route::resource("conversiones", ConversionController::class);

    Route::post('/kardex/index', [KardexController::class, 'index']);
    Route::resource("kardex", KardexController::class);

    Route::group(["prefix" => "kpi"], function ($router) {
        Route::post('/informacion-general', [KpiController::class, 'informacion_general']);
        Route::post('/venta-x-sede', [KpiController::class, 'venta_x_sede']);
        Route::post('/venta-x-dia-del-mes', [KpiController::class, 'venta_x_dia_del_mes']);
        Route::post('/venta-x-mes-del-year', [KpiController::class, 'venta_x_mes_del_year']);
        Route::post('/venta-x-segmento', [KpiController::class, 'venta_x_segmento']);
        Route::post('/vendedor-mas-venta', [KpiController::class, 'vendedor_mas_venta']);
        Route::post('/categorias-mas-ventas', [KpiController::class, 'categorias_mas_ventas']);
        Route::post('/fecha-actual', [KpiController::class, 'fecha_actual']);
    });
});

Route::get('/excel/export-articulo', [ArticuloController::class, 'export_articulo']);
Route::get('/excel/export-kardex', [KardexController::class, 'export_kardex']);

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

Route::get('/facturas/imprimir-factura', [FacturaController::class, 'imprimir']);
Route::get('/excel/export-factura', [FacturaController::class, 'export_factura']);
Route::get('/excel/export-detalle-factura', [FacturaController::class, 'export_detalle_factura']);

Route::group([
    'middleware' => 'auth:api',
], function ($router) {
    Route::post('/facturas/editar/{id}', [FacturaController::class, 'update']);
    Route::post('/facturas/eliminar-detalle', [FacturaController::class, 'eliminarDetalle']);
    Route::post('/facturas/index', [FacturaController::class, 'index']);
    Route::patch('/facturas/{id}/cambiar-estado', [FacturaController::class, 'cambiarEstado']);
    Route::resource("facturas", FacturaController::class);
});

Route::group([
    'middleware' => 'auth:api',
], function ($router) {

    Route::post('/solicitudes/entrega', [SolicitudController::class, 'entrega']);
    Route::post('/solicitudes/index', [SolicitudController::class, 'index']);
    Route::patch('/solicitudes/{id}/cambiar-estado', [SolicitudController::class, 'cambiarEstado']);
    Route::resource("solicitudes", SolicitudController::class);

    Route::post('/movimientos/entrada', [MovimientoController::class, 'entrada']);
    Route::post('/movimientos/index', [MovimientoController::class, 'index']);
    Route::patch('/movimientos/{id}/cambiar-estado', [MovimientoController::class, 'cambiarEstado']);
    Route::resource("movimientos", MovimientoController::class);

    Route::post('/plantillas/index', [PlantillaController::class, 'index']);
    Route::patch('/plantillas/{id}/cambiar-estado', [PlantillaController::class, 'cambiarEstado']);
    Route::resource("plantillas", PlantillaController::class);
});
