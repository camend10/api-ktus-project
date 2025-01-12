<?php

namespace App\Services\Kpi;

use App\Models\Clientes\Cliente;
use App\Models\Configuracion\Sede;
use App\Models\Facturas\Factura;
use App\Models\Movimientos\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public function getTotalCompras($data)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Movimiento::where('estado', '>', 2)
            ->where('empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total');
    }

    public function getTotalClientes($data)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Cliente::where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
    }

    public function getTotalVentas($data)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('total_venta');
    }

    public function getSedeMasVenta($data)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::with(['empresa:id,nombre', 'sede:id,nombre'])
            ->selectRaw('sede_id, empresa_id, COUNT(*) as total_facturas, SUM(total_venta) as total_venta')
            ->where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('sede_id', 'empresa_id')
            ->orderBy('total_venta', 'desc')
            ->take(1)
            ->get();
    }

    public function getVentaSedes($data)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Sede::selectRaw('
            sedes.id as sede_id,
            sedes.nombre as sede_nombre,
            empresa.id as empresa_id,
            empresa.nombre as empresa_nombre,
            COUNT(facturas.id) as total_facturas,
            COALESCE(SUM(facturas.total_venta), 0) as total_venta
        ')
            ->leftJoin('facturas', function ($join) use ($year, $month, $empresa_id) {
                $join->on('sedes.id', '=', 'facturas.sede_id')
                    ->whereYear('facturas.created_at', $year)
                    ->whereMonth('facturas.created_at', $month)
                    ->where('facturas.estado', 1)
                    ->where('facturas.empresa_id', $empresa_id);
            })
            ->join('empresa', 'sedes.empresa_id', '=', 'empresa.id')
            ->where('sedes.estado', 1)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('sedes.id', $sede_id);
            })
            ->where('sedes.empresa_id', $empresa_id)
            ->groupBy('sedes.id', 'sedes.nombre', 'empresa.id', 'empresa.nombre')
            ->orderBy('total_venta', 'desc')
            ->get();
    }

    public function getVentaSedesFechaPasada($data)
    {

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::with(['empresa:id,nombre', 'sede:id,nombre'])
            ->join('sedes', 'facturas.sede_id', '=', 'sedes.id')
            ->where('facturas.estado', 1)
            ->where('sedes.estado', 1)
            ->where('facturas.empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('facturas.sede_id', $sede_id);
            })
            ->whereYear('facturas.created_at', $data->format("Y"))
            ->whereMonth('facturas.created_at', $data->format("m"))
            ->sum("total_venta");
    }

    public function getVentaDiaMes($data)
    {

        $year = $data["year"];
        $month = $data["month"];
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? null : ($data['sede_id'] ?? null);
        $sede_id = $data["sede_id"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $role_id = $user->role_id;

        return Factura::selectRaw("FORMAT(created_at,'yyyy-MM-dd') as created_format,
                                   FORMAT(created_at,'MM-dd') as dia_created_format,
                                   ROUND(SUM(total_venta),2) as total_venta,
                                   COUNT(*) as total_facturas")
            ->where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(isset($data['sede_id']), function ($query) use ($sede_id) {
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupByRaw("FORMAT(created_at, 'yyyy-MM-dd'), FORMAT(created_at, 'MM-dd')")
            ->get();
    }

    public function getVentaPasada($data, $sede_id)
    {
        $sede_id = isset($sede_id) && $sede_id == 9999999 ? null : ($sede_id ?? null);

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $role_id = $user->role_id;

        return Factura::where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(isset($sede_id), function ($query) use ($sede_id) {
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $data->format("Y"))
            ->whereMonth('created_at', $data->format("m"))
            ->sum("total_venta");
    }

    public function getVentaMesYearActual($data)
    {
        $year = $data["year"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year)
            ->selectRaw("
                FORMAT(created_at,'yyyy-MM') as created_at_format,
                ROUND(SUM(total_venta),2) as total_venta,
                COUNT(*) as total_facturas
            ")
            ->groupByRaw("FORMAT(created_at, 'yyyy-MM')")
            ->get();
    }

    public function getVentaMesYearAnterior($data)
    {
        $year = $data["year"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year - 1)
            ->selectRaw("
                FORMAT(created_at,'yyyy-MM') as created_at_format,
                ROUND(SUM(total_venta),2) as total_venta,
                COUNT(*) as total_facturas
            ")
            ->groupByRaw("FORMAT(created_at, 'yyyy-MM')")
            ->get();
    }

    public function getVentaPorSegmento($data)
    {

        $year = $data["year"];
        $month = $data["month"];
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? null : ($data['sede_id'] ?? null);
        $sede_id = $data["sede_id"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $role_id = $user->role_id;

        return Factura::with(['segmento:id,nombre'])
            ->selectRaw("segmento_cliente_id,                                    
                                   ROUND(SUM(total_venta),2) as total_venta,
                                   COUNT(*) as total_facturas")
            ->where('estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(isset($data['sede_id']), function ($query) use ($sede_id) {
                $query->where('sede_id', $sede_id);
            })
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupByRaw("segmento_cliente_id")
            ->orderBy("total_venta", "desc")
            ->get()
            ->map(function ($factura) {
                // Agregar el nombre del segmento al resultado
                return [
                    'segmento_cliente_id' => $factura->segmento_cliente_id,
                    'nombre_segmento' => $factura->segmento->nombre ?? 'Sin segmento',
                    'total_venta' => $factura->total_venta,
                    'total_facturas' => $factura->total_facturas,
                ];
            });
    }

    public function getVendedorMasVenta($data)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::selectRaw("
                        user_id as vendedor_id,
                        COALESCE(users.name, 'Sin vendedor') as vendedor,
                        ROUND(SUM(total_venta),2) as total_venta,
                        COUNT(*) as total_facturas
                    ")
            ->leftJoin('users', 'facturas.user_id', '=', 'users.id')
            ->where('facturas.estado', 1)
            ->where('facturas.empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('facturas.sede_id', $sede_id);
            })
            ->whereYear('facturas.created_at', $year)
            ->whereMonth('facturas.created_at', $month)
            ->groupByRaw("facturas.user_id, users.name")
            ->orderBy("total_venta", "desc")
            ->take(1)
            ->get()
            ->first();
    }

    public function getVendedorVentaMesAnterior($data, $user_id)
    {

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::where('facturas.estado', 1)
            ->where('empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('sede_id', $sede_id);
            })
            ->where('facturas.user_id', $user_id)
            ->whereYear('facturas.created_at', $data->format("Y"))
            ->whereMonth('facturas.created_at', $data->format("m"))
            ->sum("total_venta");
    }

    public function getVendedorVentaSemana($inicio_semana, $fin_semana, $user_id)
    {

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::selectRaw("
                        FORMAT(created_at,'yyyy-MM-dd') as created_format,
                        FORMAT(created_at,'dd') as dia,
                        ROUND(SUM(total_venta),2) as total_venta,
                        COUNT(*) as total_facturas
                    ")
            ->where('facturas.estado', 1)
            ->where('facturas.user_id', $user_id)
            ->where('facturas.empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('facturas.sede_id', $sede_id);
            })
            ->whereBetween('facturas.created_at', [$inicio_semana->format('Y-m-d') . " 00:00:00", $fin_semana->format('Y-m-d') . " 23:59:59"])
            ->groupByRaw("FORMAT(created_at, 'yyyy-MM-dd'),FORMAT(created_at,'dd')")
            ->get();
    }

    public function getCategoriasMasVentas($data)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::selectRaw("
                    detalle_facturas.categoria_id as categoria_id,
                    COALESCE(categorias.nombre, 'Sin categoria') as categoria,
                    categorias.imagen as imagen,
                    ROUND(SUM(detalle_facturas.total_precio),2) as total_venta,
                    SUM(detalle_facturas.cantidad_item) as cantidad_articulos
                ")
            ->join('detalle_facturas', 'detalle_facturas.factura_id', '=', 'facturas.id')
            ->join('categorias', 'detalle_facturas.categoria_id', '=', 'categorias.id')
            ->where('facturas.estado', 1)
            ->where('detalle_facturas.estado', 2)
            ->where('facturas.empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('facturas.sede_id', $sede_id);
            })
            ->whereYear('facturas.created_at', $year)
            ->whereMonth('facturas.created_at', $month)
            ->groupByRaw("detalle_facturas.categoria_id, categorias.nombre,categorias.imagen")
            ->orderBy("total_venta", "desc")
            ->get();
    }

    public function getArticulosMasVentas($data, $categoria_id)
    {

        $year = $data["year"];
        $month = $data["month"];

        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        return Factura::selectRaw("
                    detalle_facturas.articulo_id as articulo_id,
                    COALESCE(articulos.nombre, 'Sin articulo') as articulo,
                    articulos.imagen as imagen,
                    articulos.sku as sku,
                    ROUND(AVG(detalle_facturas.precio_item),2) as sub_total_venta,
                    ROUND(SUM(detalle_facturas.total_precio),2) as total_venta,
                    SUM(detalle_facturas.cantidad_item) as cantidad_articulos
                ")
            ->join('detalle_facturas', 'detalle_facturas.factura_id', '=', 'facturas.id')
            ->join('articulos', 'detalle_facturas.articulo_id', '=', 'articulos.id')
            ->where('facturas.estado', 1)
            ->where('detalle_facturas.estado', 2)
            ->where('detalle_facturas.categoria_id', $categoria_id)
            ->where('facturas.empresa_id', $empresa_id)
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                $query->where('facturas.sede_id', $sede_id);
            })
            ->whereYear('facturas.created_at', $year)
            ->whereMonth('facturas.created_at', $month)
            ->groupByRaw("detalle_facturas.articulo_id, articulos.nombre,articulos.imagen,articulos.sku")
            ->orderBy("total_venta", "desc")
            ->take(5)
            ->get();
    }
}
