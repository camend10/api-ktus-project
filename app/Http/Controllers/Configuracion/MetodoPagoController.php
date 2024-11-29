<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\MetodoPagos\MetodoPagoRequest;
use App\Models\Configuracion\MetodoPago;
use App\Services\Configuracion\MetodoPagoService;
use Illuminate\Http\Request;

class MetodoPagoController extends Controller
{
    protected $metodoPagoService;

    public function __construct(MetodoPagoService $metodoPagoService)
    {
        $this->metodoPagoService = $metodoPagoService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', MetodoPago::class);

        $buscar = $request->get('buscar');
        $metodoPagos = $this->metodoPagoService->getByFilter($buscar);

        if (!$metodoPagos) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $metodoPagos->total(),
            'metodoPagos' => $metodoPagos->map(function ($metodoPago) {
                return [
                    'id' => $metodoPago->id,
                    'nombre' => $metodoPago->nombre,
                    'empresa_id' => $metodoPago->empresa_id,
                    'empresa' => $metodoPago->empresa,
                    // 'metodo_pago_id' => is_null($metodoPago->metodo_pago_id) ? null : $metodoPago->metodo_pago_id,
                    'metodo_pago_id' => $metodoPago->metodo_pago_id,
                    'metodo_pago' => $metodoPago->metodo_pago,
                    'metodo_pagos' => $metodoPago->metodo_pagos,
                    'estado' => $metodoPago->estado,
                    "created_format_at" => $metodoPago->created_at ? $metodoPago->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MetodoPagoRequest $request)
    {

        $this->authorize('create', MetodoPago::class);

        $metodoPago = $this->metodoPagoService->store($request->validated());        

        if (!$metodoPago) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El metodo de pago se registró de manera exitosa',
            'metodoPago' => [
                'id' => $metodoPago->id,
                'nombre' => $metodoPago->nombre,
                'empresa_id' => $metodoPago->empresa_id,
                'empresa' => $metodoPago->empresa,
                'metodo_pago_id' => $metodoPago->metodo_pago_id,
                'metodo_pago' => $metodoPago->metodo_pago,
                'metodo_pagos' => $metodoPago->metodo_pagos,
                'estado' => $metodoPago->estado,
                "created_format_at" => $metodoPago->created_at ? $metodoPago->created_at->format("Y-m-d h:i A") : ''
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
    public function update(MetodoPagoRequest $request, string $id)
    {
        $this->authorize('update', MetodoPago::class);

        $metodoPago = $this->metodoPagoService->update($request->validated(), $id);

        if (!$metodoPago) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El metodo de pago se editó de manera exitosa',
            'metodoPago' => [
                'id' => $metodoPago->id,
                'nombre' => $metodoPago->nombre,
                'empresa_id' => $metodoPago->empresa_id,
                'empresa' => $metodoPago->empresa,
                'metodo_pago_id' => $metodoPago->metodo_pago_id,
                'metodo_pago' => $metodoPago->metodo_pago,
                'metodo_pagos' => $metodoPago->metodo_pagos,
                'estado' => $metodoPago->estado,
                "created_format_at" => $metodoPago->created_at ? $metodoPago->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', MetodoPago::class);

        $metodoPago = $this->metodoPagoService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Metodo de pago activado de manera exitosa';
        } else {
            $texto = 'Metodo de pago eliminado de manera exitosa';
        }

        if ($metodoPago == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Metodo de pago no encontrado',
                'metodoPago' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'metodoPago' => [
                'id' => $metodoPago->id,
                'nombre' => $metodoPago->nombre,
                'empresa_id' => $metodoPago->empresa_id,
                'empresa' => $metodoPago->empresa,
                'metodo_pago_id' => is_null($metodoPago->metodo_pago_id) ? 9999999 : $metodoPago->metodo_pago_id,
                'metodo_pago' => $metodoPago->metodo_pago,
                'metodo_pagos' => $metodoPago->metodo_pagos,
                'estado' => $metodoPago->estado,
                "created_format_at" => $metodoPago->created_at ? $metodoPago->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
