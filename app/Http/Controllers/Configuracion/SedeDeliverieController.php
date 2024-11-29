<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\SedeDeliveries\SedeDeliverieRequest;
use App\Models\Configuracion\SedeDeliverie;
use App\Services\Configuracion\SedeDeliverieService;
use Illuminate\Http\Request;

class SedeDeliverieController extends Controller
{

    protected $sedeDeliverieService;

    public function __construct(SedeDeliverieService $sedeDeliverieService)
    {
        $this->sedeDeliverieService = $sedeDeliverieService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SedeDeliverie::class);

        $buscar = $request->get('buscar');
        $sedeDeliveries = $this->sedeDeliverieService->getByFilter($buscar);

        if (!$sedeDeliveries) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $sedeDeliveries->total(),
            'sedeDeliveries' => $sedeDeliveries->map(function ($sedeDeliverie) {
                return [
                    'id' => $sedeDeliverie->id,
                    'nombre' => $sedeDeliverie->nombre,
                    'direccion' => $sedeDeliverie->direccion ?? '',
                    'empresa_id' => $sedeDeliverie->empresa_id,
                    'empresa' => $sedeDeliverie->empresa,
                    'sede_id' => $sedeDeliverie->sede_id,
                    'sede' => $sedeDeliverie->sede,
                    'estado' => $sedeDeliverie->estado,
                    "created_format_at" => $sedeDeliverie->created_at ? $sedeDeliverie->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SedeDeliverieRequest $request)
    {
        $this->authorize('create', SedeDeliverie::class);

        $sedeDeliverie = $this->sedeDeliverieService->store($request->validated());

        if (!$sedeDeliverie) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'EL lugar de entrega se registró de manera exitosa',
            'sedeDeliverie' => [
                'id' => $sedeDeliverie->id,
                'nombre' => $sedeDeliverie->nombre,
                'direccion' => $sedeDeliverie->direccion ?? '',
                'empresa_id' => $sedeDeliverie->empresa_id,
                'empresa' => $sedeDeliverie->empresa,
                'sede_id' => $sedeDeliverie->sede_id,
                'sede' => $sedeDeliverie->sede,
                'estado' => $sedeDeliverie->estado,
                "created_format_at" => $sedeDeliverie->created_at ? $sedeDeliverie->created_at->format("Y-m-d h:i A") : ''
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
    public function update(SedeDeliverieRequest $request, string $id)
    {
        $this->authorize('update', SedeDeliverie::class);

        $sedeDeliverie = $this->sedeDeliverieService->update($request->validated(), $id);

        if (!$sedeDeliverie) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'EL lugar de entrega se editó de manera exitosa',
            'sedeDeliverie' => [
                'id' => $sedeDeliverie->id,
                'nombre' => $sedeDeliverie->nombre,
                'direccion' => $sedeDeliverie->direccion ?? '',
                'empresa_id' => $sedeDeliverie->empresa_id,
                'empresa' => $sedeDeliverie->empresa,
                'sede_id' => $sedeDeliverie->sede_id,
                'sede' => $sedeDeliverie->sede,
                'estado' => $sedeDeliverie->estado,
                "created_format_at" => $sedeDeliverie->created_at ? $sedeDeliverie->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', SedeDeliverie::class);

        $sedeDeliverie = $this->sedeDeliverieService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Lugar de entrega activado de manera exitosa';
        } else {
            $texto = 'Lugar de entrega eliminado de manera exitosa';
        }

        if ($sedeDeliverie == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Lugar de entrega no encontrado',
                'sedeDeliverie' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'sedeDeliverie' => [
                'id' => $sedeDeliverie->id,
                'nombre' => $sedeDeliverie->nombre,
                'direccion' => $sedeDeliverie->direccion ?? '',
                'empresa_id' => $sedeDeliverie->empresa_id,
                'empresa' => $sedeDeliverie->empresa,
                'sede_id' => $sedeDeliverie->sede_id,
                'sede' => $sedeDeliverie->sede,
                'estado' => $sedeDeliverie->estado,
                "created_format_at" => $sedeDeliverie->created_at ? $sedeDeliverie->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
