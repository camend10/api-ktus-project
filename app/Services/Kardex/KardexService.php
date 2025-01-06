<?php

namespace App\Services\Kardex;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class KardexService
{

    public function getSolicitudes($data, $opcion)
    {

        $bodega_id = $data["bodega_id"];
        $year = $data["year"];
        $month = $data["month"];
        $articulo = $data["articulo"];

        if ($opcion == 2) {
            // Si $opcion es 2, tomamos estos valores directamente de $data
            $empresa_id = $data["empresa_id"];
            $sede_id = $data["sede_id"];
            $role_id = $data["role_id"];
        } else {
            // Si $opcion no es 2, usamos el usuario autenticado
            $user = auth('api')->user();

            if (!$user) {
                return false;
            }

            $empresa_id = $user->empresa_id;
            $sede_id = $user->sede_id;
            $role_id = $user->role_id;
        }

        // ->whereYear("detalle_movimientos.fecha_entrega", "<>", NULL)
        // Subconsulta para manejar expresiones calculadas
        $subQuery = DB::table('detalle_movimientos')
            ->join('movimientos', 'movimientos.id', '=', 'detalle_movimientos.movimiento_id')
            ->join('articulos', 'articulos.id', '=', 'detalle_movimientos.articulo_id')
            ->join('categorias', 'articulos.categoria_id', '=', 'categorias.id')
            ->where('detalle_movimientos.estado', '<>', 0)
            ->whereYear('detalle_movimientos.fecha_entrega', $year)
            ->whereMonth('detalle_movimientos.fecha_entrega', $month)
            ->where('movimientos.bodega_id', $bodega_id)
            ->where('movimientos.empresa_id', $empresa_id)
            ->when($articulo, function ($query) use ($articulo) {
                // Filtrar por coincidencia en el nombre del artículo si está definido
                $query->where('articulos.nombre', 'LIKE', "%{$articulo}%");
            })            
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('movimientos.sede_id', $sede_id);
            })
            // ->where('movimientos.empresa_id', $user->empresa_id)
            // ->when(!in_array($user->role_id, [1, 2]), function ($query) use ($user) {
            //     $query->where('movimientos.sede_id', $user->sede_id);
            // })
            ->where('movimientos.destino', 'Solicitud')
            ->where('movimientos.tipo_movimiento', 1)
            ->where('movimientos.estado', 4)
            ->selectRaw(
                "
                        DATEDIFF(SECOND, '1970-01-01 00:00:00', detalle_movimientos.fecha_entrega) as fecha_entrega_num,
                        FORMAT(detalle_movimientos.fecha_entrega, 'dd MMMM yyyy') as fecha_entrega_format,
                        detalle_movimientos.articulo_id,
                        detalle_movimientos.unidad_id,
                        movimientos.empresa_id,
                        movimientos.sede_id,
                        articulos.nombre as nombre_articulo,
                        articulos.sku as sku,
                        categorias.nombre as categoria,
                        detalle_movimientos.cantidad,
                        detalle_movimientos.costo
                    "
            );

        // Consulta principal usando la subconsulta
        return DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->selectRaw("
                        fecha_entrega_num,
                        fecha_entrega_format,
                        articulo_id,
                        unidad_id,
                        empresa_id,
                        sede_id,
                        nombre_articulo,
                        sku,
                        categoria,
                        1 as tipo,
                        'Solicitud' as tipo_movimiento,
                        SUM(cantidad) as cantidad,
                        AVG(costo) as costo_avg
                    ")
            ->groupBy(
                'fecha_entrega_num',
                'fecha_entrega_format',
                'articulo_id',
                'unidad_id',
                'empresa_id',
                'sede_id',
                'nombre_articulo',
                'sku',
                'categoria'
                // 'tipo_movimiento'
            )
            ->get();
    }

    public function getEntradas($data, $opcion)
    {
        $bodega_id = $data["bodega_id"];
        $year = $data["year"];
        $month = $data["month"];
        $articulo = $data["articulo"];

        if ($opcion == 2) {
            // Si $opcion es 2, tomamos estos valores directamente de $data
            $empresa_id = $data["empresa_id"];
            $sede_id = $data["sede_id"];
            $role_id = $data["role_id"];
        } else {
            // Si $opcion no es 2, usamos el usuario autenticado
            $user = auth('api')->user();

            if (!$user) {
                return false;
            }

            $empresa_id = $user->empresa_id;
            $sede_id = $user->sede_id;
            $role_id = $user->role_id;
        }

        // Subconsulta para manejar expresiones calculadas
        $subQuery = DB::table('detalle_movimientos')
            ->join('movimientos', 'movimientos.id', '=', 'detalle_movimientos.movimiento_id')
            ->join('articulos', 'articulos.id', '=', 'detalle_movimientos.articulo_id')
            ->join('categorias', 'articulos.categoria_id', '=', 'categorias.id')
            ->where('detalle_movimientos.estado', '<>', 0)
            ->whereYear('detalle_movimientos.fecha_entrega', $year)
            ->whereMonth('detalle_movimientos.fecha_entrega', $month)
            ->where('movimientos.bodega_id', $bodega_id)            
            ->where('movimientos.empresa_id', $empresa_id)
            ->when($articulo, function ($query) use ($articulo) {
                // Filtrar por coincidencia en el nombre del artículo si está definido
                $query->where('articulos.nombre', 'LIKE', "%{$articulo}%");
            })            
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('movimientos.sede_id', $sede_id);
            })
            ->where('movimientos.destino', 'Movimiento')
            ->where('movimientos.tipo_movimiento', 1)
            ->where('movimientos.estado', 4)
            ->selectRaw("
                DATEDIFF(SECOND, '1970-01-01 00:00:00', detalle_movimientos.fecha_entrega) as fecha_entrega_num,
                FORMAT(detalle_movimientos.fecha_entrega, 'dd MMMM yyyy') as fecha_entrega_format,
                detalle_movimientos.articulo_id,
                detalle_movimientos.unidad_id,
                movimientos.empresa_id,
                movimientos.sede_id,
                articulos.nombre as nombre_articulo,
                articulos.sku as sku,
                categorias.nombre as categoria,
                detalle_movimientos.cantidad,
                detalle_movimientos.costo
            ");

        // Consulta principal usando la subconsulta
        return DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->selectRaw("
                    fecha_entrega_num,
                    fecha_entrega_format,
                    articulo_id,
                    unidad_id,
                    empresa_id,
                    sede_id,
                    nombre_articulo,
                    sku,
                    categoria,
                    1 as tipo,
                    'Entrada' as tipo_movimiento,
                    SUM(cantidad) as cantidad,
                    AVG(costo) as costo_avg
                ")
            ->groupBy(
                'fecha_entrega_num',
                'fecha_entrega_format',
                'articulo_id',
                'unidad_id',
                'empresa_id',
                'sede_id',
                'nombre_articulo',
                'sku',
                'categoria'
            )
            ->get();
    }

    public function getConversionesEntradas($data, $opcion)
    {
        $bodega_id = $data["bodega_id"];
        $year = $data["year"];
        $month = $data["month"];
        $articulo = $data["articulo"];

        if ($opcion == 2) {
            // Si $opcion es 2, tomamos estos valores directamente de $data
            $empresa_id = $data["empresa_id"];
            $sede_id = $data["sede_id"];
            $role_id = $data["role_id"];
        } else {
            // Si $opcion no es 2, usamos el usuario autenticado
            $user = auth('api')->user();

            if (!$user) {
                return false;
            }

            $empresa_id = $user->empresa_id;
            $sede_id = $user->sede_id;
            $role_id = $user->role_id;
        }

        // Subconsulta para manejar expresiones calculadas
        $subQuery = DB::table('conversiones')
            ->join('articulos', 'articulos.id', '=', 'conversiones.articulo_id')
            ->join('categorias', 'articulos.categoria_id', '=', 'categorias.id')
            ->where('conversiones.estado', '<>', 0)
            ->whereYear('conversiones.created_at', $year)
            ->whereMonth('conversiones.created_at', $month)
            ->where('conversiones.bodega_id', $bodega_id)
            ->where('conversiones.empresa_id', $empresa_id)
            ->when($articulo, function ($query) use ($articulo) {
                // Filtrar por coincidencia en el nombre del artículo si está definido
                $query->where('articulos.nombre', 'LIKE', "%{$articulo}%");
            })            
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('conversiones.sede_id', $sede_id);
            })
            ->selectRaw("
                DATEDIFF(SECOND, '1970-01-01 00:00:00', conversiones.created_at) as fecha_entrega_num,
                FORMAT(conversiones.created_at, 'dd MMMM yyyy') as fecha_entrega_format,
                conversiones.articulo_id,
                conversiones.unidad_final_id as unidad_id,
                conversiones.empresa_id,
                conversiones.sede_id,
                articulos.nombre as nombre_articulo,
                articulos.sku as sku,
                categorias.nombre as categoria,
                conversiones.cantidad_convertida
            ");

        // Consulta principal usando la subconsulta
        return DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->selectRaw("
                fecha_entrega_num,
                fecha_entrega_format,
                articulo_id,
                unidad_id,
                empresa_id,
                sede_id,
                nombre_articulo,
                sku,
                categoria,
                1 as tipo,
                'Conversión' as tipo_movimiento,
                SUM(cantidad_convertida) as cantidad,
                0 as costo_avg
            ")
            ->groupBy(
                'fecha_entrega_num',
                'fecha_entrega_format',
                'articulo_id',
                'unidad_id',
                'empresa_id',
                'sede_id',
                'nombre_articulo',
                'sku',
                'categoria'
            )
            ->get();
    }

    public function getSalidas($data, $opcion)
    {
        $bodega_id = $data["bodega_id"];
        $year = $data["year"];
        $month = $data["month"];
        $articulo = $data["articulo"];

        if ($opcion == 2) {
            // Si $opcion es 2, tomamos estos valores directamente de $data
            $empresa_id = $data["empresa_id"];
            $sede_id = $data["sede_id"];
            $role_id = $data["role_id"];
        } else {
            // Si $opcion no es 2, usamos el usuario autenticado
            $user = auth('api')->user();

            if (!$user) {
                return false;
            }

            $empresa_id = $user->empresa_id;
            $sede_id = $user->sede_id;
            $role_id = $user->role_id;
        }

        // Subconsulta para manejar expresiones calculadas
        $subQuery = DB::table('detalle_movimientos')
            ->join('movimientos', 'movimientos.id', '=', 'detalle_movimientos.movimiento_id')
            ->join('articulos', 'articulos.id', '=', 'detalle_movimientos.articulo_id')
            ->join('categorias', 'articulos.categoria_id', '=', 'categorias.id')
            ->where('detalle_movimientos.estado', '<>', 0)
            ->whereYear('detalle_movimientos.fecha_entrega', $year)
            ->whereMonth('detalle_movimientos.fecha_entrega', $month)
            ->where('movimientos.bodega_id', $bodega_id)
            ->where('movimientos.empresa_id', $empresa_id)
            ->when($articulo, function ($query) use ($articulo) {
                // Filtrar por coincidencia en el nombre del artículo si está definido
                $query->where('articulos.nombre', 'LIKE', "%{$articulo}%");
            })            
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('movimientos.sede_id', $sede_id);
            })
            ->where('movimientos.destino', 'Movimiento')
            ->where('movimientos.tipo_movimiento', 2)
            ->where('movimientos.estado', 4)
            ->selectRaw("
                DATEDIFF(SECOND, '1970-01-01 00:00:00', detalle_movimientos.fecha_entrega) as fecha_entrega_num,
                FORMAT(detalle_movimientos.fecha_entrega, 'dd MMMM yyyy') as fecha_entrega_format,
                detalle_movimientos.articulo_id,
                detalle_movimientos.unidad_id,
                movimientos.empresa_id,
                movimientos.sede_id,
                articulos.nombre as nombre_articulo,
                articulos.sku as sku,
                categorias.nombre as categoria,
                detalle_movimientos.cantidad,
                detalle_movimientos.costo
            ");

        // Consulta principal usando la subconsulta
        return DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->selectRaw("
                fecha_entrega_num,
                fecha_entrega_format,
                articulo_id,
                unidad_id,
                empresa_id,
                sede_id,
                nombre_articulo,
                sku,
                categoria,
                2 as tipo,
                'Salida' as tipo_movimiento,
                SUM(cantidad) as cantidad,
                AVG(costo) as costo_avg
            ")
            ->groupBy(
                'fecha_entrega_num',
                'fecha_entrega_format',
                'articulo_id',
                'unidad_id',
                'empresa_id',
                'sede_id',
                'nombre_articulo',
                'sku',
                'categoria'
            )
            ->get();
    }

    public function getFacturas($data, $opcion)
    {
        $bodega_id = $data["bodega_id"];
        $year = $data["year"];
        $month = $data["month"];
        $articulo = $data["articulo"];

        if ($opcion == 2) {
            // Si $opcion es 2, tomamos estos valores directamente de $data
            $empresa_id = $data["empresa_id"];
            $sede_id = $data["sede_id"];
            $role_id = $data["role_id"];
        } else {
            // Si $opcion no es 2, usamos el usuario autenticado
            $user = auth('api')->user();

            if (!$user) {
                return false;
            }

            $empresa_id = $user->empresa_id;
            $sede_id = $user->sede_id;
            $role_id = $user->role_id;
        }

        // Subconsulta para manejar expresiones calculadas
        $subQuery = DB::table('detalle_facturas')
            ->join('facturas', 'facturas.id', '=', 'detalle_facturas.factura_id')
            ->join('articulos', 'articulos.id', '=', 'detalle_facturas.articulo_id')
            ->join('categorias', 'articulos.categoria_id', '=', 'categorias.id')
            ->where('detalle_facturas.estado', '<>', 0)
            ->whereYear('facturas.created_at', $year)
            ->whereMonth('facturas.created_at', $month)
            ->where('detalle_facturas.bodega_id', $bodega_id)
            ->where('facturas.empresa_id', $empresa_id)
            ->when($articulo, function ($query) use ($articulo) {
                // Filtrar por coincidencia en el nombre del artículo si está definido
                $query->where('articulos.nombre', 'LIKE', "%{$articulo}%");
            })            
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('facturas.sede_id', $sede_id);
            })            
            ->where('facturas.estado', 1)
            ->selectRaw("
                DATEDIFF(SECOND, '1970-01-01 00:00:00', facturas.created_at) as fecha_entrega_num,
                FORMAT(facturas.created_at, 'dd MMMM yyyy') as fecha_entrega_format,
                detalle_facturas.articulo_id,
                detalle_facturas.unidad_id,
                facturas.empresa_id,
                facturas.sede_id,
                articulos.nombre as nombre_articulo,
                articulos.sku as sku,
                categorias.nombre as categoria,
                detalle_facturas.cantidad_item,
                detalle_facturas.precio_item
            ");

        // Consulta principal usando la subconsulta
        return DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->selectRaw("
                fecha_entrega_num,
                fecha_entrega_format,
                articulo_id,
                unidad_id,
                empresa_id,
                sede_id,
                nombre_articulo,
                sku,
                categoria,
                2 as tipo,
                'Factura' as tipo_movimiento,
                SUM(cantidad_item) as cantidad,
                AVG(precio_item) as costo_avg
            ")
            ->groupBy(
                'fecha_entrega_num',
                'fecha_entrega_format',
                'articulo_id',
                'unidad_id',
                'empresa_id',
                'sede_id',
                'nombre_articulo',
                'sku',
                'categoria'
            )
            ->get();
    }

    public function getConversionesSalidas($data, $opcion)
    {
        $bodega_id = $data["bodega_id"];
        $year = $data["year"];
        $month = $data["month"];
        $articulo = $data["articulo"];

        if ($opcion == 2) {
            // Si $opcion es 2, tomamos estos valores directamente de $data
            $empresa_id = $data["empresa_id"];
            $sede_id = $data["sede_id"];
            $role_id = $data["role_id"];
        } else {
            // Si $opcion no es 2, usamos el usuario autenticado
            $user = auth('api')->user();

            if (!$user) {
                return false;
            }

            $empresa_id = $user->empresa_id;
            $sede_id = $user->sede_id;
            $role_id = $user->role_id;
        }

        // Subconsulta para manejar expresiones calculadas
        $subQuery = DB::table('conversiones')
            ->join('articulos', 'articulos.id', '=', 'conversiones.articulo_id')
            ->join('categorias', 'articulos.categoria_id', '=', 'categorias.id')
            ->where('conversiones.estado', '<>', 0)
            ->whereYear('conversiones.created_at', $year)
            ->whereMonth('conversiones.created_at', $month)
            ->where('conversiones.bodega_id', $bodega_id)
            ->where('conversiones.empresa_id', $empresa_id)
            ->when($articulo, function ($query) use ($articulo) {
                // Filtrar por coincidencia en el nombre del artículo si está definido
                $query->where('articulos.nombre', 'LIKE', "%{$articulo}%");
            })            
            ->when(!in_array($role_id, [1, 2]), function ($query) use ($sede_id) {
                // Si el rol no está en [1, 2], filtra por sede_id
                $query->where('conversiones.sede_id', $sede_id);
            })            
            ->selectRaw("
                DATEDIFF(SECOND, '1970-01-01 00:00:00', conversiones.created_at) as fecha_entrega_num,
                FORMAT(conversiones.created_at, 'dd MMMM yyyy') as fecha_entrega_format,
                conversiones.articulo_id,
                conversiones.unidad_inicio_id as unidad_id,
                conversiones.empresa_id,
                conversiones.sede_id,
                articulos.nombre as nombre_articulo,
                articulos.sku as sku,
                categorias.nombre as categoria,
                conversiones.cantidad_final
            ");

        // Consulta principal usando la subconsulta
        return DB::table(DB::raw("({$subQuery->toSql()}) as sub"))
            ->mergeBindings($subQuery)
            ->selectRaw("
                fecha_entrega_num,
                fecha_entrega_format,
                articulo_id,
                unidad_id,
                empresa_id,
                sede_id,
                nombre_articulo,
                sku,
                categoria,
                2 as tipo,
                'Conversión' as tipo_movimiento,
                SUM(cantidad_final) as cantidad,
                0 as costo_avg
            ")
            ->groupBy(
                'fecha_entrega_num',
                'fecha_entrega_format',
                'articulo_id',
                'unidad_id',
                'empresa_id',
                'sede_id',
                'nombre_articulo',
                'sku',
                'categoria'
            )
            ->get();
    }
}
