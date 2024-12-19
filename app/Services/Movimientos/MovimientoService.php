<?php

namespace App\Services\Movimientos;

use App\Models\Articulos\BodegaArticulo;
use App\Models\Movimientos\DetalleMovimiento;
use App\Models\Movimientos\Movimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MovimientoService
{
    public function getByFilter($data)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        if ($user && !in_array($user->role_id, [1, 2])) {
            return Movimiento::with([
                'empresa',
                'sede',
                'usuario',
                'proveedor',
                'bodega',
                'detalles_movimientos.articulo',
                'detalles_movimientos.unidad',
            ])
                ->FilterAdvance($data)
                ->where("empresa_id", $user->empresa_id)
                ->where("sede_id", $user->sede_id)
                ->where("destino", 'Movimiento')
                ->orderBy("id", "desc")
                ->paginate(20);
        } else {
            return Movimiento::with([
                'empresa',
                'sede',
                'usuario',
                'proveedor',
                'bodega',
                'detalles_movimientos.articulo',
                'detalles_movimientos.unidad',
            ])
                ->FilterAdvance($data)
                ->where("empresa_id", $user->empresa_id)
                ->where("destino", 'Movimiento')
                ->orderBy("id", "desc")
                ->paginate(20);
        }
    }

    public function store($request)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        try {
            // Inicia la transacción
            DB::beginTransaction();

            $respuesta = Movimiento::create([
                'fecha_emision' => $request["fecha_emision"],
                'tipo_movimiento' => $request["tipo_movimiento"],
                'observacion' => $request["observacion"],
                'observacion_entrega' => $request["observacion_entrega"],
                'destino' => $request["destino"],
                'total' => $request["total"],
                'user_id' => $request["user_id"],
                'bodega_id' => $request["bodega_id"],
                'plantilla_id' => $request["plantilla_id"],
                'proveedor_id' => $request["proveedor_id"],
                'empresa_id' => $request["empresa_id"],
                'sede_id' => $request["sede_id"],
                'estado' => $request["estado"],
                'fecha_entrega' => $request["fecha_entrega"],
            ]);


            $detalles = $request["detalles_movimientos"] ?? [];

            foreach ($detalles as $detalle) {
                DetalleMovimiento::create([
                    'cantidad' => $detalle["cantidad"],
                    'cantidad_recibida' => $detalle["cantidad_recibida"],
                    'total' => $detalle["total"],
                    'movimiento_id' => $respuesta->id,
                    'articulo_id' => $detalle["articulo"]["id"],
                    'empresa_id' => $detalle["empresa_id"],
                    'sede_id' => $detalle["sede_id"],
                    'estado' => $detalle["estado"],
                    'unidad_id' => $detalle["unidad"]["id"],
                    'costo' => $detalle["costo"],
                    'user_id' => $detalle["user_id"],
                    'fecha_entrega' => $detalle["fecha_entrega"],
                ]);
            }

            // Confirma la transacción
            DB::commit();

            return $respuesta;
        } catch (\Throwable $e) {
            // Revierte la transacción si ocurre un error
            DB::rollBack();
            Log::error('Error al crear la factura: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
            return false;
        }
    }

    public function update($request, $id)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        try {
            // Inicia la transacción
            DB::beginTransaction();

            // Crear o actualizar movimiento
            $movimiento = $id ? Movimiento::findOrFail($id) : new Movimiento();
            $this->fillMovimiento($movimiento, $request);
            $movimiento->save();

            // Sincronizar detalles
            $detalles = $request["detalles_movimientos"] ?? [];
            $detalle_ids = $this->syncDetalles($movimiento, $detalles);

            // Eliminar detalles antiguos
            DetalleMovimiento::where("movimiento_id", $movimiento->id)
                ->whereNotIn("id", $detalle_ids)
                ->delete();

            // Procesar entrada o salida si el estado es aprobado
            if ($request["estado"] == 4) {
                $result = $this->procesarMovimiento($movimiento, $request["tipo_movimiento"], $user);
                // Si hay un error, retorna el error
                if (isset($result['error']) && $result['error']) {
                    DB::rollBack(); // Revierte la transacción en caso de error
                    return $result;
                }

                $movimiento->update([
                    "fecha_entrega" => Carbon::now()
                ]);
            }

            // Confirmar transacción
            DB::commit();

            // Retorno exitoso
            return [
                'error' => false,
                'movimiento' => $movimiento,
            ];
        } catch (\Throwable $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();
            Log::error('Error al crear o actualizar el movimiento: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
        }
    }

    public function validacion($detalle, $bodega_id)
    {
        // Lógica para manejar cada $detalle_model
        Log::info('Llamando a movimiento con:', ['detalle' => $detalle]);

        $bodega_articulo = BodegaArticulo::where('articulo_id', $detalle->articulo_id)
            ->where('unidad_id', $detalle->articulo_id)
            ->where('bodega_id', $bodega_id)
            ->first();

        if (!$bodega_articulo) {
            return response()->json([
                'message' => 403,
                'message_text' => 'El producto ' . $detalle->articulo->nombre . ' no cuenta con la disponibilidad',
            ]);
        }

        if ($bodega_articulo->cantidad < $detalle->cantidad) {
            return response()->json([
                'message' => 403,
                'message_text' => 'El producto ' . $detalle->articulo->nombre . ' no cuenta con la disponibilidad',
            ]);
        }
    }

    public function cambiarEstado($request, $id)
    {
        $resp = $this->getById($id);
        if (!$resp) {
            return false;
        }

        $resp->estado = $request["estado"];
        $resp->save();

        // validacion por usuarios
        return $resp;
    }

    public function getById($id)
    {
        return Movimiento::with([
            'empresa',
            'sede',
            'usuario',
            'proveedor',
            'bodega',
            'detalles_movimientos.articulo.bodegas_articulos.bodega',
            'detalles_movimientos.articulo.bodegas_articulos.unidad',
            'detalles_movimientos.articulo.articulos_wallets.unidad',
            'detalles_movimientos.unidad',
        ])->findOrFail($id);
    }

    public function entrada($request)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        return DB::transaction(function () use ($request, $user) {
            // Obtener el movimiento
            $movimiento = $this->getById($request['movimiento_id']);

            // Obtener los IDs de los detalles seleccionados
            $detalles_movimientos_ids = $request['deta_movi_ids'] ?? [];

            // Filtrar los detalles que no han sido entregados y coinciden con los IDs
            $detalles_a_entregar = $movimiento->detalles_movimientos
                ->whereIn('id', $detalles_movimientos_ids)
                ->filter(fn($detalle) => !$detalle->fecha_entrega);

            // Contador para los detalles entregados
            $num_entregados_ahora = 0;

            foreach ($detalles_a_entregar as $detalle) {
                // Actualizar el detalle como entregado
                $detalle->update([
                    "estado" => 2, // Estado entregado
                    "user_id" => $user->id,
                    "fecha_entrega" => Carbon::now(),
                ]);

                // Actualizar o crear el artículo en la bodega
                BodegaArticulo::updateOrCreate(
                    [
                        'articulo_id' => $detalle->articulo_id,
                        'bodega_id' => $movimiento->bodega_id,
                        'empresa_id' => $detalle->empresa_id,
                        'unidad_id' => $detalle->unidad_id,
                    ],
                    [
                        'cantidad' => DB::raw("cantidad + {$detalle->cantidad}"),
                        'estado' => 1, // Estado activo
                    ]
                );

                $num_entregados_ahora++;
            }

            // Calcular el nuevo estado del movimiento
            $nuevo_estado = $movimiento->detalles_movimientos->count() ===
                ($movimiento->detalle_entregados->count() + $num_entregados_ahora)
                ? 4 // Estado completado
                : 3; // Estado parcial

            // Actualizar el estado y la observación del movimiento
            $movimiento->update([
                "estado" => $nuevo_estado,
                "observacion_entrega" => $request['observacion_entrega'] ?? null,
                "fecha_entrega" => Carbon::now(),
            ]);

            return $movimiento;
        });
    }

    /**
     * Rellenar datos del movimiento
     */
    private function fillMovimiento($movimiento, $request)
    {
        $movimiento->fill([
            'fecha_emision' => $request["fecha_emision"],
            'tipo_movimiento' => $request["tipo_movimiento"],
            'observacion' => $request["observacion"],
            'observacion_entrega' => $request["observacion_entrega"],
            'destino' => $request["destino"],
            'total' => $request["total"],
            'user_id' => $request["user_id"],
            'bodega_id' => $request["bodega_id"],
            'plantilla_id' => $request["plantilla_id"],
            'proveedor_id' => $request["proveedor_id"],
            'empresa_id' => $request["empresa_id"],
            'sede_id' => $request["sede_id"],
            'estado' => $request["estado"],
            'fecha_entrega' => $request["fecha_entrega"],
        ]);
    }

    /**
     * Sincronizar detalles del movimiento
     */
    private function syncDetalles($movimiento, $detalles)
    {
        $detalle_ids = [];
        foreach ($detalles as $detalle) {
            $detalle_model = DetalleMovimiento::updateOrCreate(
                [
                    "movimiento_id" => $movimiento->id,
                    "articulo_id" => $detalle["articulo"]["id"],
                ],
                [
                    'cantidad' => $detalle["cantidad"],
                    'cantidad_recibida' => $detalle["cantidad_recibida"],
                    'total' => $detalle["total"],
                    'estado' => $detalle["estado"],
                    'unidad_id' => $detalle["unidad"]["id"],
                    'costo' => $detalle["costo"],
                    'empresa_id' => $detalle["empresa_id"],
                    'sede_id' => $detalle["sede_id"],
                    'user_id' => $detalle["user_id"],
                    'fecha_entrega' => $detalle["fecha_entrega"],
                ]
            );
            $detalle_ids[] = $detalle_model->id;
        }
        return $detalle_ids;
    }

    /**
     * Procesar entrada o salida del inventario
     */
    private function procesarMovimiento($movimiento, $tipo_movimiento, $user)
    {
        if ($tipo_movimiento == 2) { // SALIDA
            $result = $this->procesarSalida($movimiento, $user);           
            return $result; // Retorna el resultado de procesar la salida
        } elseif ($tipo_movimiento == 1) { // ENTRADA
            $result = $this->procesarEntrada($movimiento, $user);
            return $result; // Retorna el resultado de procesar la entrada
        }

        // Retorno por defecto en caso de que el tipo de movimiento no sea válido
        return [
            'error' => true,
            'code' => 400,
            'message' => 'Tipo de movimiento no válido.',
        ];
    }

    /**
     * Procesar salida del inventario
     */
    private function procesarSalida($movimiento, $user)
    {
        foreach ($movimiento->detalles_movimientos as $detalle) {
            $bodega_articulo = $this->obtenerBodegaArticulo($movimiento, $detalle);
            
            // Condición: El artículo no existe en la bodega
            if (!$bodega_articulo) {
                return [
                    'error' => true,
                    'code' => 403,
                    'message' => 'El producto ' . $detalle->articulo->nombre . ' no cuenta con la disponibilidad.'
                ];
            }
            
            if ($bodega_articulo->cantidad < $detalle->cantidad) {
                
                return [
                    'error' => true,
                    'code' => 403,
                    'message' => 'El producto ' . $detalle->articulo->nombre . ' no cuenta con la disponibilidad suficiente para realizar esta salida.'
                ];
            }
            
            $bodega_articulo->update([
                "cantidad" => $bodega_articulo->cantidad - $detalle->cantidad
            ]);            

            $detalle->update([
                "estado" => 2,
                "user_id" => $user->id,
                "fecha_entrega" => Carbon::now(),
            ]);
            
            return true;
        }
    }

    /**
     * Procesar entrada al inventario
     */
    private function procesarEntrada($movimiento, $user)
    {
        foreach ($movimiento->detalles_movimientos as $detalle) {
            $bodega_articulo = $this->obtenerBodegaArticulo($movimiento, $detalle);

            if (!$bodega_articulo) {
                BodegaArticulo::create([
                    'articulo_id' => $detalle->articulo_id,
                    'bodega_id' => $movimiento->bodega_id,
                    'empresa_id' => $detalle->empresa_id,
                    'unidad_id' => $detalle->unidad_id,
                    'cantidad' => $detalle->cantidad,
                    'estado' => 1, // Estado activo
                ]);
            } else {
                $bodega_articulo->update([
                    "cantidad" => $bodega_articulo->cantidad + $detalle->cantidad
                ]);
            }

            $detalle->update([
                "estado" => 2,
                "user_id" => $user->id,
                "fecha_entrega" => Carbon::now(),
            ]);
        }
    }

    /**
     * Obtener artículo de la bodega
     */
    private function obtenerBodegaArticulo($movimiento, $detalle)
    {
        return BodegaArticulo::where('articulo_id', $detalle->articulo_id)
            ->where('unidad_id', $detalle->unidad_id)
            ->where('bodega_id', $movimiento->bodega_id)
            ->where('empresa_id', $movimiento->empresa_id)
            ->first();
    }

    /**
     * Manejo de errores
     */
    private function generarError($message, $code = 403)
    {
        return [
            'error' => true,
            'code' => $code,
            'message' => $message,
        ];
    }
}
