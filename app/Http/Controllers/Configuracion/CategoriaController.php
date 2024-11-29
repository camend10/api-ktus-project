<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categorias\CategoriaRequest;
use App\Models\Configuracion\Categoria;
use App\Services\Configuracion\CategoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


class CategoriaController extends Controller
{
    protected $categoriaService;

    public function __construct(CategoriaService $categoriaService)
    {
        $this->categoriaService = $categoriaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Categoria::class);

        $buscar = $request->get('buscar');
        $categorias = $this->categoriaService->getByFilter($buscar);

        if (!$categorias) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $categorias->total(),
            'categorias' => $categorias->map(function ($categoria) {
                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'imagen' => $categoria->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $categoria->imagen : env("APP_URL") . "storage/categorias/blank.png",
                    'descripcion' => is_null($categoria->descripcion) ? '' : $categoria->descripcion,
                    'empresa_id' => $categoria->empresa_id,
                    'empresa' => $categoria->empresa,
                    'estado' => $categoria->estado,
                    "created_format_at" => $categoria->created_at ? $categoria->created_at->format("Y-m-d h:i A") : ''
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoriaRequest $request)
    {
        $this->authorize('create', Categoria::class);

        $validated = $request->validated();

        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("categorias", $request->file("imagen"));
            $validated['imagen'] = $path;
        } else {
            $validated['imagen'] = "SIN-IMAGEN";
        }

        $categoria = $this->categoriaService->store($validated);

        if (!$categoria) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La categoria se registró de manera exitosa',
            'categoria' => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'imagen' => $categoria->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $categoria->imagen : env("APP_URL") . "storage/categorias/blank.png",
                'descripcion' => is_null($categoria->descripcion) ? '' : $categoria->descripcion,
                'empresa_id' => $categoria->empresa_id,
                'empresa' => $categoria->empresa,
                'estado' => $categoria->estado,
                "created_format_at" => $categoria->created_at ? $categoria->created_at->format("Y-m-d h:i A") : ''
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
    public function update(CategoriaRequest $request, string $id)
    {
        $this->authorize('update', Categoria::class);

        $validated = $request->validated();

        $categoria = $this->categoriaService->getCategoriaById($request->id);

        if ($request->hasFile("imagen")) {
            if ($categoria->imagen && $categoria->imagen !== 'SIN-IMAGEN') {
                if (Storage::delete($categoria->imagen)) {
                    Log::info('Imagen eliminada correctamente: ' . $categoria->imagen);
                } else {
                    Log::error('Error al eliminar la imagen: ' . $categoria->imagen);
                }
            }

            $path = Storage::putFile("categorias", $request->file("imagen"));
            $validated['imagen'] = $path;
        } else {
            $validated['imagen'] = $categoria->imagen ?? 'SIN-IMAGEN';
        }

        $categoria = $this->categoriaService->update($validated, $id);

        if (!$categoria) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'La categoria se editó de manera exitosa',
            'categoria' => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'imagen' => $categoria->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $categoria->imagen : env("APP_URL") . "storage/categorias/blank.png",
                'descripcion' => is_null($categoria->descripcion) ? '' : $categoria->descripcion,
                'empresa_id' => $categoria->empresa_id,
                'empresa' => $categoria->empresa,
                'estado' => $categoria->estado,
                "created_format_at" => $categoria->created_at ? $categoria->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Categoria::class);

        $categoria = $this->categoriaService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Categoria activada de manera exitosa';
        } else {
            $texto = 'Categoria eliminada de manera exitosa';
        }

        if ($categoria == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Categoria no encontrada',
                'categoria' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'categoria' => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'imagen' => $categoria->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $categoria->imagen : env("APP_URL") . "storage/categorias/blank.png",
                'descripcion' => is_null($categoria->descripcion) ? '' : $categoria->descripcion,
                'empresa_id' => $categoria->empresa_id,
                'empresa' => $categoria->empresa,
                'estado' => $categoria->estado,
                "created_format_at" => $categoria->created_at ? $categoria->created_at->format("Y-m-d h:i A") : ''
            ]
        ]);
    }
}
