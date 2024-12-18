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

            // Determina si es una actualización o creación
            $movimiento = $id ? Movimiento::findOrFail($id) : new Movimiento();

            // Asignar datos comunes a la factura
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

            $movimiento->save();

            // Sincronizar detalles de factura
            $detalles = $request["detalles_movimientos"] ?? [];
            // Log::error('Error al crear la factura: ' . json_encode($detalle_factura));

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

            // Eliminar registros que no estén en los nuevos detalles
            DetalleMovimiento::where("movimiento_id", $movimiento->id)
                ->whereNotIn("id", $detalle_ids)
                ->delete();

            // Confirmar transacción
            DB::commit();

            return $movimiento;
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
        return Movimiento::with([
            'empresa',
            'sede',
            'usuario',
            'proveedor',
            'bodega',
            'detalles_movimientos.articulo',
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
}
