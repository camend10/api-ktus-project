<?php

namespace App\Http\Controllers\Articulos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articulos\BodegaArticuloRequest;
use App\Models\Articulos\Articulo;
use App\Services\Articulos\ArticuloService;
use Illuminate\Http\Request;

class BodegaArticuloController extends Controller
{

    protected $articuloService;

    public function __construct(ArticuloService $articuloService)
    {
        $this->articuloService = $articuloService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BodegaArticuloRequest $request)
    {
        $this->authorize('create', Articulo::class);
        $validated = $request->validated();
        try {
            $bodega_articulo = $this->articuloService->storeBodega($validated);

            if (!$bodega_articulo) {
                return response()->json([
                    'message' => 'El registro no pudo ser creado. Intente nuevamente.',
                    'error' => 'No se pudo crear el registro',
                ], 422);  // Unprocessable Entity
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La existencia de este articulo se agregó correctamente',
                'bodega_articulo' => [
                    "id" => $bodega_articulo->id,
                    "unidad" => $bodega_articulo->unidad,
                    "bodega" => $bodega_articulo->bodega,
                    "cantidad" => $bodega_articulo->cantidad
                ]
            ]);
        } catch (\Exception $e) {
            // Captura cualquier error inesperado y responde con 500
            return response()->json([
                'message' => 'Error al registrar.',
                'error' => $e->getMessage(),
            ], 500);  // Internal Server Error
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
    public function update(BodegaArticuloRequest $request, string $id)
    {
        $this->authorize('update', Articulo::class);
        $validated = $request->validated();
        try {
            $bodega_articulo = $this->articuloService->updateBodega($validated, $id);

            if (!$bodega_articulo) {
                return response()->json([
                    'message' => 'El registro no pudo ser creado. Intente nuevamente.',
                    'error' => 'No se pudo crear el registro',
                ], 422);  // Unprocessable Entity
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La existencia de este articulo se editó correctamente',
                'bodega_articulo' => [
                    "id" => $bodega_articulo->id,
                    "unidad" => $bodega_articulo->unidad,
                    "bodega" => $bodega_articulo->bodega,
                    "cantidad" => $bodega_articulo->cantidad
                ]
            ]);
        } catch (\Exception $e) {
            // Captura cualquier error inesperado y responde con 500
            return response()->json([
                'message' => 'Error al registrar.',
                'error' => $e->getMessage(),
            ], 500);  // Internal Server Error
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete', Articulo::class);

        try {
            $bodega_articulo = $this->articuloService->destroyBodega($id);

            if (!$bodega_articulo) {
                return response()->json([
                    'message' => 'El registro no pudo ser creado. Intente nuevamente.',
                    'error' => 'No se pudo crear el registro',
                ], 422);  // Unprocessable Entity
            }

            return response()->json([
                'message' => 200,  
                'message_text' => 'La existencia de este articulo se eliminó correctamente',            
            ]);
        } catch (\Exception $e) {
            // Captura cualquier error inesperado y responde con 500
            return response()->json([
                'message' => 'Error al registrar.',
                'error' => $e->getMessage(),
            ], 500);  // Internal Server Error
        }
    }
}
