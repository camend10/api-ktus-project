<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\Schema;
use App\Models\Articulos\Articulo;
use App\Models\Articulos\ArticuloWallet;
use App\Models\Articulos\BodegaArticulo;
use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Categoria;
use App\Models\Configuracion\Proveedor;
use App\Models\Configuracion\Sede;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReporteService
{
    public function getBajaExistencia($data)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        // $articulosBajaExistencia = DB::table('articulos')
        //     ->join('bodegas_articulos', 'articulos.id', '=', 'bodegas_articulos.articulo_id')
        //     ->join('bodegas', 'bodegas.id', '=', 'bodegas_articulos.bodega_id') // Relación con bodegas
        //     ->leftJoin('unidades as unidad_articulo', 'articulos.punto_pedido_unidad_id', '=', 'unidad_articulo.id')
        //     ->leftJoin('unidades as unidad_bodega', 'bodegas_articulos.unidad_id', '=', 'unidad_bodega.id')
        //     ->select(
        //         'articulos.id as articulo_id',
        //         'articulos.nombre as articulo_nombre',
        //         'articulos.sku',
        //         'articulos.punto_pedido',
        //         'bodegas.sede_id', // Incluimos la sede
        //         'unidad_articulo.nombre as unidad_articulo',
        //         DB::raw('SUM(bodegas_articulos.cantidad) as total_existencia'),
        //         'unidad_bodega.nombre as unidad_bodega'
        //     )
        //     ->groupBy(
        //         'articulos.id',
        //         'articulos.nombre',
        //         'articulos.sku',
        //         'articulos.punto_pedido',
        //         'bodegas.sede_id',
        //         'unidad_articulo.nombre',
        //         'unidad_bodega.nombre'
        //     )
        //     ->havingRaw('SUM(bodegas_articulos.cantidad) < articulos.punto_pedido') // Baja existencia por sede
        //     ->where('articulos.empresa_id', $user->empresa_id) // Filtro por empresa
        //     ->where('bodegas.sede_id', $user->sede_id) // Filtro por sede específica
        //     ->orderBy('articulos.id', 'desc')
        //     ->paginate(20);

        // Normaliza los valores especiales
        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $user->sede_id : ($data['sede_id'] ?? null);
        $data['bodega_id'] = isset($data['bodega_id']) && $data['bodega_id'] == 9999999 ? null : ($data['bodega_id'] ?? null);
        $data['unidad_id_bodegas'] = isset($data['unidad_id_bodegas']) && $data['unidad_id_bodegas'] == 9999999 ? null : ($data['unidad_id_bodegas'] ?? null);
        $data['proveedor_id'] = isset($data['proveedor_id']) && $data['proveedor_id'] == 9999999 ? null : ($data['proveedor_id'] ?? null);
        $data['state_stock'] = isset($data['state_stock']) && $data['state_stock'] == 9999999 ? null : ($data['state_stock'] ?? null);

        // Obtén los nombres de todas las columnas de la tabla 'articulos'
        $articuloColumns = Schema::getColumnListing('articulos');

        // Agrega el prefijo 'articulos.' a cada columna para evitar ambigüedades
        $articuloColumns = array_map(fn($column) => "articulos.$column", $articuloColumns);

        // Agrega campos adicionales necesarios para el `GROUP BY`
        $groupByColumns = array_merge($articuloColumns, [
            'bodegas.sede_id',
            'unidad_punto_pedido.nombre',
            'unidad_bodega.nombre',
        ]);

        $articulos =  Articulo::with([
            'empresa',
            'categoria',
            'unidad_punto_pedido',
            'usuario',
            'proveedor',
            'bodegas_articulos',
        ])
            ->select(
                'articulos.*',
                'bodegas.sede_id', // Incluimos explícitamente la sede
                DB::raw('SUM(bodegas_articulos.cantidad) as total_existencia'),
                'unidad_punto_pedido.nombre as unidad_articulo',
                'unidad_bodega.nombre as unidad_bodega'
            )
            ->join('bodegas_articulos', 'articulos.id', '=', 'bodegas_articulos.articulo_id') // Unión con bodegas_articulos
            ->join('bodegas', 'bodegas.id', '=', 'bodegas_articulos.bodega_id') // Unión con bodegas
            ->leftJoin('unidades as unidad_punto_pedido', 'articulos.punto_pedido_unidad_id', '=', 'unidad_punto_pedido.id') // Unión con unidad_articulo
            ->leftJoin('unidades as unidad_bodega', 'bodegas_articulos.unidad_id', '=', 'unidad_bodega.id') // Unión con unidad_bodega
            ->where('articulos.empresa_id', $user->empresa_id) // Filtro por empresa
            ->where('bodegas.sede_id', (int) $data['sede_id']) // Filtro por sede específica
            // ->FilterAdvance($data)

            ->when($data['buscar'], function ($sql) use ($data) {
                $sql->where(DB::raw("CONCAT(articulos.nombre,' ',articulos.sku)"), 'like', '%' . $data['buscar'] . '%');
            })

            // Filtro por categoría
            ->when(isset($data['categoria_id']), function ($sql) use ($data) {
                $sql->where('categoria_id', $data['categoria_id']);
            })

            // Filtro por disponibilidad
            ->when(isset($data['state_stock']), function ($sql) use ($data) {
                $sql->where('state_stock', $data['state_stock']);
            })

            // Filtro por proveedor
            ->when(isset($data['proveedor_id']), function ($sql) use ($data) {
                $sql->where('proveedor_id', $data['proveedor_id']);
            })

            ->when(isset($data['bodega_id']), function ($sql) use ($data) {
                $sql->whereHas('bodegas_articulos', function ($sub) use ($data) {
                    $sub->where('bodega_id', $data['bodega_id']);
                });
            })

            ->when(isset($data['unidad_id_bodegas']), function ($sql) use ($data) {
                $sql->whereHas('bodegas_articulos', function ($sub) use ($data) {
                    $sub->where('unidad_id', $data['unidad_id_bodegas']);
                });
            })

            ->groupBy(...$groupByColumns)

            ->havingRaw('SUM(bodegas_articulos.cantidad) < articulos.punto_pedido') // Baja existencia
            ->orderBy('articulos.id', 'desc') // Orden descendente por ID
            // ->paginate(20); // Paginación
            ->get();

        // Actualiza el campo `state_stock` basado en las condiciones
        foreach ($articulos as $articulo) {
            // Si la existencia es 0, actualiza a estado 3 y continúa con el siguiente artículo
            if ($articulo->total_existencia == 0) {
                $articulo->state_stock = 3; // Estado por "agotado"
                $articulo->save();
                continue; // Salta al siguiente artículo
            }

            // Si la existencia es menor al punto de pedido, actualiza a estado 2
            if ($articulo->total_existencia <= $articulo->punto_pedido) {
                $articulo->state_stock = 2; // Estado por "baja existencia"
                $articulo->save();
            }
        }

        // Simula la paginación en memoria
        // $currentPage = request('page', 1); // Obtén la página actual (por defecto 1)
        $currentPage = $data["page"] ?? 1; // Obtén la página actual desde $data o usa 1 como valor por defecto
        $perPage = 20; // Registros por página
        $paginacion = new \Illuminate\Pagination\LengthAwarePaginator(
            $articulos->forPage($currentPage, $perPage), // Registros de la página actual
            $articulos->count(), // Total de registros
            $perPage, // Registros por página
            $currentPage, // Página actual
            ['path' => request()->url(), 'query' => request()->query()] // Parámetros de la URL
        );

        // Devuelve la paginación
        return $paginacion;
    }

    public function getAllArticulosBajaExistencia($data)
    {
        // Normaliza los valores especiales
        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $data["sede_usuario_id"] : ($data['sede_id'] ?? null);
        $data['bodega_id'] = isset($data['bodega_id']) && $data['bodega_id'] == 9999999 ? null : ($data['bodega_id'] ?? null);
        $data['unidad_id_bodegas'] = isset($data['unidad_id_bodegas']) && $data['unidad_id_bodegas'] == 9999999 ? null : ($data['unidad_id_bodegas'] ?? null);
        $data['proveedor_id'] = isset($data['proveedor_id']) && $data['proveedor_id'] == 9999999 ? null : ($data['proveedor_id'] ?? null);
        $data['state_stock'] = isset($data['state_stock']) && $data['state_stock'] == 9999999 ? null : ($data['state_stock'] ?? null);

        // Obtén los nombres de todas las columnas de la tabla 'articulos'
        $articuloColumns = Schema::getColumnListing('articulos');

        // Agrega el prefijo 'articulos.' a cada columna para evitar ambigüedades
        $articuloColumns = array_map(fn($column) => "articulos.$column", $articuloColumns);

        // Agrega campos adicionales necesarios para el `GROUP BY`
        $groupByColumns = array_merge($articuloColumns, [
            'bodegas.sede_id',
            'unidad_punto_pedido.nombre',
            'unidad_bodega.nombre',
        ]);

        return  Articulo::with([
            'empresa',
            'categoria',
            'unidad_punto_pedido',
            'usuario',
            'proveedor',
            'bodegas_articulos',
        ])
            ->select(
                'articulos.*',
                'bodegas.sede_id', // Incluimos explícitamente la sede
                DB::raw('SUM(bodegas_articulos.cantidad) as total_existencia'),
                'unidad_punto_pedido.nombre as unidad_articulo',
                'unidad_bodega.nombre as unidad_bodega'
            )
            ->join('bodegas_articulos', 'articulos.id', '=', 'bodegas_articulos.articulo_id') // Unión con bodegas_articulos
            ->join('bodegas', 'bodegas.id', '=', 'bodegas_articulos.bodega_id') // Unión con bodegas
            ->leftJoin('unidades as unidad_punto_pedido', 'articulos.punto_pedido_unidad_id', '=', 'unidad_punto_pedido.id') // Unión con unidad_articulo
            ->leftJoin('unidades as unidad_bodega', 'bodegas_articulos.unidad_id', '=', 'unidad_bodega.id') // Unión con unidad_bodega
            ->where('articulos.empresa_id', $data["empresa_id"]) // Filtro por empresa
            ->where('bodegas.sede_id', $data["sede_id"]) // Filtro por sede específica
            // ->FilterAdvance($data)

            ->when($data['buscar'], function ($sql) use ($data) {
                $sql->where(DB::raw("CONCAT(articulos.nombre,' ',articulos.sku)"), 'like', '%' . $data['buscar'] . '%');
            })

            // Filtro por categoría
            ->when(isset($data['categoria_id']), function ($sql) use ($data) {
                $sql->where('categoria_id', $data['categoria_id']);
            })

            // Filtro por disponibilidad
            ->when(isset($data['state_stock']), function ($sql) use ($data) {
                $sql->where('state_stock', $data['state_stock']);
            })

            // Filtro por proveedor
            ->when(isset($data['proveedor_id']), function ($sql) use ($data) {
                $sql->where('proveedor_id', $data['proveedor_id']);
            })

            ->when(isset($data['bodega_id']), function ($sql) use ($data) {
                $sql->whereHas('bodegas_articulos', function ($sub) use ($data) {
                    $sub->where('bodega_id', $data['bodega_id']);
                });
            })

            ->when(isset($data['unidad_id_bodegas']), function ($sql) use ($data) {
                $sql->whereHas('bodegas_articulos', function ($sub) use ($data) {
                    $sub->where('unidad_id', $data['unidad_id_bodegas']);
                });
            })
            ->groupBy(...$groupByColumns)

            ->havingRaw('SUM(bodegas_articulos.cantidad) < articulos.punto_pedido') // Baja existencia
            ->orderBy('articulos.id', 'desc') // Orden descendente por ID
            // ->paginate(20); // Paginación
            ->get();
    }

    public function getSedeById($id)
    {
        return Sede::findOrFail($id);
    }

    public function getBodegaById($id)
    {
        return Bodega::findOrFail($id);
    }

    public function getProveedorById($id)
    {
        return Proveedor::findOrFail($id);
    }

    public function getCategoriaById($id)
    {
        return Categoria::findOrFail($id);
    }
}
