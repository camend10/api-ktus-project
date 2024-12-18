<?php

namespace App\Http\Controllers\Solicitudes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Solicitudes\SolicitudRequest;
use App\Http\Resources\Movimientos\SolicitudCollection;
use App\Http\Resources\Movimientos\SolicitudResource;
use App\Models\Movimientos\Solicitud;
use App\Services\Movimientos\SolicitudService;
use Illuminate\Http\Request;

class SolicitudController extends Controller
{
    protected $solicitudService;

    public function __construct(SolicitudService $solicitudService)
    {
        $this->solicitudService = $solicitudService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Solicitud::class);

        $data = $request->all();
        $solicitudes = $this->solicitudService->getByFilter($data);
        if (!$solicitudes) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $solicitudes->total(),
            'solicitudes' => SolicitudCollection::make($solicitudes),
        ]);
    }

    public function store(SolicitudRequest $request)
    {
        $this->authorize('create', Solicitud::class);

        $validated = $request->validated();

        try {
            $solicitud = $this->solicitudService->store($validated);

            if (!$solicitud) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La solicitud se registró de manera exitosa',
                'solicitud' => SolicitudResource::make($solicitud)
            ]);
        } catch (\Exception $e) {

            throw $e;
        }
    }

    public function update(SolicitudRequest $request, string $id)
    {
        $this->authorize('update', Solicitud::class);

        $validated = $request->validated();

        try {
            $solicitud = $this->solicitudService->update($validated, $id);

            if (!$solicitud) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La solicitud se editó de manera exitosa',
                'solicitud' => SolicitudResource::make($solicitud)
            ]);
        } catch (\Exception $e) {
            // Lanza nuevamente la excepción para que se gestione adecuadamente
            throw $e;
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Solicitud::class);

        $solicitud = $this->solicitudService->getById($id);
        if ($solicitud->estado == 3 || $solicitud->estado == 4) {
            return response()->json([
                'message' => 200,
                'message_text' => 'La solicitud ya se encuentra en un estado donde no se puede eliminar',
                'solicitud' => SolicitudResource::make($solicitud)
            ]);
        }

        $solicitud = $this->solicitudService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Solicitud activada de manera exitosa';
        } else {
            $texto = 'Solicitud eliminada de manera exitosa';
        }

        if ($solicitud == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Solicitud no encontrada',
                'solicitud' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'solicitud' => SolicitudResource::make($solicitud)
        ]);
    }

    public function show(string $id)
    {
        $solicitud = $this->solicitudService->getById($id);

        if (!$solicitud) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => '',
            'solicitud' => SolicitudResource::make($solicitud)
        ]);
    }

    public function entrega(Request $request)
    {
        $this->authorize('entrega', Solicitud::class);

        try {
            
            $solicitud = $this->solicitudService->entrega($request);

            if (!$solicitud) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La entrega se realizó de manera exitosa',
                'solicitud' => $solicitud
            ]);
        } catch (\Exception $e) {

            throw $e;
        }
    }
}
