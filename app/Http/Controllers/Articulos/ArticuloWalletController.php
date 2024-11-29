<?php

namespace App\Http\Controllers\Articulos;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articulos\ArticuloWalletRequest;
use App\Models\Articulos\Articulo;
use App\Services\Articulos\ArticuloService;
use Illuminate\Http\Request;

class ArticuloWalletController extends Controller
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
    public function store(ArticuloWalletRequest $request)
    {
        $this->authorize('create', Articulo::class);
        $validated = $request->validated();
        try {
            $articulo_wallet = $this->articuloService->storeWallet($validated);
            if (!$articulo_wallet) {
                return response()->json([
                    'message' => 'El registro no pudo ser creado. Intente nuevamente.',
                    'error' => 'No se pudo crear el registro',
                ], 422);  // Unprocessable Entity
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'El precio de este articulo se editó correctamente',
                'articulo_wallet' => [
                    "id" => $articulo_wallet->id,
                    "unidad" => $articulo_wallet->unidad,
                    "sede" => $articulo_wallet->sede,
                    "segmento_cliente" => $articulo_wallet->segmento_cliente,
                    "precio" => $articulo_wallet->precio,
                    "sede_id_premul" => $articulo_wallet->sede ? $articulo_wallet->sede->id : null,
                    "segmento_cliente_id_premul" => $articulo_wallet->segmento_cliente ? $articulo_wallet->segmento_cliente->id : null,
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
    public function update(ArticuloWalletRequest $request, string $id)
    {
        $this->authorize('update', Articulo::class);
        $validated = $request->validated();
        try {
            $articulo_wallet = $this->articuloService->updateWallet($validated, $id);

            if (!$articulo_wallet) {
                return response()->json([
                    'message' => 'El registro no pudo ser creado. Intente nuevamente.',
                    'error' => 'No se pudo crear el registro',
                ], 422);  // Unprocessable Entity
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'El precio de este articulo se editó correctamente',
                'articulo_wallet' => [
                    "id" => $articulo_wallet->id,
                    "unidad" => $articulo_wallet->unidad,
                    "sede" => $articulo_wallet->sede,
                    "segmento_cliente" => $articulo_wallet->segmento_cliente,
                    "precio" => $articulo_wallet->precio,
                    "sede_id_premul" => $articulo_wallet->sede ? $articulo_wallet->sede->id : null,
                    "segmento_cliente_id_premul" => $articulo_wallet->segmento_cliente ? $articulo_wallet->segmento_cliente->id : null,
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
            $articulo_wallet = $this->articuloService->destroyWallet($id);

            if (!$articulo_wallet) {
                return response()->json([
                    'message' => 'El registro no pudo ser creado. Intente nuevamente.',
                    'error' => 'No se pudo crear el registro',
                ], 422);  // Unprocessable Entity
            }

            return response()->json([
                'message' => 200,          
                'message_text' => 'El precio de este articulo se eliminó correctamente',    
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
