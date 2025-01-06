<?php

namespace App\Http\Controllers\Kardex;

use App\Exports\Kardex\DownloadKardex;
use App\Http\Controllers\Controller;
use App\Models\Articulos\Articulo;
use App\Models\Configuracion\Unidad;
use App\Models\Kardex\ArticuloStockInicial;
use App\Services\Kardex\KardexService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class KardexController extends Controller
{

    protected $kardexService;

    public function __construct(KardexService $kardexService)
    {
        $this->kardexService = $kardexService;
    }

    public function kardex_articulos($request, $bodega_id, $year, $month, $articulo, $opcion)
    {

        $movimientos_articulos = collect([]);
        // ENTRADAS

        $query_solicitudes = $this->kardexService->getSolicitudes($request, $opcion);
        foreach ($query_solicitudes as $item) {
            $movimientos_articulos->push($item);
        }

        $query_conversiones_entradas = $this->kardexService->getConversionesEntradas($request, $opcion);
        foreach ($query_conversiones_entradas as $item) {
            $movimientos_articulos->push($item);
        }

        $query_movimientos_entradas = $this->kardexService->getEntradas($request, $opcion);
        foreach ($query_movimientos_entradas as $item) {
            $movimientos_articulos->push($item);
        }

        // SALIDAS
        $query_movimientos_salidas = $this->kardexService->getSalidas($request, $opcion);
        foreach ($query_movimientos_salidas as $item) {
            $movimientos_articulos->push($item);
        }

        $query_facturas = $this->kardexService->getFacturas($request, $opcion);
        foreach ($query_facturas as $item) {
            $movimientos_articulos->push($item);
        }

        $query_conversiones_salidas = $this->kardexService->getConversionesSalidas($request, $opcion);
        foreach ($query_conversiones_salidas as $item) {
            $movimientos_articulos->push($item);
        }

        // CALCULOS Y OPERACIONES PARA LA COLUMNA EXISTENCIA
        // 1. -AGREGAR TODOS LOS REGISTROS EN UNA SOLA VARIABLE
        // 2. -AGRUPAR POR ARTICULOS
        // 2.1 - AGRUPAR LOS ARTICULOS SEGUN UNIDAD
        $kardex_articulos = collect([]);
        foreach ($movimientos_articulos->groupBy('articulo_id') as $key => $mov_articulo) {

            // MOVIMIENTOS DE LAS UNIDADES DE UN PRODUCTO
            $movimiento_unidades = collect([]);
            $unidades = collect([]);
            foreach ($mov_articulo->groupBy('unidad_id') as $key_unidad => $mov_unidad) {
                // LISTA DE MOVIMIENTOS DE UNA UNIDAD EN ESPECIFICO
                $movimientos = collect([]);

                $stock_inicial = ArticuloStockInicial::whereDate("created_at", "$year-$month-01")
                    ->where("articulo_id", $mov_unidad[0]->articulo_id)
                    ->where("unidad_id", $mov_unidad[0]->unidad_id)
                    ->where("bodega_id", $bodega_id)
                    ->where("empresa_id", $mov_unidad[0]->empresa_id)
                    ->first();

                $cantidad_anterior = $stock_inicial ? $stock_inicial->cantidad : 0;
                $precio_unitario_anterior = $stock_inicial ? $stock_inicial->precio_avg : 0;
                $total_anterior = round($cantidad_anterior * $precio_unitario_anterior, 2);

                // Configurar Carbon en español

                $movimientos->push([
                    "fecha" => Carbon::parse("$year-$month-01")->translatedFormat('d M Y'),
                    "detalle" => "STOCK INICIAL",
                    "ingreso" => NULL,
                    "salida" => NULL,
                    "existencia" => [
                        "cantidad" => $cantidad_anterior,
                        "precio" => $precio_unitario_anterior,
                        "total" => $total_anterior,
                    ],
                ]);

                foreach ($mov_unidad->sortBy("fecha_entrega_num") as $item) {
                    $cantidad_actual = $item->cantidad;
                    $cantidad_existencia = 0;
                    if ($item->tipo == 1) {
                        // ENTRADA
                        $cantidad_existencia = $cantidad_anterior + $cantidad_actual;
                    } else {
                        //SALIDA
                        $cantidad_existencia = $cantidad_anterior - $cantidad_actual;
                    }

                    $precio_actual = $item->costo_avg == 0 ? $precio_unitario_anterior : $item->costo_avg;
                    $total_actual = round($cantidad_actual * $precio_actual, 2);

                    $total_existencia = 0;
                    if ($item->tipo == 1) {
                        // ENTRADA
                        $total_existencia = $total_anterior + $total_actual;
                    } else {
                        //SALIDA
                        $total_existencia = $total_anterior - $total_actual;
                    }

                    $precio_existencia = round($total_existencia / $cantidad_existencia, 2);

                    $movimientos->push([
                        "fecha" => Carbon::parse($item->fecha_entrega_format)->translatedFormat('d M Y'),
                        "detalle" => $item->tipo_movimiento,
                        "ingreso" => $item->tipo == 1 ? [
                            "cantidad" => $cantidad_actual,
                            "precio" => $precio_actual,
                            "total" => $total_actual,
                        ] : NULL,
                        "salida" => $item->tipo == 2 ? [
                            "cantidad" => $cantidad_actual,
                            "precio" => $precio_actual,
                            "total" => $total_actual,
                        ] : NULL,
                        "existencia" => [
                            "cantidad" => $cantidad_existencia,
                            "precio" => $precio_existencia,
                            "total" => $total_existencia,
                        ],
                    ]);

                    $cantidad_anterior  = $cantidad_existencia;
                    $precio_unitario_anterior = $precio_existencia;
                    $total_anterior = $total_existencia;
                }

                $movimiento_unidades->push([
                    "unidad_id" => $key_unidad,
                    "movimientos" => $movimientos,
                ]);

                $unidades->push(Unidad::find($key_unidad));
            }

            $kardex_articulos->push([
                "articulo_id" => $mov_articulo[0]->articulo_id,
                "nombre" => $mov_articulo[0]->nombre_articulo,
                "sku" => $mov_articulo[0]->sku,
                "categoria" => $mov_articulo[0]->categoria,
                "movimiento_unidades" =>  $movimiento_unidades,
                "unidad_first" =>  $unidades->first(),
                "unidades" =>  $unidades,
            ]);

            return $kardex_articulos;
        }
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', ArticuloStockInicial::class);
        $bodega_id = $request->bodega_id;
        $year = $request->year;
        $month = $request->month;
        $articulo = $request->articulo;

        Carbon::setLocale('es');

        try {

            return response()->json([
                'kardex_articulos' => $this->kardex_articulos($request, $bodega_id, $year, $month, $articulo,1)
            ]);
        } catch (\Exception $e) {

            // Manejo de excepciones
            Log::error('Error al filtrar el kardex: ' . $e->getMessage(), [
                'stack' => $e->getTrace(),
            ]);

            // Retorna un error genérico
            return response()->json([
                'message' => 500,
                'message_text' => 'Ocurrió un error inesperado durante el filtrar del kardex.',
            ], 500);
        }
    }

    public function export_kardex(Request $request)
    {
        $bodega_id = $request->get("bodega_id");
        $year = $request->get("year");
        $month = $request->get("month");
        $articulo = $request->get("articulo");

        $kardex_articulos = $this->kardex_articulos($request, $bodega_id, $year, $month, $articulo,2);

        return Excel::download(new DownloadKardex($kardex_articulos), 'Reporte_Kardex.xlsx');
    }
}
