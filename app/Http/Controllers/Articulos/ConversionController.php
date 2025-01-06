<?php

namespace App\Http\Controllers\Articulos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articulos\ConversionRequest;
use App\Http\Resources\Articulo\Conversion\ConversionCollection;
use App\Http\Resources\Articulo\Conversion\ConversionResource;
use App\Models\Articulos\Conversion;
use App\Services\Articulos\ConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConversionController extends Controller
{
    protected $conversionService;

    public function __construct(ConversionService $conversionService)
    {
        $this->conversionService = $conversionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Conversion::class);

        $data = $request->all();
        $conversiones = $this->conversionService->getByFilter($data);

        if (!$conversiones) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $conversiones->total(),
            'conversiones' => ConversionCollection::make($conversiones)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ConversionRequest $request)
    {

        $this->authorize('create', Conversion::class);

        try {
            $conversion = $this->conversionService->store($request->validated());

            // Verificar si el servicio retornó un error
            if (isset($conversion['error']) && $conversion['error']) {

                return response()->json([
                    'message' => $conversion['code'],
                    'message_text' => $conversion['message'],
                ]);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La conversión se registró de manera exitosa',
                'conversion' => ConversionResource::make($conversion)
            ]);
        } catch (\Exception $e) {

            // Manejo de excepciones
            Log::error('Error al crear la conversión: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

            // Retorna un error genérico
            return response()->json([
                'message' => 500,
                'message_text' => 'Ocurrió un error inesperado durante la creación de la conversión.',
            ], 500);
        }
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
    public function update(ConversionRequest $request, string $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Conversion::class);

        try {
            $conversion = $this->conversionService->cambiarEstado($request, $id);

            if ($request->estado == "1" || $request->estado == 1) {
                $texto = 'conversión activada de manera exitosa';
            } else {
                $texto = 'conversión eliminada de manera exitosa';
            }
            // Verificar si el servicio retornó un error
            if (isset($conversion['error']) && $conversion['error']) {

                return response()->json([
                    'message' => $conversion['code'],
                    'message_text' => $conversion['message'],
                ]);
            }

            return response()->json([
                'message' => 200,
                'message_text' => $texto,
                'conversion' => ConversionResource::make($conversion)
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar la conversión: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

            return response()->json([
                'message' => 500,
                'message_text' => 'Ocurrió un error inesperado durante la eliminación de la conversión.',
            ], 500);
        }
    }
}
