<?php

namespace App\Http\Controllers\Movimientos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Solicitudes\SolicitudRequest;
use App\Http\Resources\Movimientos\MovimientoCollection;
use App\Http\Resources\Movimientos\MovimientoResource;
use App\Models\Movimientos\Movimiento;
use App\Services\Movimientos\MovimientoService;
use Illuminate\Http\Request;

class MovimientoController extends Controller
{
    protected $movimientoService;

    public function __construct(MovimientoService $movimientoService)
    {
        $this->movimientoService = $movimientoService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Movimiento::class);

        $data = $request->all();
        $movimientos = $this->movimientoService->getByFilter($data);
        if (!$movimientos) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $movimientos->total(),
            'movimientos' => MovimientoCollection::make($movimientos),
        ]);
    }

    public function store(SolicitudRequest $request)
    {
        $this->authorize('create', Movimiento::class);

        $validated = $request->validated();

        try {
            $movimiento = $this->movimientoService->store($validated);

            if (!$movimiento) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'El movimiento se registró de manera exitosa',
                'movimiento' => MovimientoResource::make($movimiento)
            ]);
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function update(SolicitudRequest $request, string $id)
    {
        $this->authorize('update', Movimiento::class);

        $validated = $request->validated();

        try {
            $movimiento = $this->movimientoService->update($validated, $id);

            if (!$movimiento) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'El movimiento se editó de manera exitosa',
                'movimiento' => MovimientoResource::make($movimiento)
            ]);
        } catch (\Exception $e) {
            // Lanza nuevamente la excepción para que se gestione adecuadamente
            throw $e;
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Movimiento::class);

        $movimiento = $this->movimientoService->getById($id);
        if ($movimiento->estado == 3 || $movimiento->estado == 4) {
            return response()->json([
                'message' => 200,
                'message_text' => 'El movimiento ya se encuentra en un estado donde no se puede eliminar',
                'movimiento' => MovimientoResource::make($movimiento)
            ]);
        }

        $movimiento = $this->movimientoService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Movimiento activado de manera exitosa';
        } else {
            $texto = 'Movimiento eliminado de manera exitosa';
        }

        if ($movimiento == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Movimiento no encontrado',
                'movimiento' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'movimiento' => MovimientoResource::make($movimiento)
        ]);
    }

    public function show(string $id)
    {
        $movimiento = $this->movimientoService->getById($id);

        if (!$movimiento) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => '',
            'movimiento' => MovimientoResource::make($movimiento)
        ]);
    }

    public function entrada(Request $request)
    {
        $this->authorize('entrada', Movimiento::class);

        try {

            $movimiento = $this->movimientoService->entrada($request);

            if (!$movimiento) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La entrega se realizó de manera exitosa',
                'movimiento' => $movimiento
            ]);
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function salida(Request $request)
    {
        $this->authorize('salida', Movimiento::class);

        try {

            $movimiento = $this->movimientoService->entrada($request);

            if (!$movimiento) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La entrega se realizó de manera exitosa',
                'movimiento' => $movimiento
            ]);
        } catch (\Exception $e) {

            throw $e;
        }
    }
}
