<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bodegas\BodegaRequest;
use App\Models\Configuracion\Bodega;
use App\Services\Configuracion\BodegaService;
use Illuminate\Http\Request;

class BodegaController extends Controller
{

    protected $bodegaService;

    public function __construct(BodegaService $bodegaService)
    {
        $this->bodegaService = $bodegaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Bodega::class);

        $buscar = $request->get('buscar');
        $bodegas = $this->bodegaService->getByFilter($buscar);

        if (!$bodegas) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $bodegas->total(),
            'bodegas' => $bodegas->map(function ($bodega) {
                return [
                    'id' => $bodega->id,
                    'nombre' => $bodega->nombre,
                    'descripcion' => $bodega->descripcion ?? '',
                    'empresa_id' => $bodega->empresa_id,
                    'empresa' => $bodega->empresa,
                    'sede_id' => $bodega->sede_id,
                    'sede' => $bodega->sede,
                    'estado' => $bodega->estado,
                    "created_format_at" => $bodega->created_at ? $bodega->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BodegaRequest $request)
    {

        $this->authorize('create', Bodega::class);

        $bodega = $this->bodegaService->store($request->validated());

        if (!$bodega) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La bodega se registró de manera exitosa',
            'bodega' => [
                'id' => $bodega->id,
                'nombre' => $bodega->nombre,
                'descripcion' => $bodega->descripcion ?? '',
                'empresa_id' => $bodega->empresa_id,
                'empresa' => $bodega->empresa,
                'sede_id' => $bodega->sede_id,
                'sede' => $bodega->sede,
                'estado' => $bodega->estado,
                "created_format_at" => $bodega->created_at ? $bodega->created_at->format("Y-m-d h:i A") : ''
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
    public function update(BodegaRequest $request, string $id)
    {
        $this->authorize('update', Bodega::class);

        $bodega = $this->bodegaService->update($request->validated(), $id);

        if (!$bodega) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La bodega se editó de manera exitosa',
            'bodega' => [
                'id' => $bodega->id,
                'nombre' => $bodega->nombre,
                'descripcion' => $bodega->descripcion ?? '',
                'empresa_id' => $bodega->empresa_id,
                'empresa' => $bodega->empresa,
                'sede_id' => $bodega->sede_id,
                'sede' => $bodega->sede,
                'estado' => $bodega->estado,
                "created_format_at" => $bodega->created_at ? $bodega->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Bodega::class);

        $bodega = $this->bodegaService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Bodega activada de manera exitosa';
        } else {
            $texto = 'Bodega eliminada de manera exitosa';
        }

        if ($bodega == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Bodega no encontrada',
                'bodega' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'bodega' => [
                'id' => $bodega->id,
                'nombre' => $bodega->nombre,
                'descripcion' => $bodega->descripcion ?? '',
                'empresa_id' => $bodega->empresa_id,
                'empresa' => $bodega->empresa,
                'sede_id' => $bodega->sede_id,
                'sede' => $bodega->sede,
                'estado' => $bodega->estado,
                "created_format_at" => $bodega->created_at ? $bodega->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
