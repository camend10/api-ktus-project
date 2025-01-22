<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Services\Kpi\KpiService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KpiController extends Controller
{

    protected $kpiService;

    public function __construct(KpiService $kpiService)
    {
        $this->kpiService = $kpiService;
    }

    public function informacion_general(Request $request)
    {

        $data = $request->all();

        $total_compra = $this->kpiService->getTotalCompras($data);
        $total_clientes = $this->kpiService->getTotalClientes($data);
        $total_ventas = $this->kpiService->getTotalVentas($data);

        $sede_mas_venta = $this->kpiService->getSedeMasVenta($data);

        return response()->json([
            'total_compra' => round($total_compra, 2),
            'total_clientes' => $total_clientes,
            'total_ventas' => round($total_ventas, 2),
            'sede_mas_venta' => $sede_mas_venta->first(),
        ]);
    }

    public function venta_x_sede(Request $request)
    {
        $data = $request->all();

        $venta_sedes = $this->kpiService->getVentaSedes($data);
        $total_venta_sedes =   $venta_sedes->sum("total_venta");

        $fecha_pasada = Carbon::parse($data["year"] . "-" . str_pad($data["month"], 2, "0", STR_PAD_LEFT) . "-01")
            ->subMonth();

        $total_venta_sedes_fecha_pasada = $this->kpiService->getVentaSedesFechaPasada($fecha_pasada);
        $porcentajeV = 0;

        if ($total_venta_sedes > 0) {
            $porcentajeV = round((($total_venta_sedes - $total_venta_sedes_fecha_pasada) / $total_venta_sedes) * 100, 2);
        }

        return response()->json([
            'venta_sedes' => $venta_sedes,
            'total_venta_sedes' => $total_venta_sedes,
            'total_venta_sedes_fecha_pasada' => round($total_venta_sedes_fecha_pasada, 2),
            'porcentajeV' => $porcentajeV,
        ]);
    }

    public function venta_x_dia_del_mes(Request $request)
    {
        $data = $request->all();

        $venta_dia_del_mes = $this->kpiService->getVentaDiaMes($data);

        $total_venta_actual = round($venta_dia_del_mes->sum("total_venta"), 2);

        $fecha_pasada = Carbon::parse($data["year"] . "-" . str_pad($data["month"], 2, "0", STR_PAD_LEFT) . "-01")
            ->subMonth();

        $total_venta_pasada = $this->kpiService->getVentaPasada($fecha_pasada, $data["sede_id"]);

        $porcentajeV = 0;

        if ($total_venta_actual > 0) {
            $porcentajeV = round((($total_venta_actual - $total_venta_pasada) / $total_venta_actual) * 100, 2);
        }

        return response()->json([
            'venta_dia_del_mes' => $venta_dia_del_mes,
            'total_venta_actual' => $total_venta_actual,
            'total_venta_pasada' => $total_venta_pasada,
            'porcentajeV' => $porcentajeV,
        ]);
    }

    public function venta_x_mes_del_year(Request $request)
    {
        $data = $request->all();

        $venta_x_mes_del_year_actual = $this->kpiService->getVentaMesYearActual($data);

        $venta_x_mes_del_year_anterior = $this->kpiService->getVentaMesYearAnterior($data);

        // Crear un arreglo base con 12 meses inicializados en 0
        $meses_actual = array_fill(0, 12, 0);
        $meses_anterior = array_fill(0, 12, 0);

        // Asignar datos del año actual a los meses correspondientes
        foreach ($venta_x_mes_del_year_actual as $venta) {
            $year_actual = substr($venta['created_at_format'], 0, 4); // Obtener el año
            $mes = intval(substr($venta['created_at_format'], 5, 2)) - 1; // Mes en base 0
            if ((int)$data['year'] === (int)$year_actual) {
                $meses_actual[$mes] = $venta['total_venta'];
            }
        }

        // Asignar datos del año anterior a los meses correspondientes
        foreach ($venta_x_mes_del_year_anterior as $venta) {
            $year_anterior = substr($venta['created_at_format'], 0, 4); // Obtener el año
            $mes = intval(substr($venta['created_at_format'], 5, 2)) - 1; // Mes en base 0
            if ((int)$data['year'] - 1 === (int)$year_anterior) {
                $meses_anterior[$mes] = $venta['total_venta'];
            }
        }
        return response()->json([
            'venta_x_mes_del_year_actual' => $venta_x_mes_del_year_actual,
            'total_venta_year_actual' => round($venta_x_mes_del_year_actual->sum("total_venta"), 2),
            'venta_x_mes_del_year_anterior' => $venta_x_mes_del_year_anterior,
            'total_venta_year_anterior' => round($venta_x_mes_del_year_anterior->sum("total_venta"), 2),
            'meses_nombre' => array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"),
        ]);
    }

    public function venta_x_segmento(Request $request)
    {
        $data = $request->all();
        $venta_x_segmento_clientes = $this->kpiService->getVentaPorSegmento($data);

        return response()->json([
            'total_venta_segmento' => $venta_x_segmento_clientes->sum("total_venta"),
            'venta_x_segmento_clientes' => $venta_x_segmento_clientes,
        ]);
    }

    public function vendedor_mas_venta(Request $request)
    {
        $vendedor_mas_venta = $this->kpiService->getVendedorMasVenta($request);

        $fecha_pasada = Carbon::parse(date("Y") . "-" . str_pad(date("m"), 2, "0", STR_PAD_LEFT) . "-01")
            ->subMonth();

        $vendedor_total_venta_mes_anterior = $this->kpiService->getVendedorVentaMesAnterior($fecha_pasada, $vendedor_mas_venta->vendedor_id ?? null);

        $vendedor_total_venta_mes_actual = $vendedor_mas_venta->total_venta ?? 0;

        $porcentajeV = 0;

        if ($vendedor_total_venta_mes_actual > 0) {
            $porcentajeV = round((($vendedor_total_venta_mes_actual - $vendedor_total_venta_mes_anterior) / $vendedor_total_venta_mes_actual) * 100, 2);
        }

        $inicio_semana = Carbon::now()->startOfWeek();
        $fin_semana = Carbon::now()->endOfWeek();

        $vendedor_venta_semana = $this->kpiService->getVendedorVentaSemana($inicio_semana, $fin_semana, $vendedor_mas_venta->vendedor_id ?? null);

        Carbon::setLocale('es');

        return response()->json([
            'porcentajeV' => $porcentajeV,
            'inicio_semana' => $inicio_semana,
            'fin_semana' => $fin_semana,
            'vendedor_mas_venta' => $vendedor_mas_venta,
            'vendedor_total_venta_mes_anterior' => $vendedor_total_venta_mes_anterior,
            'vendedor_total_venta_mes_actual' => $vendedor_total_venta_mes_actual,
            'vendedor_venta_semana' => $vendedor_venta_semana,
            'nombre_mes' => Carbon::parse($request["year"] . '-' . $request["month"] . '-01')->monthName,
            'imagen' =>  url('storage/users/blank.png'),

        ]);
    }

    public function categorias_mas_ventas(Request $request)
    {
        $data = $request->all();
        $categorias_mas_ventas = $this->kpiService->getCategoriasMasVentas($data);

        $categorias_mas_ventas->map(function ($categoria) {
            $categoria->imagen = $categoria->imagen != 'SIN-IMAGEN'
                ? env("APP_URL") . "storage/" . $categoria->imagen
                : env("APP_URL") . "storage/articulos/blank-image.svg";
            return $categoria;
        });

        $categorias =  $categorias_mas_ventas->take(4);
        $categorias_articulos = collect([]);

        foreach ($categorias as $key => $item) {
            $articulos = $this->kpiService->getArticulosMasVentas($data, $item->categoria_id);
            $categorias_articulos->push([
                "id" => $item->categoria_id,
                "nombre" => $item->categoria,
                "imagen" => $item->imagen,
                "articulos" => $articulos->map(function ($articulo) {
                    $articulo->imagen = isset($articulo->imagen) && $articulo->imagen != 'SIN-IMAGEN'
                        ? env("APP_URL") . "storage/" . $articulo->imagen
                        : env("APP_URL") . "storage/articulos/blank-image.svg"; // Imagen predeterminada
                    return $articulo;
                }),
            ]);
        }

        return response()->json([
            'categorias_mas_ventas' => $categorias_mas_ventas,
            'categorias_articulos' => $categorias_articulos,
        ]);
    }

    public function fecha_actual()
    {
        return response()->json([
            'year' => date("Y"),
            'month' => date("m"),
        ]);
    }
}
