<?php

namespace App\Http\Controllers\Articulos;

use App\Exports\Articulo\DownloadArticulo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Articulos\ArticuloRequest;
use App\Http\Requests\Articulos\ImportArticuloRequest;
use App\Http\Resources\Articulo\ArticuloCollection;
use App\Http\Resources\Articulo\ArticuloResource;
use App\Imports\ArticuloImport;
use App\Models\Articulos\Articulo;
use App\Services\Articulos\ArticuloService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ArticuloController extends Controller
{
    protected $articuloService;

    public function __construct(ArticuloService $articuloService)
    {
        $this->articuloService = $articuloService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Articulo::class);

        $data = $request->all();

        $articulos = $this->articuloService->getByFilter($data);

        if (!$articulos) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'total' => $articulos->total(),
            'articulos' => ArticuloCollection::make($articulos)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticuloRequest $request)
    {
        $this->authorize('create', Articulo::class);

        $validated = $request->validated();

        if ($request->hasFile("imagen")) {
            $path = Storage::putFile("articulos", $request->file("imagen"));
            $validated['imagen'] = $path;
        } else {
            $validated['imagen'] = "SIN-IMAGEN";
        }

        try {
            $articulo = $this->articuloService->store($validated);

            if (!$articulo) {
                return response()->json([
                    'message' => 'El artículo no pudo ser creado. Intente nuevamente.',
                    'error' => 'No se pudo crear el artículo',
                ], 422);  // Unprocessable Entity
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'EL articulo se registró de manera exitosa',
            ]);
        } catch (\Exception $e) {
            // Captura cualquier error inesperado y responde con 500
            return response()->json([
                'message' => 'Error al registrar el artículo.',
                'error' => $e->getMessage(),
            ], 500);  // Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $articulo = $this->articuloService->getArticuloById($id);

        if (!$articulo) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => '',
            'articulo' => ArticuloResource::make($articulo)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ArticuloRequest $request, string $id)
    {
        $this->authorize('update', Articulo::class);

        $validated = $request->validated();

        $articulo = $this->articuloService->getArticuloById($request->id);

        if ($request->hasFile("imagen")) {
            if ($articulo->imagen && $articulo->imagen !== 'SIN-IMAGEN') {
                if (Storage::delete($articulo->imagen)) {
                    Log::info('Imagen eliminada correctamente: ' . $articulo->imagen);
                } else {
                    Log::error('Error al eliminar la imagen: ' . $articulo->imagen);
                }
            }

            $path = Storage::putFile("articulos", $request->file("imagen"));
            $validated['imagen'] = $path;
        } else {
            $validated['imagen'] = $articulo->imagen ?? 'SIN-IMAGEN';
        }

        $articulo = $this->articuloService->update($validated, $id);

        if (!$articulo) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => 'El articulo se editó de manera exitosa',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Articulo::class);

        $articulo = $this->articuloService->cambiarEstado($request, $id);

        if ($request->estado == "1" || $request->estado == 1) {
            $texto = 'Articulo activada de manera exitosa';
        } else {
            $texto = 'Articulo eliminada de manera exitosa';
        }

        if ($articulo == false) {
            return response()->json([
                'message' => 403,
                'message_text' => 'Articulo no encontrado',
                'articulo' => []
            ], 403);
        }

        return response()->json([
            'message' => 200,
            'message_text' => $texto,
            'articulo' => $articulo
        ]);
    }

    public function export_articulo(Request $request)
    {
        $data = $request->all();

        $articulos = $this->articuloService->getAllArticulo($data);

        return Excel::download(new DownloadArticulo($articulos), 'Articulos_descargados.xlsx');
    }

    public function import_articulo(ImportArticuloRequest $request)
    {
        $validated = $request->validated();

        $path = $request->file('import_file');

        $data = Excel::import(new ArticuloImport(), $path);

        return response()->json([
            'message' => 200,
            'message_text' => 'Los articulo han sido importados exitosamente',
        ]);
    }
}
