<?php

namespace App\Http\Controllers\Facturas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Facturas\FacturaRequest;
use App\Http\Resources\Facturas\FacturaCollection;
use App\Http\Resources\Facturas\FacturaResource;
use App\Models\Facturas\Factura;
use App\Services\Facturas\FacturaService;
use Illuminate\Http\Request;

class FacturaController extends Controller
{
    protected $facturaService;

    public function __construct(FacturaService $facturaService)
    {
        $this->facturaService = $facturaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Factura::class);

        $data = $request->all();
        $facturas = $this->facturaService->getByFilter($data);

        if (!$facturas) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $facturas->total(),
            'facturas' => FacturaCollection::make($facturas),
        ]);
    }

    
    /**
     * Store a newly created resource in storage.
     */
    public function store(FacturaRequest $request)
    {
        $this->authorize('create', Factura::class);

        $validated = $request->validated();

        $factura = $this->facturaService->store($validated);

        if (!$factura) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La factura se registró de manera exitosa',
            'factura' => FacturaResource::make($factura)
        ]);
    }

    public function update(FacturaRequest $request, string $id)
    {
        $this->authorize('update', Factura::class);

        $validated = $request->validated();

        $factura = $this->facturaService->update($validated, $id);

        if (!$factura) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La factura se editó de manera exitosa',
            'factura' => FacturaResource::make($factura)
        ]);
    }

    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Factura::class);

        $factura = $this->facturaService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Factura activada de manera exitosa';
        } else {
            $texto = 'Factura eliminada de manera exitosa';
        }

        if ($factura == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Factura no encontrada',
                'proveedor' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'factura' => FacturaResource::make($factura)
        ]);
    }
}
