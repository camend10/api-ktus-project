<?php

namespace App\Services\Movimientos;

use App\Models\Articulos\BodegaArticulo;
use App\Models\Movimientos\DetalleSolicitud;
use App\Models\Movimientos\Solicitud;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SolicitudService
{

    public function getByFilter($data)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        if ($user && !in_array($user->role_id, [1, 2])) {
            return Solicitud::with([
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
                ->where("estado", 1)
                ->orderBy("id", "desc")
                ->paginate(20);
        } else {
            return Solicitud::with([
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

            $respuesta = Solicitud::create([
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
                DetalleSolicitud::create([
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

            // Determina si es una actualización o creación
            $solicitud = $id ? Solicitud::findOrFail($id) : new Solicitud();

            // Asignar datos comunes a la factura
            $solicitud->fill([
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

            $solicitud->save();

            // Sincronizar detalles de factura
            $detalles = $request["detalles_movimientos"] ?? [];
            // Log::error('Error al crear la factura: ' . json_encode($detalle_factura));

            $detalle_ids = [];

            foreach ($detalles as $detalle) {
                $detalle_model = DetalleSolicitud::updateOrCreate(
                    [
                        "movimiento_id" => $solicitud->id,
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

            // Eliminar registros que no estén en los nuevos detalles
            DetalleSolicitud::where("movimiento_id", $solicitud->id)
                ->whereNotIn("id", $detalle_ids)
                ->delete();

            // Confirmar transacción
            DB::commit();

            return $solicitud;
        } catch (\Throwable $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();
            Log::error('Error al crear o actualizar la factura: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
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
        return Solicitud::with([
            'empresa',
            'sede',
            'usuario',
            'proveedor',
            'bodega',
            'detalles_movimientos.articulo',
            'detalles_movimientos.unidad',
        ])->findOrFail($id);
    }

    public function entrega($request)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        try {
            // Inicia la transacción
            DB::beginTransaction();
            $detalles_movimientos = $request["deta_movi_ids"] ?? [];
            $solicitud = $this->getById($request["movimiento_id"]);

            $num_max_detalle = $solicitud->detalles_movimientos->count();
            $num_detalle_entregados = $solicitud->detalle_entregados->count();
            date_default_timezone_set("America/Bogota");
            $num_entregados_ahora = 0;
            foreach ($solicitud->detalles_movimientos as $key => $detalle) {
                if (in_array($detalle->id, $detalles_movimientos) && !$detalle->fecha_entrega) {
                    $detalle->update([
                        "estado" => 2,
                        "user_id" => $user->id,
                        "fecha_entrega" => now(),
                    ]);
                    $num_entregados_ahora++;

                    $is_existe_bodega = BodegaArticulo::where("articulo_id", $detalle->articulo_id)
                        ->where("unidad_id", $detalle->unidad_id)
                        ->where("bodega_id", $solicitud->bodega_id)
                        ->where("empresa_id", $detalle->empresa_id)
                        ->first();

                    if ($is_existe_bodega) {
                        $is_existe_bodega->update([
                            "cantidad" => $detalle->cantidad + $is_existe_bodega->cantidad
                        ]);
                    } else {
                        BodegaArticulo::create([
                            'articulo_id' => $detalle->articulo_id,
                            'bodega_id' => $solicitud->bodega_id,
                            'cantidad' => $detalle->cantidad,
                            'empresa_id' => $detalle->empresa_id,
                            'estado' => 1,
                            'unidad_id' =>  $detalle->unidad_id,
                        ]);
                    }
                }
            }

            if ($num_max_detalle == ($num_detalle_entregados + $num_entregados_ahora)) {
                $solicitud->update([
                    "estado" => 4,
                    "fecha_entrega" => now()
                ]);
            } else {
                $solicitud->update([
                    "estado" => 3,
                    "fecha_entrega" => now()
                ]);
            }
            // Confirmar transacción
            DB::commit();

            return $solicitud;
        } catch (\Throwable $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();
            Log::error('Error al crear o actualizar la factura: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
        }
    }
}
