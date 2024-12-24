<?php

namespace App\Http\Controllers\Movimientos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Movimientos\PlantillaRequest;
use App\Models\Movimientos\Plantilla;
use App\Services\Movimientos\PlantillaService;
use Illuminate\Http\Request;

class PlantillaController extends Controller
{
    protected $plantillaService;

    public function __construct(PlantillaService $plantillaService)
    {
        $this->plantillaService = $plantillaService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Plantilla::class);

        $buscar = $request->get('buscar');
        $plantillas = $this->plantillaService->getByFilter($buscar);

        if (!$plantillas) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $plantillas->total(),
            'plantillas' => $plantillas,
            'plantillas' => $plantillas->map(function ($plantilla) {
                return [
                    'id' => $plantilla->id,
                    'nombre' => $plantilla->nombre,
                    'codigo' => $plantilla->codigo,
                    'observacion' => $plantilla->observacion ?? '',
                    'user_id' => $plantilla->user_id,
                    'usuario' => $plantilla->usuario,
                    'empresa_id' => $plantilla->empresa_id,
                    'empresa' => $plantilla->empresa,
                    'sede_id' => $plantilla->sede_id,
                    'sede' => $plantilla->sede,
                    'estado' => $plantilla->estado,
                    'detalles_plantillas' => $plantilla->detalles_plantillas,
                    "created_format_at" => $plantilla->created_at ? $plantilla->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    public function store(PlantillaRequest $request)
    {
        $this->authorize('create', Plantilla::class);

        $validated = $request->validated();

        try {
            $plantilla = $this->plantillaService->store($validated);

            if (!$plantilla) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La plantilla se registró de manera exitosa',
                'plantilla' => $plantilla
            ]);
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function update(PlantillaRequest $request, string $id)
    {
        $this->authorize('update', Plantilla::class);

        $validated = $request->validated();

        try {
            $plantilla = $this->plantillaService->update($validated, $id);

            if (!$plantilla) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La plantilla se editó de manera exitosa',
                'plantilla' => $plantilla
            ]);
        } catch (\Exception $e) {
            // Lanza nuevamente la excepción para que se gestione adecuadamente
            throw $e;
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Plantilla::class);

        $plantilla = $this->plantillaService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Plantilla activada de manera exitosa';
        } else {
            $texto = 'Plantilla eliminada de manera exitosa';
        }

        if ($plantilla == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Plantilla no encontrada',
                'plantilla' => []
            ], 403);
        }
        $plantilla = $this->plantillaService->getById($id);

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'plantilla' => $plantilla
        ]);
    }


    public function show(string $id)
    {
        $plantilla = $this->plantillaService->getById($id);

        if (!$plantilla) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => '',
            'plantilla' => $plantilla
        ]);
    }
}
