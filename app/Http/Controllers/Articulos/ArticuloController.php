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
use App\Services\Configuracion\CategoriaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ArticuloController extends Controller
{
    protected $articuloService;
    protected $categoriaService;

    public function __construct(ArticuloService $articuloService, CategoriaService $categoriaService)
    {
        $this->articuloService = $articuloService;
        $this->categoriaService = $categoriaService;
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

        $num_art_agotados = $this->articuloService->getByDisponibilidad(3);
        $num_art_por_agotar = $this->articuloService->getByDisponibilidad(2);

        return response()->json([
            'total' => $articulos->total(),
            'articulos' => ArticuloCollection::make($articulos),
            'num_art_agotados' => $num_art_agotados,
            'num_art_por_agotar' => $num_art_por_agotar
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ArticuloRequest $request)
    {
        $this->authorize('create', Articulo::class);

        $validated = $request->validated();

        $imagenPath = null;
        if ($request->hasFile("imagen")) {
            $imagenPath = Storage::putFile("articulos", $request->file("imagen"));
            $validated['imagen'] = $imagenPath;
        } else {
            $validated['imagen'] = "SIN-IMAGEN";
        }

        try {
            $articulo = $this->articuloService->store($validated);

            if (!$articulo) {

                if ($imagenPath) {
                    Storage::delete($imagenPath);
                }

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
            // Si hay un error, elimina la imagen subida
            if ($imagenPath) {
                Storage::delete($imagenPath);
            }
            // Manejo de excepciones
            Log::error('Error al crear el articulo: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

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
        $imagenPath = null;
        try {
            $articulo = $this->articuloService->getArticuloById($request->id);

            if ($request->hasFile("imagen")) {
                if ($articulo->imagen && $articulo->imagen !== 'SIN-IMAGEN') {
                    if (Storage::delete($articulo->imagen)) {
                        Log::info('Imagen eliminada correctamente: ' . $articulo->imagen);
                    } else {
                        Log::error('Error al eliminar la imagen: ' . $articulo->imagen);
                    }
                }

                $imagenPath = Storage::putFile("articulos", $request->file("imagen"));
                $validated['imagen'] = $imagenPath;
            } else {
                $validated['imagen'] = $articulo->imagen ?? 'SIN-IMAGEN';
            }

            $articulo = $this->articuloService->update($validated, $id);

            if (!$articulo) {
                if ($imagenPath) {
                    Storage::delete($imagenPath);
                }

                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'El articulo se editó de manera exitosa',
            ]);
        } catch (\Exception $e) {
            // Si hay un error, elimina la imagen subida
            if ($imagenPath) {
                Storage::delete($imagenPath);
            }
            // Manejo de excepciones
            Log::error('Error al editar el articulo: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

            // Captura cualquier error inesperado y responde con 500
            return response()->json([
                'message' => 'Error al editar el artículo.',
                'error' => $e->getMessage(),
            ], 500);  // Internal Server Error
        }
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
            'articulo' => ArticuloResource::make($articulo)
        ]);
    }

    public function export_articulo(Request $request)
    {
        $data = $request->all();

        $articulos = $this->articuloService->getAllArticulos($data);

        return Excel::download(new DownloadArticulo($articulos), 'Articulos_descargados.xlsx');
    }

    public function import_articulo(ImportArticuloRequest $request)
    {
        $validated = $request->validated();

        $path = $request->file('import_file');

        $data = Excel::import(new ArticuloImport(), $path);

        return response()->json([
            'message' => 200,
            'message_text' => 'Los articulos han sido importados exitosamente',
        ]);
    }

    public function generarSku($categoria_id)
    {

        // Obtener el nombre de la categoría (para usar como prefijo)
        $categoria = $this->categoriaService->getCategoriaById($categoria_id);

        if (!$categoria) {
            return response()->json(['error' => 'Categoría no encontrada'], 404);
        }

        $prefijo = strtoupper(substr($categoria->nombre, 0, 3)); // Prefijo basado en el nombre de la categoría

        // Obtener el SKU más alto de la categoría seleccionada
        $ultimoSku = $this->articuloService->generarSku($categoria_id, $prefijo);

        // Determinar el consecutivo
        $consecutivo = 1; // Si no hay artículos en esta categoría, el consecutivo empieza en 1
        if ($ultimoSku) {
            $consecutivo = (int) substr($ultimoSku, 3) + 1; // Extraer el consecutivo y sumarle 1
        }

        // Crear el nuevo SKU
        // Crear el nuevo SKU
        $nuevoSku = $prefijo . str_pad($consecutivo, 4, '0', STR_PAD_LEFT);

        return response()->json([
            'message' => 200,
            'message_text' => '',
            'sku' => $nuevoSku
        ]);
    }

    public function buscarArticulos(Request $request)
    {
        $data = $request->all();

        $articulos = $this->articuloService->getAllArticulos($data);

        if (!$articulos) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'articulos' => ArticuloCollection::make($articulos),
        ]);
    }  
}
