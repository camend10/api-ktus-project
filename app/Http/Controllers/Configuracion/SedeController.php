<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sedes\SedeRequest;
use App\Models\Configuracion\Sede;
use App\Services\Configuracion\SedeService;
use Illuminate\Http\Request;

class SedeController extends Controller
{
    protected $sedeService;

    public function __construct(SedeService $sedeService)
    {
        $this->sedeService = $sedeService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sede::class);

        $buscar = $request->get('buscar');
        $sedes = $this->sedeService->getSedesByFilter($buscar);

        return response()->json([
            'total' => $sedes->total(),
            'sedes' => $sedes->map(function ($sede) {
                return [
                    'id' => $sede->id,
                    'codigo' => $sede->codigo,
                    'nombre' => $sede->nombre,
                    'direccion' => $sede->direccion,
                    'telefono' => $sede->telefono,
                    'celular' => $sede->celular,
                    'responsable' => $sede->responsable,
                    'telefono_responsable' => $sede->telefono_responsable,
                    'identificacion_responsable' => $sede->identificacion_responsable,
                    'empresa_id' => $sede->empresa_id,
                    'empresa' => $sede->empresa,
                    'estado' => $sede->estado,
                    'departamento_id' => $sede->departamento_id,
                    'municipio_id' => $sede->municipio_id,
                    'departamento' => $sede->departamento->nombre,
                    'municipio' => $sede->municipio->nombre,
                    "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SedeRequest $request)
    {
        $this->authorize('create', Sede::class);

        $validated = $request->validated();

        $sede = $this->sedeService->storeSede($validated);

        return response()->json([
            'message' => 200,
            'message_text' => 'La sede se registró de manera exitosa',
            'sede' => [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'celular' => $sede->celular,
                'responsable' => $sede->responsable,
                'telefono_responsable' => $sede->telefono_responsable,
                'identificacion_responsable' => $sede->identificacion_responsable,
                'empresa_id' => $sede->empresa_id,
                'empresa' => $sede->empresa,
                'estado' => $sede->estado,
                'departamento_id' => $sede->departamento_id,
                'municipio_id' => $sede->municipio_id,
                'departamento' => $sede->departamento->nombre,
                'municipio' => $sede->municipio->nombre,
                "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
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
    public function update(SedeRequest $request, string $id)
    {

        $this->authorize('update', Sede::class);

        $validated = $request->validated();

        $sede = $this->sedeService->updateSede($validated, $id);

        return response()->json([
            'message' => 200,
            'message_text' => 'La sede se editó de manera exitosa',
            'sede' => [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'celular' => $sede->celular,
                'responsable' => $sede->responsable,
                'telefono_responsable' => $sede->telefono_responsable,
                'identificacion_responsable' => $sede->identificacion_responsable,
                'empresa_id' => $sede->empresa_id,
                'empresa' => $sede->empresa,
                'estado' => $sede->estado,
                'departamento_id' => $sede->departamento_id,
                'municipio_id' => $sede->municipio_id,
                'departamento' => $sede->departamento->nombre,
                'municipio' => $sede->municipio->nombre,
                "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Sede::class);

        $sede = $this->sedeService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Sede activada de manera exitosa';
        } else {
            $texto = 'Sede eliminada de manera exitosa';
        }

        if ($sede == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Sede no encontrada',
                'sede' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'sede' => [
                'id' => $sede->id,
                'codigo' => $sede->codigo,
                'nombre' => $sede->nombre,
                'direccion' => $sede->direccion,
                'telefono' => $sede->telefono,
                'celular' => $sede->celular,
                'responsable' => $sede->responsable,
                'telefono_responsable' => $sede->telefono_responsable,
                'identificacion_responsable' => $sede->identificacion_responsable,
                'empresa_id' => $sede->empresa_id,
                'empresa' => $sede->empresa,
                'estado' => $sede->estado,
                'departamento_id' => $sede->departamento_id,
                'municipio_id' => $sede->municipio_id,
                'departamento' => $sede->departamento->nombre,
                'municipio' => $sede->municipio->nombre,
                "created_format_at" => $sede->created_at ? $sede->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
