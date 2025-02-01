<?php

namespace App\Http\Controllers\Facturas;

use App\Exports\Factura\FacturaDetalleExport;
use App\Exports\Factura\FacturaGeneralExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Facturas\FacturaRequest;
use App\Http\Resources\Facturas\FacturaCollection;
use App\Http\Resources\Facturas\FacturaResource;
use App\Models\Facturas\Factura;
use App\Services\Facturas\FacturaService;
use App\Services\GeneralService;
use App\Services\UsuarioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class FacturaController extends Controller
{
    protected $facturaService;
    protected $generalService;
    protected $userService;

    public function __construct(FacturaService $facturaService, GeneralService $generalService, UsuarioService $userService)
    {
        $this->facturaService = $facturaService;
        $this->generalService = $generalService;
        $this->userService = $userService;
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

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = Storage::putFile('facturas', $request->file('imagen'));
            $validated['imagen'] = $imagenPath;
        } else {
            $validated['imagen'] = null;
        }

        try {
            $factura = $this->facturaService->store($validated);

            // Verificar si el servicio retornó un error
            if (isset($factura['error']) && $factura['error']) {
                // Si hay un error, elimina la imagen subida
                if ($imagenPath) {
                    Storage::delete($imagenPath);
                }

                return response()->json([
                    'message' => $factura['code'],
                    'message_text' => $factura['message'],
                ]);
            }

            // Respuesta exitosa
            return response()->json([
                'message' => 200,
                'message_text' => 'La factura se registró de manera exitosa.',
                'factura' => FacturaResource::make($factura),
            ]);
        } catch (\Exception $e) {
            // Si hay un error, elimina la imagen subida
            if ($imagenPath) {
                Storage::delete($imagenPath);
            }
            // Manejo de excepciones
            Log::error('Error al crear la factura: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

            // Retorna un error genérico
            return response()->json([
                'message' => 500,
                'message_text' => 'Ocurrió un error inesperado durante la creación de la factura.',
            ], 500);
        }
    }

    public function update(FacturaRequest $request, string $id)
    {
        $this->authorize('update', Factura::class);

        $validated = $request->validated();

        $imagenPath = null;
        if ($request->hasFile('imagen')) {
            $imagenPath = Storage::putFile('facturas', $request->file('imagen'));
            $validated['imagen'] = $imagenPath;
        } else {
            $validated['imagen'] = null;
        }

        try {
            $factura = $this->facturaService->update($validated, $id);

            // Verificar si el servicio retornó un error
            if (isset($factura['error']) && $factura['error']) {
                // Si hay un error, elimina la imagen subida
                if ($imagenPath) {
                    Storage::delete($imagenPath);
                }

                return response()->json([
                    'message' => $factura['code'],
                    'message_text' => $factura['message'],
                ]);
            }

            return response()->json([
                'message' => 200,
                'message_text' => 'La factura se editó de manera exitosa',
                'factura' => FacturaResource::make($factura)
            ]);
        } catch (\Exception $e) {
            // Si hay un error, elimina la imagen subida
            if ($imagenPath) {
                Storage::delete($imagenPath);
            }

            Log::error('Error al actualizar la factura: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

            return response()->json([
                'message' => 500,
                'message_text' => 'Ocurrió un error inesperado durante la actualización de la factura.',
            ], 500);
        }
    }

    public function cambiarEstado(Request $request, $id)
    {
        $this->authorize('delete', Factura::class);
       
        try {
            $factura = $this->facturaService->cambiarEstado($request, $id);

            // Verificar si el servicio retornó un error
            if (isset($factura['error']) && $factura['error']) {
                return response()->json([
                    'message' => $factura['code'],
                    'message_text' => $factura['message'],
                ]);
            }

            $factura = $this->facturaService->getById($id);

            return response()->json([
                'message' => 200,
                'message_text' => 'La factura y sus detalles se han eliminado correctamente.',
                'factura' => FacturaResource::make($factura)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar la factura: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

            return response()->json([
                'message' => 500,
                'message_text' => 'Ocurrió un error inesperado durante la eliminación de la factura.',
            ], 500);
        }
    }

    public function export_factura(Request $request)
    {
        $data = $request->all();

        $facturas = $this->facturaService->getAllFacturas($data);

        return Excel::download(new FacturaGeneralExport($facturas), 'Facturas-' . uniqid() . '.xlsx');
    }

    public function export_factura2(Request $request)
    {
        $data = $request->all();

        $facturas = $this->facturaService->getAllFacturas2($data);

        return Excel::download(new FacturaGeneralExport($facturas), 'Facturas-' . uniqid() . '.xlsx');
    }

    public function export_detalle_factura(Request $request)
    {
        $data = $request->all();

        $detalles = $this->facturaService->getAllDetallesFacturas($data);        
        return Excel::download(new FacturaDetalleExport($detalles), 'Detalle_facturas-' . uniqid() . '.xlsx');
    }

    public function imprimir(Request $request)
    {
        $factura = $this->facturaService->getById($request->id);
        $empresa = $this->generalService->getEmpresa($request->empresa_id);
        $usuario = $this->userService->getUserById($request->user_id);

        // $totalValorUnidad = $factura->detalles_facturas->sum(function ($item) {
        //     return $item->precio_item * $item->cantidad_item;
        // });

        // $subtotal = $factura->detalles_facturas->sum('sub_total');
        // $descuento = $factura->detalles_facturas->sum('total_descuento');
        // $iva = $factura->detalles_facturas->sum('total_iva');
        $subtotal = $factura->sub_total;
        $iva = $factura->total_iva;
        $descuento = $factura->total_descuento;
        $total = $subtotal - $descuento + $iva;

        $totalValorTotal = $factura->detalles_facturas->sum(function ($item) {
            return $item->sub_total - $item->total_descuento + $item->total_iva;
        });
        return view('factura.factura', compact('factura', 'empresa', 'usuario', 'subtotal', 'descuento', 'iva', 'total'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $factura = $this->facturaService->getById($id);

        if (!$factura) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        foreach ($factura->detalles_facturas as $detalle) {
            if ($detalle->articulo) {
                // Agregar `unidades` al artículo
                if ($detalle->articulo->articulos_wallets) {
                    // Inicializa la propiedad `unidades` si no existe
                    if (!isset($detalle->articulo->unidades)) {
                        $detalle->articulo->unidades = collect([]);
                    }

                    // Agrupar las unidades por `unidad_id` y agregarlas
                    foreach ($detalle->articulo->articulos_wallets->groupBy('unidad_id') as $grupo) {
                        $detalle->articulo->unidades->push($grupo[0]->unidad);
                    }

                    // Elimina duplicados en las unidades
                    $detalle->articulo->unidades = $detalle->articulo->unidades->unique('id')->values();
                }

                // Agregar `bodegas_articulos` al artículo
                if ($detalle->articulo->bodegas_articulos) {
                    // Inicializa la propiedad `bodegas_articulos` si no existe
                    if (!isset($detalle->articulo->bodegas_articulos)) {
                        $detalle->articulo->bodegas_articulos = collect([]);
                    }

                    // Procesar los `bodegas_articulos` y asignarlos
                    $detalle->articulo->bodegas_articulos = $detalle->articulo->bodegas_articulos->map(function ($bodega) {
                        return [
                            "id" => $bodega->id,
                            "unidad" => $bodega->unidad,
                            "bodega" => $bodega->bodega,
                            "cantidad" => $bodega->cantidad,
                        ];
                    });
                }
            }
        }

        return response()->json([
            'message' => 200,
            'message_text' => '',
            'factura' => FacturaResource::make($factura)
        ]);
    }

    public function eliminarDetalle(Request $request)
    {
        $factura = $this->facturaService->deleteDetalle($request->id);

        if (!$factura) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        return response()->json([
            'message' => 200,
            'message_text' => ''
        ]);
    }
}
