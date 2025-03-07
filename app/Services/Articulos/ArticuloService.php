<?php

namespace App\Services\Articulos;

use Illuminate\Support\Facades\Schema;
use App\Models\Articulos\Articulo;
use App\Models\Articulos\ArticuloWallet;
use App\Models\Articulos\BodegaArticulo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ArticuloService
{

    public function getByFilter($data)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return Articulo::with([
            'iva',
            'empresa',
            'categoria',
            'unidad_punto_pedido',
            'usuario',
            'proveedor',
            'bodegas_articulos',
            'articulos_wallets'
        ])
            ->FilterAdvance($data)
            ->where('empresa_id', $user->empresa_id)
            ->orderBy('id', 'desc')
            ->paginate(20);
    }

    public function getByDisponibilidad($state_stock)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        return Articulo::where('state_stock', $state_stock)
            ->where('empresa_id', $user->empresa_id)
            ->count();
    }

    public function store($request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }
        $request["user_id"] = $user->id;
        try {
            // Inicia la transacción
            DB::beginTransaction();
            $articulo  = Articulo::create($request);

            if ($articulo) {
                // $bodegas_articulos = json_decode($request['bodegas_articulos'], true);
                // $articulos_wallets = json_decode($request['articulos_wallets'], true);

                // Sincronizar bodegas

                $bodegas_articulos = collect($request['bodegas_articulos'])
                    ->map(function ($item) use ($articulo) {
                        return [
                            'bodega_id' => $item['bodega']['id'], // Incluye explícitamente el id de la bodega como campo
                            'cantidad' => $item['cantidad'],
                            'estado' => 1,
                            'unidad_id' => $item['unidad']['id'],
                            'empresa_id' => $articulo->empresa_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    });


                $articulo->bodegas()->sync($bodegas_articulos);

                // Sincronizar wallets
                $articulos_wallets = collect($request['articulos_wallets'])
                    ->map(function ($item)  use ($articulo) {

                        $segmento_cliente_id = NULL;
                        if ($item['segmento_cliente_id_premul'] == 9999999) {
                            $segmento_cliente_id = NULL;
                        } else {
                            $segmento_cliente_id = $item['segmento_cliente_id_premul'];
                        }


                        $sede_id = NULL;
                        if ($item['sede_id_premul'] == 9999999) {
                            $sede_id = NULL;
                        } else {
                            $sede_id = $item['sede_id_premul'];
                        }

                        // Aquí siempre agregamos los campos necesarios
                        $wallet_data = [
                            'unidad_id' => $item['unidad']['id'],
                            'precio' => $item['precio'],
                            'estado' => 1,
                            'empresa_id' => $articulo->empresa_id,
                            'segmento_cliente_id' => $segmento_cliente_id,
                            'sede_id' =>  $sede_id,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        // // Verificar y agregar segmento_cliente_id si está definido
                        // if (isset($item['segmento_cliente']) && isset($item['segmento_cliente']['id'])) {
                        //     $wallet_data['segmento_cliente_id'] = $item['segmento_cliente']['id'];
                        // }

                        // // Verificar y agregar sede_id si está definido
                        // if (isset($item['sede']) && isset($item['sede']['id'])) {
                        //     $wallet_data['sede_id'] = $item['sede']['id'];
                        // }

                        // El valor de la clave debe ser el id de la sede, o null si no existe
                        // El valor de la clave debe ser el id de la sede, o null si no existe
                        // return [
                        //     $item['sede']['id'] ?? null => $wallet_data
                        // ];

                        return $wallet_data;
                    });
                // ->filter(function ($value, $key) {
                //     // Filtrar elementos con clave null
                //     return $key !== null;
                // });
                // Sincronizar los datos de wallets con los artículos
                $articulo->wallets()->sync($articulos_wallets);
            } else {
                return 500;
            }

            // Confirma la transacción
            DB::commit();

            return $articulo;
        } catch (\Throwable $e) {
            // Revierte la transacción si ocurre un error
            DB::rollBack();
            Log::error('Error al crear el articulo: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
            return false;
        }
    }

    public function update(array $request, $id)
    {

        try {
            // Inicia la transacción
            DB::beginTransaction();
            $articulo = Articulo::findOrFail($id);

            $articulo->especificaciones = empty($request['especificaciones'])
                ? null
                : (is_array($request['especificaciones'])
                    ? json_encode($request['especificaciones'], JSON_UNESCAPED_UNICODE)
                    : $request['especificaciones']);


            // Filtrar y llenar los datos del artículo
            $articulo->fill(array_intersect_key($request, array_flip([
                'sku',
                'nombre',
                'descripcion',
                'precio_general',
                'punto_pedido',
                'tipo',
                'imagen',
                'iva_id',
                'empresa_id',
                'estado',
                'especificaciones',
                'categoria_id',
                'is_gift',
                'descuento_maximo',
                'descuento_minimo',
                'tiempo_de_abastecimiento',
                'disponibilidad',
                'peso',
                'ancho',
                'alto',
                'largo',
                'user_id',
                'punto_pedido_unidad_id',
                'is_discount',
                'impuesto',
                'proveedor_id'
            ])));

            // Guardar cambios
            $articulo->save();


            // $articulo->update($request);

            DB::commit();

            return $articulo;
        } catch (\Throwable $e) {
            // Revierte la transacción si ocurre un error
            DB::rollBack();
            Log::error('Error al editar el articulo: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Articulo::findOrFail($id);
        if (!$resp) {
            return false;
        }

        // Actualizar el estado del usuario
        $resp->estado = $request["estado"];
        $resp->save();

        // validacion por usuarios
        return $resp;
    }

    public function getArticuloById($id)
    {
        return Articulo::findOrFail($id);
    }

    public function generarSku($categoria_id, $prefijo)
    {
        $user = auth('api')->user();

        if (!$user) {
            return false;
        }

        // Obtener el SKU más alto de la categoría seleccionada
        return Articulo::where('empresa_id', $user->empresa_id)
            ->where('categoria_id', $categoria_id)
            ->where('sku', 'LIKE', "$prefijo%")
            ->orderByRaw("CAST(SUBSTRING(sku, 4, LEN(sku) - 3) AS INT) DESC")
            ->value('sku');
    }

    public function getAllArticulos($data)
    {

        return Articulo::with([
            'iva',
            'empresa',
            'categoria',
            'unidad_punto_pedido',
            'usuario',
            'proveedor',
            'bodegas_articulos',
            'articulos_wallets'
        ])
            ->FilterAdvance($data)
            ->where('estado', 1)
            ->where('empresa_id', $data["empresa_id"])
            ->orderBy('id', 'desc')
            ->get();

        // logger($query->toSql()); // Registra la consulta SQL
        // logger($query->getBindings()); // Registra los valores de los parámetros
    }

    public function storeWallet($request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }

        $resp = ArticuloWallet::create([
            'articulo_id' => $request['articulo_id'],
            'unidad_id' => $request['unidad_id'],
            'precio' => $request['precio'],
            'estado' => 1,
            'empresa_id' => $user->empresa_id,
            'segmento_cliente_id' => $request['segmento_cliente_id'],
            'sede_id' =>  $request['sede_id'],
        ]);
        return $resp;
    }

    public function updateWallet($request, $id)
    {

        $resp = ArticuloWallet::findOrFail($id);

        $user = auth('api')->user();
        if (!$user) {
            return false;
        }

        $resp->update([
            'articulo_id' => $request['articulo_id'],
            'unidad_id' => $request['unidad_id'],
            'precio' => $request['precio'],
            'estado' => 1,
            'empresa_id' => $user->empresa_id,
            'segmento_cliente_id' => $request['segmento_cliente_id'],
            'sede_id' =>  $request['sede_id'],
        ]);

        return $resp;
    }

    public function destroyWallet(string $id)
    {

        $resp = ArticuloWallet::findOrFail($id);
        $resp->delete();

        return true;
    }

    public function storeBodega($request)
    {
        $user = auth('api')->user();
        if (!$user) {
            return false;
        }

        $resp = BodegaArticulo::create([
            'articulo_id' => $request['articulo_id'],
            'bodega_id' => $request['bodega_id'],
            'cantidad' =>  $request['cantidad'],
            'estado' => 1,
            'unidad_id' => $request['unidad_id'],
            'empresa_id' => $user->empresa_id,
        ]);

        return $resp;
    }

    public function updateBodega($request, $id)
    {
        $resp = BodegaArticulo::findOrFail($id);

        $user = auth('api')->user();
        if (!$user) {
            return false;
        }

        $resp->update([
            'bodega_id' => $request['bodega_id'], // Incluye explícitamente el id de la bodega como campo
            'cantidad' =>  $request['cantidad'],
            'estado' => 1,
            'unidad_id' => $request['unidad_id'],
            'empresa_id' => $user->empresa_id,
        ]);

        return $resp;
    }

    public function destroyBodega(string $id)
    {

        $resp = BodegaArticulo::findOrFail($id);
        $resp->delete();

        return true;
    }

}
