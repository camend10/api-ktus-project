<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Proveedores\ProveedorRequest;
use App\Models\Configuracion\Proveedor;
use App\Services\Configuracion\ProveedorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProveedorController extends Controller
{

    protected $proveedorService;

    public function __construct(ProveedorService $proveedorService)
    {
        $this->proveedorService = $proveedorService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Proveedor::class);

        $buscar = $request->get('buscar');
        $proveedores = $this->proveedorService->getByFilter($buscar);

        if (!$proveedores) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $proveedores->total(),
            'proveedores' => $proveedores->map(function ($proveedor) {
                return [
                    'id' => $proveedor->id,
                    'tipo_identificacion' => $proveedor->tipo_identificacion,
                    'sigla' => $proveedor->tipodocumento->sigla,
                    'identificacion' => $proveedor->identificacion,
                    'dv' => $proveedor->dv,
                    'nombres' => $proveedor->nombres,
                    'apellidos' => $proveedor->apellidos,
                    'email' => $proveedor->email,
                    'direccion' => is_null($proveedor->direccion) ? '' : $proveedor->direccion,
                    'celular' => $proveedor->celular,
                    'departamento_id' => $proveedor->departamento_id,
                    'departamento' => $proveedor->departamento->nombre,
                    'municipio_id' => $proveedor->municipio_id,
                    'municipio' => $proveedor->municipio->nombre,
                    'empresa_id' => $proveedor->empresa_id,
                    'empresa' => $proveedor->empresa,
                    'imagen' => $proveedor->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $proveedor->imagen : env("APP_URL") . "storage/proveedores/blank.png",
                    'estado' => $proveedor->estado,
                    "created_format_at" => $proveedor->created_at ? $proveedor->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProveedorRequest $request)
    {
        $this->authorize('create', Proveedor::class);

        $validated = $request->validated();

        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("proveedores", $request->file("imagen"));
            $validated['imagen'] = $path;
        } else {
            $validated['imagen'] = "SIN-IMAGEN";
        }

        $proveedor = $this->proveedorService->store($validated);

        if (!$proveedor) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El proveedor se registró de manera exitosa',
            'proveedor' => [
                'id' => $proveedor->id,
                'tipo_identificacion' => $proveedor->tipo_identificacion,
                'sigla' => $proveedor->tipodocumento->sigla,
                'identificacion' => $proveedor->identificacion,
                'dv' => $proveedor->dv,
                'nombres' => $proveedor->nombres,
                'apellidos' => $proveedor->apellidos,
                'email' => $proveedor->email,
                'direccion' => is_null($proveedor->direccion) ? '' : $proveedor->direccion,
                'celular' => $proveedor->celular,
                'departamento_id' => $proveedor->departamento_id,
                'departamento' => $proveedor->departamento->nombre,
                'municipio_id' => $proveedor->municipio_id,
                'municipio' => $proveedor->municipio->nombre,
                'empresa_id' => $proveedor->empresa_id,
                'empresa' => $proveedor->empresa,
                'imagen' => $proveedor->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $proveedor->imagen : env("APP_URL") . "storage/proveedores/blank.png",
                'estado' => $proveedor->estado,
                "created_format_at" => $proveedor->created_at ? $proveedor->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProveedorRequest $request, string $id)
    {
        $this->authorize('update', Proveedor::class);

        $validated = $request->validated();

        $proveedor = $this->proveedorService->getById($request->id);

        if ($request->hasFile("imagen")) {
            if ($proveedor->imagen && $proveedor->imagen !== 'SIN-IMAGEN') {
                if (Storage::delete($proveedor->imagen)) {
                    Log::info('Imagen proveedor eliminada correctamente: ' . $proveedor->imagen);
                } else {
                    Log::error('Error al eliminar la imagen proveedor: ' . $proveedor->imagen);
                }
            }

            $path = Storage::putFile("proveedores", $request->file("imagen"));
            $validated['imagen'] = $path;
        } else {
            $validated['imagen'] = $categoria->imagen ?? 'SIN-IMAGEN';
        }

        $proveedor = $this->proveedorService->update($validated, $id);

        if (!$proveedor) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El proveedor se editó de manera exitosa',
            'proveedor' => [
                'id' => $proveedor->id,
                'tipo_identificacion' => $proveedor->tipo_identificacion,
                'sigla' => $proveedor->tipodocumento->sigla,
                'identificacion' => $proveedor->identificacion,
                'dv' => $proveedor->dv,
                'nombres' => $proveedor->nombres,
                'apellidos' => $proveedor->apellidos,
                'email' => $proveedor->email,
                'direccion' => is_null($proveedor->direccion) ? '' : $proveedor->direccion,
                'celular' => $proveedor->celular,
                'departamento_id' => $proveedor->departamento_id,
                'departamento' => $proveedor->departamento->nombre,
                'municipio_id' => $proveedor->municipio_id,
                'municipio' => $proveedor->municipio->nombre,
                'empresa_id' => $proveedor->empresa_id,
                'empresa' => $proveedor->empresa,
                'imagen' => $proveedor->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $proveedor->imagen : env("APP_URL") . "storage/proveedores/blank.png",
                'estado' => $proveedor->estado,
                "created_format_at" => $proveedor->created_at ? $proveedor->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Proveedor::class);

        $proveedor = $this->proveedorService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Proveedor activado de manera exitosa';
        } else {
            $texto = 'Proveedor eliminado de manera exitosa';
        }

        if ($proveedor == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Proveedor no encontrado',
                'proveedor' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'proveedor' => [
                'id' => $proveedor->id,
                'tipo_identificacion' => $proveedor->tipo_identificacion,
                'sigla' => $proveedor->tipodocumento->sigla,
                'identificacion' => $proveedor->identificacion,
                'dv' => $proveedor->dv,
                'nombres' => $proveedor->nombres,
                'apellidos' => $proveedor->apellidos,
                'email' => $proveedor->email,
                'direccion' => is_null($proveedor->direccion) ? '' : $proveedor->direccion,
                'celular' => $proveedor->celular,
                'departamento_id' => $proveedor->departamento_id,
                'departamento' => $proveedor->departamento->nombre,
                'municipio_id' => $proveedor->municipio_id,
                'municipio' => $proveedor->municipio->nombre,
                'empresa_id' => $proveedor->empresa_id,
                'empresa' => $proveedor->empresa,
                'imagen' => $proveedor->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $proveedor->imagen : env("APP_URL") . "storage/proveedores/blank.png",
                'estado' => $proveedor->estado,
                "created_format_at" => $proveedor->created_at ? $proveedor->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
