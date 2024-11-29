<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\SegmentoClientes\SegmentoClienteRequest;
use App\Models\Configuracion\SegmentoCliente;
use App\Services\Configuracion\SegmentoClienteService;
use Illuminate\Http\Request;

class SegmentoClienteController extends Controller
{
    protected $segmentoClienteService;

    public function __construct(SegmentoClienteService $segmentoClienteService)
    {
        $this->segmentoClienteService = $segmentoClienteService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', SegmentoCliente::class);

        $buscar = $request->get('buscar');
        $segmentoClientes = $this->segmentoClienteService->getByFilter($buscar);

        if (!$segmentoClientes) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $segmentoClientes->total(),
            'segmentoClientes' => $segmentoClientes->map(function ($segmentoCliente) {
                return [
                    'id' => $segmentoCliente->id,
                    'nombre' => $segmentoCliente->nombre,
                    'empresa_id' => $segmentoCliente->empresa_id,
                    'empresa' => $segmentoCliente->empresa,
                    'estado' => $segmentoCliente->estado,
                    "created_format_at" => $segmentoCliente->created_at ? $segmentoCliente->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SegmentoClienteRequest $request)
    {

        $this->authorize('create', SegmentoCliente::class);

        $segmentoCliente = $this->segmentoClienteService->store($request->validated());

        if (!$segmentoCliente) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El segmento de cliente se registró de manera exitosa',
            'segmentoCliente' => [
                'id' => $segmentoCliente->id,
                'nombre' => $segmentoCliente->nombre,
                'empresa_id' => $segmentoCliente->empresa_id,
                'empresa' => $segmentoCliente->empresa,
                'estado' => $segmentoCliente->estado,
                "created_format_at" => $segmentoCliente->created_at ? $segmentoCliente->created_at->format("Y-m-d h:i A") : ''
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
    public function update(SegmentoClienteRequest $request, string $id)
    {
        $this->authorize('update', SegmentoCliente::class);

        $segmentoCliente = $this->segmentoClienteService->update($request->validated(), $id);

        if (!$segmentoCliente) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El segmento de cliente se editó de manera exitosa',
            'segmentoCliente' => [
                'id' => $segmentoCliente->id,
                'nombre' => $segmentoCliente->nombre,
                'empresa_id' => $segmentoCliente->empresa_id,
                'empresa' => $segmentoCliente->empresa,
                'estado' => $segmentoCliente->estado,
                "created_format_at" => $segmentoCliente->created_at ? $segmentoCliente->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', SegmentoCliente::class);

        $segmentoCliente = $this->segmentoClienteService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Segmento de cliente activado de manera exitosa';
        } else {
            $texto = 'Segmento de cliente eliminado de manera exitosa';
        }

        if ($segmentoCliente == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Segmento de cliente no encontrado',
                'segmentoCliente' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'segmentoCliente' => [
                'id' => $segmentoCliente->id,
                'nombre' => $segmentoCliente->nombre,
                'empresa_id' => $segmentoCliente->empresa_id,
                'empresa' => $segmentoCliente->empresa,
                'estado' => $segmentoCliente->estado,
                "created_format_at" => $segmentoCliente->created_at ? $segmentoCliente->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
