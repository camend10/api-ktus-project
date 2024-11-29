<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Iva\IvaRequest;
use App\Models\Configuracion\Iva;
use App\Services\Configuracion\IvaService;
use Illuminate\Http\Request;

class IvaController extends Controller
{

    protected $ivaService;

    public function __construct(IvaService $ivaService)
    {
        $this->ivaService = $ivaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Iva::class);

        $buscar = $request->get('buscar');
        $ivas = $this->ivaService->getByFilter($buscar);

        if (!$ivas) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $ivas->total(),
            'ivas' => $ivas->map(function ($iva) {
                return [
                    'id' => $iva->id,
                    'porcentaje' => $iva->porcentaje,
                    'empresa_id' => $iva->empresa_id,
                    'empresa' => $iva->empresa,
                    'estado' => $iva->estado,
                    "created_format_at" => $iva->created_at ? $iva->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(IvaRequest $request)
    {

        $this->authorize('create', Iva::class);

        $iva = $this->ivaService->store($request->validated());

        if (!$iva) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El porcentaje de iva se registró de manera exitosa',
            'iva' => [
                'id' => $iva->id,
                'porcentaje' => $iva->porcentaje,
                'empresa_id' => $iva->empresa_id,
                'empresa' => $iva->empresa,
                'estado' => $iva->estado,
                "created_format_at" => $iva->created_at ? $iva->created_at->format("Y-m-d h:i A") : ''
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
    public function update(IvaRequest $request, string $id)
    {
        $this->authorize('update', Iva::class);

        $iva = $this->ivaService->update($request->validated(), $id);

        if (!$iva) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El porcentaje de iva se editó de manera exitosa',
            'iva' => [
                'id' => $iva->id,
                'porcentaje' => $iva->porcentaje,
                'empresa_id' => $iva->empresa_id,
                'empresa' => $iva->empresa,
                'estado' => $iva->estado,
                "created_format_at" => $iva->created_at ? $iva->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Iva::class);

        $iva = $this->ivaService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Porcentaje de iva activado de manera exitosa';
        } else {
            $texto = 'Porcentaje de iva eliminado de manera exitosa';
        }

        if ($iva == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Porcentaje de iva no encontrado',
                'iva' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'iva' => [
                'id' => $iva->id,
                'porcentaje' => $iva->porcentaje,
                'empresa_id' => $iva->empresa_id,
                'empresa' => $iva->empresa,
                'estado' => $iva->estado,
                "created_format_at" => $iva->created_at ? $iva->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
