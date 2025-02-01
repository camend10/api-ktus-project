<?php

namespace App\Services\Reportes;

use Illuminate\Support\Facades\Schema;
use App\Models\Articulos\Articulo;
use App\Models\Articulos\ArticuloWallet;
use App\Models\Articulos\BodegaArticulo;
use App\Models\Articulos\Conversion;
use App\Models\Configuracion\Bodega;
use App\Models\Configuracion\Categoria;
use App\Models\Configuracion\MetodoPago;
use App\Models\Configuracion\Proveedor;
use App\Models\Configuracion\Sede;
use App\Models\Configuracion\SegmentoCliente;
use App\Models\Facturas\DetalleFactura;
use App\Models\Facturas\Factura;
use App\Models\Movimientos\Movimiento;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ReporteService
{

    public function getSedeById($id)
    {
        return Sede::findOrFail($id);
    }

    public function getUserById($id)
    {
        return User::findOrFail($id);
    }

    public function getSegmentoById($id)
    {
        return SegmentoCliente::findOrFail($id);
    }

    public function getMetodoById($id)
    {
        return MetodoPago::findOrFail($id);
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

    public function getVentas($data)
    {

        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        $data['segmento_cliente_id'] = isset($data['segmento_cliente_id']) && $data['segmento_cliente_id'] == 9999999 ? null : ($data['segmento_cliente_id'] ?? null);
        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
        $data['fecha_final'] = $data['fecha_final'] ?? null;
        $data['vendedor_id'] = isset($data['vendedor_id']) && $data['vendedor_id'] == 9999999 ? null : ($data['vendedor_id'] ?? null);
        $data['metodo_pago_id'] = isset($data['metodo_pago_id']) && $data['metodo_pago_id'] == 9999999 ? null : ($data['metodo_pago_id'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $user->sede_id : ($data['sede_id'] ?? null);

        return Factura::with([
            'empresa',
            'sede',
            'usuario',
            'cliente',
            'segmento',
            'detalles_facturas.articulo',
            'detalles_facturas.categoria',
            'detalles_facturas.unidad',
            'factura_deliverie.sede_deliverie',
            'factura_pago.metodo_pago'
        ])
            ->where("empresa_id", $empresa_id)
            // ->when(!in_array($role_id, [1, 2]), function ($query) use ($data) {
            //     $query->where('sede_id', (int) $data['sede_id']);
            // })
            ->where('sede_id', (int) $data['sede_id'])
            // ->FilterAdvance($data)
            // Filtro por segmento_id
            ->when(isset($data['segmento_cliente_id']), function ($sql) use ($data) {
                $sql->where('segmento_cliente_id', $data['segmento_cliente_id']);
            })

            // Filtro por categoría
            ->when(isset($data['categoria_id']), function ($sql) use ($data) {
                $sql->whereHas('detalles_facturas', function ($sub) use ($data) {
                    $sub->where('categoria_id', $data['categoria_id']);
                });
            })

            // Filtro por metodo pago
            ->when(isset($data['metodo_pago_id']), function ($sql) use ($data) {
                $sql->whereHas('factura_pago', function ($sub) use ($data) {
                    $sub->where('metodo_pago_id', $data['metodo_pago_id']);
                });
            })

            // Filtro por fecha
            ->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
                $sql->whereBetween('created_at', [
                    Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                    Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
                ]);
            })

            // Filtro por vendedor
            ->when(isset($data['vendedor_id']), function ($sql) use ($data) {
                $sql->where('user_id', $data['vendedor_id']);
            })

            ->where("estado", 1)
            ->orderBy("id", "desc")
            ->paginate(20);
    }

    public function getVentasPdf($data)
    {

        $empresa_id = $data["empresa_id"];

        $data['segmento_cliente_id'] = isset($data['segmento_cliente_id']) && $data['segmento_cliente_id'] == 9999999 ? null : ($data['segmento_cliente_id'] ?? null);
        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
        $data['fecha_final'] = $data['fecha_final'] ?? null;
        $data['vendedor_id'] = isset($data['vendedor_id']) && $data['vendedor_id'] == 9999999 ? null : ($data['vendedor_id'] ?? null);
        $data['metodo_pago_id'] = isset($data['metodo_pago_id']) && $data['metodo_pago_id'] == 9999999 ? null : ($data['metodo_pago_id'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $data["sede_usuario_id"] : ($data['sede_id'] ?? null);

        return Factura::with([
            'empresa',
            'sede',
            'usuario',
            'cliente',
            'segmento',
            'detalles_facturas.articulo',
            'detalles_facturas.categoria',
            'detalles_facturas.unidad',
            'factura_deliverie.sede_deliverie',
            'factura_pago.metodo_pago'
        ])
            ->where("empresa_id", $empresa_id)
            // ->when(!in_array($role_id, [1, 2]), function ($query) use ($data) {
            //     $query->where('sede_id', (int) $data['sede_id']);
            // })
            ->where('sede_id', (int) $data['sede_id'])
            // ->FilterAdvance($data)
            // Filtro por segmento_id
            ->when(isset($data['segmento_cliente_id']), function ($sql) use ($data) {
                $sql->where('segmento_cliente_id', $data['segmento_cliente_id']);
            })

            // Filtro por categoría
            ->when(isset($data['categoria_id']), function ($sql) use ($data) {
                $sql->whereHas('detalles_facturas', function ($sub) use ($data) {
                    $sub->where('categoria_id', $data['categoria_id']);
                });
            })

            // Filtro por metodo pago
            ->when(isset($data['metodo_pago_id']), function ($sql) use ($data) {
                $sql->whereHas('factura_pago', function ($sub) use ($data) {
                    $sub->where('metodo_pago_id', $data['metodo_pago_id']);
                });
            })

            // Filtro por fecha
            ->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
                $sql->whereBetween('created_at', [
                    Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                    Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
                ]);
            })

            // Filtro por vendedor
            ->when(isset($data['vendedor_id']), function ($sql) use ($data) {
                $sql->where('user_id', $data['vendedor_id']);
            })

            ->where("estado", 1)
            ->orderBy("id", "desc")
            ->paginate(20);
    }

    public function getVendidos($data)
    {

        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        $empresa_id = $user->empresa_id;
        $sede_id = $user->sede_id;
        $role_id = $user->role_id;

        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
        $data['fecha_final'] = $data['fecha_final'] ?? null;
        $data['vendedor_id'] = isset($data['vendedor_id']) && $data['vendedor_id'] == 9999999 ? null : ($data['vendedor_id'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $user->sede_id : ($data['sede_id'] ?? null);

        $articulos = DetalleFactura::select('articulo_id', DB::raw('SUM(cantidad_item) as total_vendido'))
            // Filtro por fecha
            ->where("estado", 2)
            ->where("empresa_id", $empresa_id)
            ->where('sede_id', (int) $data['sede_id'])
            ->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
                $sql->whereBetween('created_at', [
                    Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                    Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
                ]);
            })
            // Filtro por categoría
            ->when(isset($data['categoria_id']), function ($sql) use ($data) {
                $sql->where('categoria_id', $data['categoria_id']);
            })
            // Filtro por vendedor
            ->when(isset($data['vendedor_id']), function ($sql) use ($data) {
                $sql->whereHas('factura', function ($sub) use ($data) {
                    $sub->where('user_id', $data['vendedor_id']);
                });
            })
            ->groupBy('articulo_id')
            ->orderByDesc('total_vendido')
            // Cargar relaciones del artículo
            ->with(['articulo' => function ($query) {
                $query->with([
                    'empresa',
                    'categoria',
                    'unidad_punto_pedido',
                    'usuario',
                    'proveedor',
                    'bodegas_articulos',
                    'articulos_wallets',
                ]);
            }])
            ->limit(10)
            ->paginate(10);

        // Transformar los datos antes de retornar
        return $articulos->through(function ($detalleFactura) {
            $articulo = $detalleFactura->articulo;

            $unidad_vendida = $articulo->unidad_punto_pedido ? $articulo->unidad_punto_pedido->nombre : 'Unidad';

            // Agrupar unidades de articulos_wallets
            $unidades = collect([]);
            foreach ($articulo->articulos_wallets->groupBy('unidad_id') as $value) {
                $unidades->push($value[0]->unidad);
            }

            return [
                'articulo_id' => $detalleFactura->articulo_id,
                'total_vendido' => $detalleFactura->total_vendido,
                'unidad' => $unidad_vendida,
                'articulo' => [
                    'id' => $articulo->id,
                    'sku' => $articulo->sku,
                    'nombre' => $articulo->nombre,
                    'state_stock' => $articulo->state_stock,
                    'descripcion' => $articulo->descripcion ?? '',
                    'precio_general' => $articulo->precio_general,
                    'punto_pedido' => $articulo->punto_pedido,
                    'tipo' => $articulo->tipo,
                    'imagen' => $articulo->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $articulo->imagen : env("APP_URL") . "storage/articulos/blank-image.svg",
                    'iva_id' => $articulo->iva_id ?? 9999999,
                    'empresa_id' => $articulo->empresa_id,
                    'estado' => $articulo->estado ?? 9999999,
                    'especificaciones' => is_string($articulo->especificaciones)
                        ? json_decode($articulo->especificaciones, true)
                        : ($articulo->especificaciones ?? []),
                    'categoria_id' => $articulo->categoria_id ?? 9999999,
                    'is_gift' => $articulo->is_gift ?? 1,
                    'descuento_maximo' => $articulo->descuento_maximo ?? 0,
                    'descuento_minimo' => $articulo->descuento_minimo ?? 0,
                    'tiempo_de_abastecimiento' => $articulo->tiempo_de_abastecimiento ?? 0,
                    'disponibilidad' => $articulo->disponibilidad ?? 9999999,
                    'peso' => $articulo->peso ?? 0,
                    'ancho' => $articulo->ancho ?? 0,
                    'alto' => $articulo->alto ?? 0,
                    'largo' => $articulo->largo ?? 0,
                    'user_id' => $articulo->user_id,
                    'punto_pedido_unidad_id' => $articulo->punto_pedido_unidad_id ?? 9999999,
                    'is_discount' => $articulo->is_discount ?? 1,
                    'impuesto' => $articulo->impuesto ?? 9999999,
                    'proveedor_id' => $articulo->proveedor_id ?? 9999999,
                    "created_format_at" => $articulo->created_at ? $articulo->created_at->format("Y-m-d h:i A") : '',
                    'iva' => $articulo->iva ? $articulo->iva : null,
                    'empresa' => $articulo->empresa,
                    'categoria' => $articulo->categoria,
                    'usuario' => $articulo->usuario,
                    'unidad_punto_pedido' => $articulo->unidad_punto_pedido ? $articulo->unidad_punto_pedido : null,
                    'proveedor' => $articulo->proveedor ? $articulo->proveedor : null,
                    'bodegas_articulos' => $articulo->bodegas_articulos->map(function ($bodega) {
                        return [
                            "id" => $bodega->id,
                            "unidad" => $bodega->unidad,
                            "bodega" => $bodega->bodega,
                            "cantidad" => $bodega->cantidad
                        ];
                    }),
                    'articulos_wallets' => $articulo->articulos_wallets->map(function ($wallet) {
                        return [
                            "id" => $wallet->id,
                            "unidad" => $wallet->unidad,
                            "sede" => $wallet->sede,
                            "segmento_cliente" => $wallet->segmento_cliente,
                            "precio" => $wallet->precio,
                            "sede_id_premul" => $wallet->sede ? $wallet->sede->id : null,
                            "segmento_cliente_id_premul" => $wallet->segmento_cliente ? $wallet->segmento_cliente->id : null,
                        ];
                    }),
                    'unidades' => $unidades
                ]
            ];
        });
    }

    public function getAllVendidos($data)
    {

        $data['categoria_id'] = isset($data['categoria_id']) && $data['categoria_id'] == 9999999 ? null : ($data['categoria_id'] ?? null);
        $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
        $data['fecha_final'] = $data['fecha_final'] ?? null;
        $data['vendedor_id'] = isset($data['vendedor_id']) && $data['vendedor_id'] == 9999999 ? null : ($data['vendedor_id'] ?? null);
        $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $data["sede_usuario_id"] : ($data['sede_id'] ?? null);

        $articulos = DetalleFactura::select('articulo_id', DB::raw('SUM(cantidad_item) as total_vendido'))
            // Filtro por fecha
            ->where("estado", 2)
            ->where("empresa_id", $data['empresa_id'])
            ->where('sede_id', (int) $data['sede_id'])
            ->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
                $sql->whereBetween('created_at', [
                    Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                    Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
                ]);
            })
            // Filtro por categoría
            ->when(isset($data['categoria_id']), function ($sql) use ($data) {
                $sql->where('categoria_id', $data['categoria_id']);
            })
            // Filtro por vendedor
            ->when(isset($data['vendedor_id']), function ($sql) use ($data) {
                $sql->whereHas('factura', function ($sub) use ($data) {
                    $sub->where('user_id', $data['vendedor_id']);
                });
            })
            ->groupBy('articulo_id')
            ->orderByDesc('total_vendido')
            // Cargar relaciones del artículo
            ->with(['articulo' => function ($query) {
                $query->with([
                    'empresa',
                    'categoria',
                    'unidad_punto_pedido',
                    'usuario',
                    'proveedor',
                    'bodegas_articulos',
                    'articulos_wallets',
                ]);
            }])
            ->limit(10)
            ->get(); // Se cambió paginate(10) por get()

        // Transformar los datos antes de retornar
        return $articulos->map(function ($detalleFactura) {
            $articulo = $detalleFactura->articulo;

            $unidad_vendida = $articulo->unidad_punto_pedido ? $articulo->unidad_punto_pedido->nombre : 'Unidad';

            // Agrupar unidades de articulos_wallets
            $unidades = collect([]);
            foreach ($articulo->articulos_wallets->groupBy('unidad_id') as $value) {
                $unidades->push($value[0]->unidad);
            }

            return [
                'articulo_id' => $detalleFactura->articulo_id,
                'total_vendido' => $detalleFactura->total_vendido,
                'unidad' => $unidad_vendida,
                'articulo' => [
                    'id' => $articulo->id,
                    'sku' => $articulo->sku,
                    'nombre' => $articulo->nombre,
                    'state_stock' => $articulo->state_stock,
                    'descripcion' => $articulo->descripcion ?? '',
                    'precio_general' => $articulo->precio_general,
                    'punto_pedido' => $articulo->punto_pedido,
                    'tipo' => $articulo->tipo,
                    'imagen' => $articulo->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $articulo->imagen : env("APP_URL") . "storage/articulos/blank-image.svg",
                    'iva_id' => $articulo->iva_id ?? 9999999,
                    'empresa_id' => $articulo->empresa_id,
                    'estado' => $articulo->estado ?? 9999999,
                    'especificaciones' => is_string($articulo->especificaciones)
                        ? json_decode($articulo->especificaciones, true)
                        : ($articulo->especificaciones ?? []),
                    'categoria_id' => $articulo->categoria_id ?? 9999999,
                    'is_gift' => $articulo->is_gift ?? 1,
                    'descuento_maximo' => $articulo->descuento_maximo ?? 0,
                    'descuento_minimo' => $articulo->descuento_minimo ?? 0,
                    'tiempo_de_abastecimiento' => $articulo->tiempo_de_abastecimiento ?? 0,
                    'disponibilidad' => $articulo->disponibilidad ?? 9999999,
                    'peso' => $articulo->peso ?? 0,
                    'ancho' => $articulo->ancho ?? 0,
                    'alto' => $articulo->alto ?? 0,
                    'largo' => $articulo->largo ?? 0,
                    'user_id' => $articulo->user_id,
                    'punto_pedido_unidad_id' => $articulo->punto_pedido_unidad_id ?? 9999999,
                    'is_discount' => $articulo->is_discount ?? 1,
                    'impuesto' => $articulo->impuesto ?? 9999999,
                    'proveedor_id' => $articulo->proveedor_id ?? 9999999,
                    "created_format_at" => $articulo->created_at ? $articulo->created_at->format("Y-m-d h:i A") : '',
                    'iva' => $articulo->iva ? $articulo->iva : null,
                    'empresa' => $articulo->empresa,
                    'categoria' => $articulo->categoria,
                    'usuario' => $articulo->usuario,
                    'unidad_punto_pedido' => $articulo->unidad_punto_pedido ? $articulo->unidad_punto_pedido : null,
                    'proveedor' => $articulo->proveedor ? $articulo->proveedor : null,
                    'bodegas_articulos' => $articulo->bodegas_articulos->map(function ($bodega) {
                        return [
                            "id" => $bodega->id,
                            "unidad" => $bodega->unidad,
                            "bodega" => $bodega->bodega,
                            "cantidad" => $bodega->cantidad
                        ];
                    }),
                    'articulos_wallets' => $articulo->articulos_wallets->map(function ($wallet) {
                        return [
                            "id" => $wallet->id,
                            "unidad" => $wallet->unidad,
                            "sede" => $wallet->sede,
                            "segmento_cliente" => $wallet->segmento_cliente,
                            "precio" => $wallet->precio,
                            "sede_id_premul" => $wallet->sede ? $wallet->sede->id : null,
                            "segmento_cliente_id_premul" => $wallet->segmento_cliente ? $wallet->segmento_cliente->id : null,
                        ];
                    }),
                    'unidades' => $unidades
                ]
            ];
        });
    }

    public function getAllInventario($data)
    {

        // Obtén los nombres de todas las columnas de la tabla 'articulos'
        $articuloColumns = Schema::getColumnListing('articulos');

        // Agrega el prefijo 'articulos.' a cada columna para evitar ambigüedades
        $articuloColumns = array_map(fn($column) => "articulos.$column", $articuloColumns);

        // Agrega campos adicionales necesarios para el `GROUP BY`
        $groupByColumns = array_merge($articuloColumns, [
            'unidad_punto_pedido.nombre',
            'unidad_bodega.nombre',
        ]);

        return Articulo::with([
            'empresa',
            'categoria',
            'unidad_punto_pedido',
            'usuario',
            'proveedor',
            'bodegas_articulos.bodega', // Cargar bodegas relacionadas
        ])
            ->where('articulos.empresa_id', (int) $data['empresa_id']) // Filtro por empresa
            ->where('articulos.estado', 1)
            ->select(
                'articulos.*',
                DB::raw('COALESCE(SUM(bodegas_articulos.cantidad), 0) as total_existencia'),
                'unidad_punto_pedido.nombre as unidad_articulo',
                'unidad_bodega.nombre as unidad_bodega'
            )
            ->leftJoin('bodegas_articulos', 'articulos.id', '=', 'bodegas_articulos.articulo_id') // Unión con bodegas_articulos
            ->leftJoin('unidades as unidad_punto_pedido', 'articulos.punto_pedido_unidad_id', '=', 'unidad_punto_pedido.id') // Unión con unidad_articulo
            ->leftJoin('unidades as unidad_bodega', 'bodegas_articulos.unidad_id', '=', 'unidad_bodega.id') // Unión con unidad_bodega            
            // ->FilterAdvance($data)
            ->groupBy(...$groupByColumns)
            ->orderBy('articulos.id', 'desc')
            ->get();
    }

    public function getMovimientos($data, $opc = 1)
    {
        try {
            $empresa_id = 0;
            $sede_id = 0;
            $role_id = 0;

            if ($opc == 1) {
                $user = auth("api")->user();

                if (!$user) {
                    return false;
                }

                $empresa_id = $user->empresa_id;
                $sede_id = $user->sede_id;
                $role_id = $user->role_id;
                $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $user->sede_id : ($data['sede_id'] ?? null);
            } else {
                $empresa_id = $data["empresa_id"];
                $data['sede_id'] = isset($data['sede_id']) && $data['sede_id'] == 9999999 ? $data["sede_usuario_id"] : ($data['sede_id'] ?? null);
            }


            $data['fecha_inicio'] = $data['fecha_inicio'] ?? null;
            $data['fecha_final'] = $data['fecha_final'] ?? null;
            $data['articulo'] = $data['articulo'] ?? null;
            $data['bodega_id'] = isset($data['bodega_id']) && $data['bodega_id'] == 9999999 ? null : ($data['bodega_id'] ?? null);
            
            // Obtener el ID y el nombre del primer artículo que coincide con el filtro
            $articuloId = null;
            $nombreArticulo = null;
            if (isset($data['articulo'])) {
                $articulo = DB::table('articulos')
                    ->where('empresa_id', $empresa_id)
                    ->where(function ($query) use ($data) {
                        $query->where('nombre', 'like', '%' . $data['articulo'] . '%')
                            ->orWhere('sku', 'like', '%' . $data['articulo'] . '%');
                    })
                    ->first(); // Obtener el primer artículo que coincide

                if ($articulo) {
                    $articuloId = $articulo->id; // ID del artículo
                    $nombreArticulo = $articulo->nombre; // Nombre del artículo
                }
            }
           
            $unidadBase = null;
            $nombreUnidadBase = null;
            if ($articuloId) {
                $unidadBase = DB::table('articulos')
                    ->where('id', $articuloId)
                    ->value('punto_pedido_unidad_id'); // Unidad base del artículo

                if ($unidadBase) {
                    $nombreUnidadBase = DB::table('unidades')
                        ->where('id', $unidadBase)
                        ->value('nombre'); // Nombre de la unidad base
                }
            }            

            if ($data['fecha_inicio']) {

                // Sumar entradas y restar salidas en movimientos
                $existenciaInicial = Movimiento::whereHas('detalles_movimientos', function ($subQuery) use ($empresa_id, $articuloId) {
                    $subQuery->join('articulos', 'detalle_movimientos.articulo_id', '=', 'articulos.id')
                        ->where('articulos.empresa_id', $empresa_id);

                    // Filtrar por el ID del artículo si se encontró una coincidencia
                    if ($articuloId) {
                        $subQuery->where('articulos.id', $articuloId);
                    }
                })

                    ->when(isset($data['bodega_id']), function ($sql) use ($data) {
                        $sql->where('movimientos.bodega_id', $data['bodega_id']);
                    })
                    ->where('fecha_entrega', '<', $data['fecha_inicio'])
                    ->with(['detalles_movimientos' => function ($query) use ($articuloId, $empresa_id) {
                        // Filtrar detalles_movimientos por el artículo específico
                        $query->where('articulo_id', $articuloId)
                            ->where('empresa_id', $empresa_id);
                    }])
                    ->get()
                    ->reduce(function ($carry, $movimiento) use ($unidadBase, &$vector, &$i) {
                        // Inicializar el contador si aún no existe

                        // Convertir las cantidades a la unidad base antes de sumar o restar
                        $cantidad = $movimiento->detalles_movimientos->sum(function ($detalle) use ($unidadBase) {
                            return $this->convertirUnidad($detalle->cantidad, $detalle->unidad_id, $unidadBase);
                        });

                        // Sumar entradas (tipo_movimiento = 1) y restar salidas (tipo_movimiento = 2)
                        return $carry + ($movimiento->tipo_movimiento == 1 ? $cantidad : -$cantidad);
                    }, 0);

                // Restar las cantidades de las facturas (ventas)
                $existenciaInicial -= Factura::whereHas('detalles_facturas', function ($subQuery) use ($empresa_id, $articuloId) {
                    $subQuery->join('articulos', 'detalle_facturas.articulo_id', '=', 'articulos.id')
                        ->where('articulos.empresa_id', $empresa_id);

                    // Filtrar por el ID del artículo si se encontró una coincidencia
                    if ($articuloId) {
                        $subQuery->where('articulos.id', $articuloId);
                    }
                })
                    ->where('created_at', '<', $data['fecha_inicio'])
                    ->with(['detalles_facturas' => function ($query) use ($articuloId, $empresa_id, $data) {
                        // Filtrar detalles_facturas por el artículo específico
                        $query->where('articulo_id', $articuloId)
                            ->where('empresa_id', $empresa_id)
                            ->where('bodega_id', $data['bodega_id']);
                    }])
                    ->get()
                    ->reduce(function ($carry, $factura) use ($unidadBase) {
                        // Convertir las cantidades a la unidad base antes de restar
                        $cantidad = $factura->detalles_facturas->sum(function ($detalle) use ($unidadBase) {
                            return $this->convertirUnidad($detalle->cantidad_item, $detalle->unidad_id, $unidadBase);
                        });

                        return $carry - $cantidad;
                    }, 0);

                // Sumar las cantidades de las conversiones (unidades finales) y restar las unidades iniciales
                $existenciaInicial += Conversion::whereHas('articulo', function ($query) use ($empresa_id, $articuloId) {
                    $query->where('empresa_id', $empresa_id);

                    // Filtrar por el ID del artículo si se encontró una coincidencia
                    if ($articuloId) {
                        $query->where('id', $articuloId);
                    }
                })
                    ->where('created_at', '<', $data['fecha_inicio'])
                    ->get()
                    ->reduce(function ($carry, $conversion) use ($unidadBase) {
                        // Convertir las cantidades a la unidad base antes de sumar o restar
                        $cantidadInicial = $this->convertirUnidad($conversion->cantidad_inicial, $conversion->unidad_inicio_id, $unidadBase);
                        $cantidadFinal = $this->convertirUnidad($conversion->cantidad_final, $conversion->unidad_final_id, $unidadBase);

                        // Sumar las unidades finales y restar las unidades iniciales
                        return $carry + $cantidadFinal - $cantidadInicial;
                    }, 0);
            }            

            // Consulta de Movimientos (Entradas y Salidas)
            $movimientos = Movimiento::with(['detalles_movimientos.articulo', 'bodega', 'proveedor', 'usuario'])
                ->where('estado', 4) // Solo movimientos confirmados
                ->where('empresa_id', $empresa_id)

                ->whereHas('detalles_movimientos', function ($subQuery) use ($empresa_id, $articuloId) {
                    $subQuery->join('articulos', 'detalle_movimientos.articulo_id', '=', 'articulos.id')
                        ->where('articulos.empresa_id', $empresa_id);

                    // Filtrar por el ID del artículo si se encontró una coincidencia
                    if ($articuloId) {
                        $subQuery->where('articulos.id', $articuloId);
                    }
                })

                ->when(isset($data['sede_id']), function ($sql) use ($data) {
                    $sql->where('sede_id', $data['sede_id']);
                })

                ->when(isset($data['bodega_id']), function ($sql) use ($data) {
                    $sql->where('bodega_id', $data['bodega_id']);
                })

                ->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
                    $sql->whereBetween('fecha_entrega', [
                        Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                        Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
                    ]);
                })

                ->get()

                ->map(function ($movimiento) use ($articuloId) {
                    // Filtrar los detalles del movimiento para incluir solo el artículo que coincide
                    $detallesFiltrados = $movimiento->detalles_movimientos->filter(function ($detalle) use ($articuloId) {
                        return $detalle->articulo_id == $articuloId;
                    });

                    // Calcular la suma de las cantidades
                    $cantidad = $detallesFiltrados->sum('cantidad');
                    return [
                        'id' => sprintf('MOV-%06d', $movimiento->id), // ID del movimiento
                        'tipo' => $movimiento->tipo_movimiento == 1 ? 'Entrada' : 'Salida',
                        'fecha' => $movimiento->fecha_entrega,
                        'observacion' => $movimiento->observacion,
                        'bodega' => $movimiento->bodega->nombre,
                        'proveedor' => $movimiento->proveedor->nombre ?? 'N/A',
                        'usuario' => $movimiento->usuario->name ?? 'N/A', // Nombre del usuario asociado
                        'cantidad' => $cantidad,
                        'origen' => $movimiento->destino,
                        'detalles' => $detallesFiltrados->map(function ($detalle) {
                            return [
                                'articulo' => $detalle->articulo->nombre,
                                'cantidad' => $detalle->cantidad,
                                'unidad' => $detalle->unidad->nombre,
                                'costo' => $detalle->costo,
                                'total' => $detalle->total,
                            ];
                        }),
                    ];
                });

            // Consulta de Facturas (Salidas)
            $facturas = Factura::with(['detalles_facturas.articulo', 'cliente', 'usuario'])
                ->where('estado', 1) // Solo facturas confirmadas
                ->where('empresa_id', $empresa_id)
                ->whereHas('detalles_facturas', function ($subQuery) use ($empresa_id, $articuloId) {
                    $subQuery->join('articulos', 'detalle_facturas.articulo_id', '=', 'articulos.id')
                        ->where('articulos.empresa_id', $empresa_id);

                    // Filtrar por el ID del artículo si se encontró una coincidencia
                    if ($articuloId) {
                        $subQuery->where('articulos.id', $articuloId);
                    }
                })
                ->when(isset($data['sede_id']), function ($sql) use ($data) {
                    $sql->where('sede_id', $data['sede_id']);
                })
                ->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
                    $sql->whereBetween('created_at', [
                        Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                        Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
                    ]);
                })
                ->get()
                ->map(function ($factura) use ($articuloId) {
                    // Filtrar los detalles de la factura para incluir solo el artículo que coincide
                    $detallesFiltrados = $factura->detalles_facturas->filter(function ($detalle) use ($articuloId) {
                        return $detalle->articulo_id == $articuloId;
                    });
                    // Calcular la suma de las cantidades
                    $cantidad = $detallesFiltrados->sum('cantidad_item');
                    return [
                        'id' => sprintf('FAC-%06d', $factura->id), // ID de la factura
                        'tipo' => 'Salida',
                        'fecha' => $factura->created_at,
                        'observacion' => 'Factura #' . $factura->id,
                        'cliente' => $factura->cliente->nombres ?? 'N/A',
                        'usuario' => $factura->usuario->name ?? 'N/A', // Nombre del usuario asociado
                        'cantidad' => $cantidad, // Suma de las cantidades
                        'origen' => 'Venta',
                        'detalles' => $detallesFiltrados->map(function ($detalle) {
                            return [
                                'articulo' => $detalle->articulo->nombre,
                                'cantidad' => $detalle->cantidad_item,
                                'unidad' => $detalle->unidad->nombre,
                                'precio' => $detalle->precio_item,
                                'total' => $detalle->total_precio,
                            ];
                        }),
                    ];
                });

            // Consulta de Conversiones
            $conversiones = Conversion::with(['articulo', 'unidad_inicio', 'unidad_final', 'bodega', 'usuario'])
                ->where('estado', '<>', 0)
                ->whereHas('articulo', function ($query) use ($empresa_id, $articuloId) {
                    $query->where('empresa_id', $empresa_id);

                    // Filtrar por el ID del artículo si se encontró una coincidencia
                    if ($articuloId) {
                        $query->where('id', $articuloId);
                    }
                })
                ->when(isset($data['sede_id']), function ($sql) use ($data) {
                    $sql->where('sede_id', $data['sede_id']);
                })
                ->when(isset($data['bodega_id']), function ($sql) use ($data) {
                    $sql->where('bodega_id', $data['bodega_id']);
                })
                ->when($data['fecha_inicio'] && $data['fecha_final'], function ($sql) use ($data) {
                    $sql->whereBetween('created_at', [
                        Carbon::parse($data['fecha_inicio'])->format('Y-m-d') . " 00:00:00",
                        Carbon::parse($data['fecha_final'])->format('Y-m-d') . " 23:59:59"
                    ]);
                })
                ->get()
                ->flatMap(function ($conversion) use ($articuloId) {
                    // Solo procesar si el artículo coincide
                    if ($conversion->articulo_id == $articuloId) {
                        return [
                            // Salida (unidad inicial)
                            [
                                'id' => sprintf('Nº-%06d', $conversion->id), // ID de la conversión
                                'tipo' => 'Salida',
                                'fecha' => $conversion->created_at,
                                'observacion' => 'Conversión de ' . $conversion->unidad_inicio->nombre . ' a ' . $conversion->unidad_final->nombre,
                                'bodega' => $conversion->bodega->nombre,
                                'usuario' => $conversion->usuario->name ?? 'N/A', // Nombre del usuario asociado
                                'cantidad' => $conversion->cantidad_final,
                                'origen' => 'Conversión',
                                'detalles' => [
                                    [
                                        'articulo' => $conversion->articulo->nombre,
                                        'cantidad' => (float) $conversion->cantidad_inicial,
                                        'unidad' => $conversion->unidad_inicio->nombre,
                                        'costo' => 0, // No hay costo en conversiones
                                        'total' => 0, // No hay total en conversiones
                                    ],
                                ],
                            ],
                            // Entrada (unidad final)
                            [
                                'id' => sprintf('Nº-%06d', $conversion->id),  // ID de la conversión
                                'tipo' => 'Entrada',
                                'fecha' => $conversion->created_at,
                                'observacion' => 'Conversión de ' . $conversion->unidad_inicio->nombre . ' a ' . $conversion->unidad_final->nombre,
                                'bodega' => $conversion->bodega->nombre,
                                'usuario' => $conversion->usuario->name ?? 'N/A', // Nombre del usuario asociado
                                'cantidad' => $conversion->cantidad_convertida,
                                'origen' => 'Conversión',
                                'detalles' => [
                                    [
                                        'articulo' => $conversion->articulo->nombre,
                                        'cantidad' => (float) $conversion->cantidad_final,
                                        'unidad' => $conversion->unidad_final->nombre,
                                        'costo' => 0, // No hay costo en conversiones
                                        'total' => 0, // No hay total en conversiones
                                    ],
                                ],
                            ],
                        ];
                    }
                    return [];
                });

            // $movimientos = collect($movimientos); // Si está vacío, se asegura de ser una colección vacía
            // $facturas = collect($facturas);
            // $conversiones = collect($conversiones);

            // Combinar todos los resultados
            // $resultados = $movimientos->merge($facturas)->merge($conversiones);
            // Combinar todos los resultados
            $resultados = collect([]) // Crear una colección vacía
                ->merge($movimientos) // Agregar movimientos
                ->merge($facturas) // Agregar facturas
                ->merge($conversiones); // Agregar conversiones
            // Ordenar por fecha
            $resultados = $resultados->sortBy('fecha')->values();

            // Calcular la existencia final
            $existenciaFinal = $existenciaInicial;
            $totalEntradas = 0;
            $totalSalidas = 0;
            foreach ($resultados as $movimiento) {
                if ($movimiento['tipo'] === 'Entrada') {
                    $existenciaFinal += $movimiento['cantidad'];
                    $totalEntradas += $movimiento['cantidad'];
                } elseif ($movimiento['tipo'] === 'Salida') {
                    $existenciaFinal -= $movimiento['cantidad'];
                    $totalSalidas += $movimiento['cantidad'];
                }
            }

            // Devolver la respuesta con la existencia inicial
            return [
                'existencia_inicial' => $existenciaInicial,
                'existencia_final' => $existenciaFinal,
                'total_entradas' => $totalEntradas,
                'total_salidas' => $totalSalidas,
                'unidad' => $nombreUnidadBase ?? 'Unidad no definida',
                'nombreArticulo' => $nombreArticulo,
                'movimientos' => $resultados,
            ];
        } catch (\Exception $e) {
            return false;
        }
    }

    // Función para convertir cantidades a la unidad base
    private function convertirUnidad($cantidad, $unidadOrigenId, $unidadBaseId)
    {
        if ($unidadOrigenId == $unidadBaseId) {
            return $cantidad; // No es necesario convertir
        }

        // Obtener el factor de conversión desde la tabla de conversiones
        $factor = DB::table('conversiones')
            ->where('unidad_origen_id', $unidadOrigenId)
            ->where('unidad_destino_id', $unidadBaseId)
            ->value('factor');

        if ($factor) {
            return $cantidad * $factor;
        }

        // Si no hay factor de conversión, asumir que no se puede convertir
        return 0;
    }
}
