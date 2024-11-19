<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Configuracion\BodegaController;
use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\SedeController;
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
    // Route::resource("roles", RolePermissionController::class);
    // Route::post('/users/{id}', [UsuarioController::class, 'update']);
    // Route::resource("users", UsuarioController::class);
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
});
