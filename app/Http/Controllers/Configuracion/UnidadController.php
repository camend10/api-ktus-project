<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Unidades\UnidadRequest;
use App\Http\Requests\Unidades\UnidadTransformacionRequest;
use App\Models\Configuracion\Unidad;
use App\Services\Configuracion\UnidadService;
use Illuminate\Http\Request;

class UnidadController extends Controller
{
    protected $unidadService;

    public function __construct(UnidadService $unidadService)
    {
        $this->unidadService = $unidadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Unidad::class);

        $buscar = $request->get('buscar');
        $unidades = $this->unidadService->getByFilter($buscar);

        if (!$unidades) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $unidades->total(),
            'unidades' => $unidades->map(function ($unidad) {
                return [
                    'id' => $unidad->id,
                    'nombre' => $unidad->nombre,
                    'sigla' => $unidad->sigla,
                    'empresa_id' => $unidad->empresa_id,
                    'descripcion' => $unidad->descripcion ?? '',
                    'empresa' => $unidad->empresa,
                    'estado' => $unidad->estado,
                    'transformacion' => $unidad->transformacion->map(function ($transform) {
                        $transform->unidad_to = $transform->unidad_to;
                        return $transform;
                    }),
                    "created_format_at" => $unidad->created_at ? $unidad->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UnidadRequest $request)
    {

        $this->authorize('create', Unidad::class);

        $unidad = $this->unidadService->store($request->validated());

        if (!$unidad) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La unidad se registró de manera exitosa',
            'unidad' => [
                'id' => $unidad->id,
                'nombre' => $unidad->nombre,
                'sigla' => $unidad->sigla,
                'empresa_id' => $unidad->empresa_id,
                'descripcion' => $unidad->descripcion ?? '',
                'empresa' => $unidad->empresa,
                'estado' => $unidad->estado,
                'transformacion' => $unidad->transformacion->map(function ($transform) {
                    $transform->unidad_to = $transform->unidad_to;
                    return $transform;
                }),
                "created_format_at" => $unidad->created_at ? $unidad->created_at->format("Y-m-d h:i A") : ''
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
    public function update(UnidadRequest $request, string $id)
    {
        $this->authorize('update', Unidad::class);

        $unidad = $this->unidadService->update($request->validated(), $id);

        if (!$unidad) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La unidad se editó de manera exitosa',
            'unidad' => [
                'id' => $unidad->id,
                'nombre' => $unidad->nombre,
                'sigla' => $unidad->sigla,
                'empresa_id' => $unidad->empresa_id,
                'descripcion' => $unidad->descripcion ?? '',
                'empresa' => $unidad->empresa,
                'estado' => $unidad->estado,
                'transformacion' => $unidad->transformacion->map(function ($transform) {
                    $transform->unidad_to = $transform->unidad_to;
                    return $transform;
                }),
                "created_format_at" => $unidad->created_at ? $unidad->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Unidad::class);

        $unidad = $this->unidadService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Unidad activada de manera exitosa';
        } else {
            $texto = 'Unidad eliminada de manera exitosa';
        }

        if ($unidad == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Unidad no encontrada',
                'unidad' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'unidad' => [
                'id' => $unidad->id,
                'nombre' => $unidad->nombre,
                'sigla' => $unidad->sigla,
                'descripcion' => $unidad->descripcion ?? '',
                'empresa_id' => $unidad->empresa_id,
                'empresa' => $unidad->empresa,
                'estado' => $unidad->estado,
                'transformacion' => $unidad->transformacion->map(function ($transform) {
                    $transform->unidad_to = $transform->unidad_to;
                    return $transform;
                }),
                "created_format_at" => $unidad->created_at ? $unidad->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    public function add_transformacion(UnidadTransformacionRequest $request)
    {

        $unidadTransformacion = $this->unidadService->storeTranformacion($request->validated());

        if (!$unidadTransformacion) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La unidad de tranformación se registró de manera exitosa',
            'unidadTransformacion' => [
                'id' => $unidadTransformacion->id,
                'unidad_id' => $unidadTransformacion->unidad_id,
                'unidad_to_id' => $unidadTransformacion->unidad_to_id,
                'empresa_id' => $unidadTransformacion->empresa_id,
                'unidad_to' => $unidadTransformacion->unidad_to,
                'estado' => $unidadTransformacion->estado,
                "created_format_at" => $unidadTransformacion->created_at ? $unidadTransformacion->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    public function delete_transformacion($id)
    {
        $unidadTranformacion = $this->unidadService->delete_transformacion($id);

        if (!$unidadTranformacion) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La unidad de tranformación se elimino de manera exitosa',
            'unidadTransformacion' => []
        ]);
    }
}
