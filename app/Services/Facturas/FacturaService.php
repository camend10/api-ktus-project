<?php

namespace App\Services\Facturas;

use App\Models\Articulos\BodegaArticulo;
use App\Models\Facturas\DetalleFactura;
use App\Models\Facturas\Factura;
use App\Models\Facturas\FacturaDeliverie;
use App\Models\Facturas\FacturaPago;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpException;

class FacturaService
{
    public function getByFilter($data)
    {
        $user = auth("api")->user();

        if (!$user) {
            return false;
        }

        if ($user && !in_array($user->role_id, [1, 2])) {
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
                ->FilterAdvance($data)
                ->where("empresa_id", $user->empresa_id)
                ->where("sede_id", $user->sede_id)
                ->where("estado", 1)
                ->orderBy("id", "desc")
                ->paginate(20);
        } else {
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
                ->FilterAdvance($data)
                ->where("empresa_id", $user->empresa_id)
                ->orderBy("id", "desc")
                ->paginate(20);
        }
    }

    public function getAllFacturas($data)
    {

        if ($data && !in_array($data["role_id"], [1, 2])) {
            return Factura::with([
                'empresa',
                'sede',
                'usuario',
                'cliente',
                'segmento',
                'detalles_facturas',
                'factura_deliverie.sede_deliverie',
                'factura_pago.metodo_pago'
            ])
                ->FilterAdvance($data)
                ->where("estado", 1)
                ->where('empresa_id', $data["empresa_id"])
                ->where('sede_id', $data["sede_id"])
                ->orderBy("id", "desc")
                ->get();
        } else {
            return Factura::with([
                'empresa',
                'sede',
                'usuario',
                'cliente',
                'segmento',
                'detalles_facturas',
                'factura_deliverie.sede_deliverie',
                'factura_pago.metodo_pago'
            ])
                ->FilterAdvance($data)
                ->where("estado", 1)
                ->where('empresa_id', $data["empresa_id"])
                ->orderBy("id", "desc")
                ->get();
        }
    }

    public function getAllDetallesFacturas($data)
    {

        if ($data && !in_array($data["role_id"], [1, 2])) {
            return DetalleFactura::whereHas('factura', function ($q) use ($data) {
                $q->FilterAdvance($data); // Filtro avanzado aplicado a la factura
            })
                ->with([
                    'factura' => function ($query) {
                        $query->with([
                            'empresa',
                            'sede',
                            'usuario',
                            'cliente',
                            'segmento',
                            'detalles_facturas',
                            'factura_deliverie.sede_deliverie',
                            'factura_pago.metodo_pago'
                        ]);
                    }
                ])
                ->where('estado', 1)
                ->where('empresa_id', $data["empresa_id"])
                ->where('sede_id', $data["sede_id"])
                ->orderBy('id', 'desc')
                ->get();
        } else {
            return DetalleFactura::whereHas('factura', function ($q) use ($data) {
                $q->FilterAdvance($data); // Filtro avanzado aplicado a la factura
            })
                ->with([
                    'factura' => function ($query) {
                        $query->with([
                            'empresa',
                            'sede',
                            'usuario',
                            'cliente',
                            'segmento',
                            'detalles_facturas',
                            'factura_deliverie.sede_deliverie',
                            'factura_pago.metodo_pago'
                        ]);
                    }
                ])
                ->where('estado', 1)
                ->where('empresa_id', $data["empresa_id"])
                ->orderBy('id', 'desc')
                ->get();
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

            $factura = Factura::create([
                "total_venta" => $request["total_venta"],
                "total_descuento" => $request["total_descuento"],
                "total_iva" => $request["total_iva"],
                "descripcion" => $request["descripcion"],
                "user_id" => $user->id,
                "cliente_id" => $request["cliente_id"],
                "empresa_id" => $user->empresa_id,
                "sede_id" => $user->sede_id,
                "estado" => 1,
                "segmento_cliente_id" => $request["segmento_cliente_id"],
                "sub_total" => $request["sub_total"],
                "deuda" => $request["deuda"],
                "pago_out" => $request["pago_out"],
                "estado_pago" => 3,
            ]);


            $detalle_factura = $request["detalle_factura"] ?? [];

            foreach ($detalle_factura as $detalle) {
                DetalleFactura::create([
                    "precio_item" => $detalle["precio_item"],
                    "total_precio" => $detalle["total_precio"],
                    "total_iva" => $detalle["total_iva"],
                    "cantidad_item" => $detalle["cantidad_item"],
                    "factura_id" => $factura->id,
                    "articulo_id" => $detalle["articulo"]["id"],
                    "iva_id" => $detalle["iva_id"],
                    "empresa_id" => $user->empresa_id,
                    "sede_id" => $user->sede_id,
                    "estado" => 1,
                    "categoria_id" => $detalle["articulo"]["categoria_id"],
                    "descuento" => $detalle["descuento"],
                    "sub_total" => $detalle["sub_total"],
                    "unidad_id" => $detalle["unidad_id"],
                    "total_descuento" => $detalle["total_descuento"],
                ]);

                // Procesar la salida del inventario
                $result = $this->procesarSalida($detalle, $user);

                // Si hay un error en el proceso de salida, revierte la transacción
                if (isset($result['error']) && $result['error']) {
                    DB::rollBack();
                    return $result;
                }
            }

            if (isset($request["sede_deliverie_id"])) {
                if ($request['sede_deliverie_id'] != 9999999) {
                    FacturaDeliverie::create([
                        "sede_deliverie_id" => $request['sede_deliverie_id'],
                        "factura_id" => $factura->id,
                        "fecha_entrega" => $request["fecha_entrega"],
                        "direccion" => $request["direccion_deliverie"],
                        "empresa_id" => $user->empresa_id,
                        "sede_id" => $user->sede_id,
                        "estado" => 1,
                        "fecha_envio" => Carbon::parse($request["fecha_entrega"])->subDay(2),
                        "departamento_id" => $request["departamento_id"],
                        "municipio_id" => $request["municipio_id"],

                        "agencia" => $request["agencia_deliverie"],
                        "encargado" => $request["encargado_deliverie"],
                        "documento" => $request["documento_deliverie"],
                        "celular" => $request["celular_deliverie"],
                    ]);
                }
            }

            FacturaPago::create([
                "monto" => $request["monto_pago"],
                "metodo_pago_id" => $request["metodo_pago_id"],
                "banco_id" => $request["banco_id"],
                "imagen" => $request["imagen"],
                "factura_id" => $factura->id,
                "empresa_id" => $user->empresa_id,
                "sede_id" => $user->sede_id,
                "estado" => 1
            ]);

            // Confirma la transacción
            DB::commit();

            return $factura;
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
            $factura = $id ? Factura::findOrFail($id) : new Factura();

            // Asignar datos comunes a la factura
            $factura->fill([
                "total_venta" => $request["total_venta"],
                "total_descuento" => $request["total_descuento"],
                "total_iva" => $request["total_iva"],
                "descripcion" => $request["descripcion"],
                "user_id" => $user->id,
                "cliente_id" => $request["cliente_id"],
                "empresa_id" => $user->empresa_id,
                "sede_id" => $user->sede_id,
                "estado" => 1,
                "segmento_cliente_id" => $request["segmento_cliente_id"],
                "sub_total" => $request["sub_total"],
                "deuda" => $request["deuda"],
                "pago_out" => $request["pago_out"],
                "estado_pago" => 3,
            ]);

            $factura->save();

            // Sincronizar detalles de factura
            $detalle_factura = $request["detalle_factura"] ?? [];
            // Log::error('Error al crear la factura: ' . json_encode($detalle_factura));

            $detalle_ids = [];

            foreach ($detalle_factura as $detalle) {
                $detalle_model = DetalleFactura::updateOrCreate(
                    [
                        "factura_id" => $factura->id,
                        "articulo_id" => $detalle["articulo"]["id"],
                    ],
                    [
                        "precio_item" => $detalle["precio_item"],
                        "total_precio" => $detalle["total_precio"],
                        "total_iva" => $detalle["total_iva"],
                        "cantidad_item" => $detalle["cantidad_item"],
                        "iva_id" => $detalle["iva_id"],
                        "empresa_id" => $user->empresa_id,
                        "sede_id" => $user->sede_id,
                        "estado" => 1,
                        "categoria_id" => $detalle["articulo"]["categoria_id"],
                        "descuento" => $detalle["descuento"],
                        "sub_total" => $detalle["sub_total"],
                        "unidad_id" => $detalle["unidad_id"],
                        "total_descuento" => $detalle["total_descuento"],
                    ]
                );
                $detalle_ids[] = $detalle_model->id;
            }

            // Eliminar registros que no estén en los nuevos detalles
            DetalleFactura::where("factura_id", $factura->id)
                ->whereNotIn("id", $detalle_ids)
                ->delete();

            // Manejar FacturaDeliverie
            if (isset($request["sede_deliverie_id"])) {
                if ($request['sede_deliverie_id'] != 9999999) {
                    FacturaDeliverie::updateOrCreate(
                        ["factura_id" => $factura->id],
                        [
                            "sede_deliverie_id" => $request['sede_deliverie_id'],
                            "fecha_entrega" => $request["fecha_entrega"],
                            "direccion" => $request["direccion_deliverie"],
                            "empresa_id" => $user->empresa_id,
                            "sede_id" => $user->sede_id,
                            "estado" => 1,
                            "fecha_envio" => Carbon::parse($request["fecha_entrega"])->subDay(2),
                            "departamento_id" => $request["departamento_id"],
                            "municipio_id" => $request["municipio_id"],
                            "agencia" => $request["agencia_deliverie"],
                            "encargado" => $request["encargado_deliverie"],
                            "documento" => $request["documento_deliverie"],
                            "celular" => $request["celular_deliverie"],
                        ]
                    );
                }
            }

            // Manejar FacturaPago
            FacturaPago::updateOrCreate(
                ["factura_id" => $factura->id],
                [
                    "monto" => $request["monto_pago"],
                    "metodo_pago_id" => $request["metodo_pago_id"],
                    "banco_id" => $request["banco_id"],
                    "imagen" => $request["imagen"],
                    "empresa_id" => $user->empresa_id,
                    "sede_id" => $user->sede_id,
                    "estado" => 1,
                ]
            );

            // Confirmar transacción
            DB::commit();

            return $factura;
        } catch (\Throwable $e) {
            // Revierte la transacción en caso de error
            DB::rollBack();
            Log::error('Error al crear o actualizar la factura: ' . $e->getMessage());
            throw new HttpException(500, $e->getMessage());
        }
    }

    public function cambiarEstado($request, $id)
    {
        $resp = Factura::findOrFail($id);
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
        return Factura::with([
            'empresa',
            'sede',
            'usuario',
            'cliente',
            'segmento',
            'detalles_facturas.articulo',
            'detalles_facturas.iva',
            'detalles_facturas.unidad',
            'detalles_facturas.categoria',
            'factura_deliverie.sede_deliverie',
            'factura_pago.metodo_pago',
            'factura_pago.banco'
        ])->findOrFail($id);
    }

    public function deleteDetalle($id)
    {
        $detalle = DetalleFactura::findOrFail($id);

        $detalle->delete();
    }


    /**
     * Procesar salida del inventario
     */
    private function procesarSalida($detalle, $user)
    {
        $bodega_articulo = BodegaArticulo::where('articulo_id', $detalle["articulo"]["id"])
            ->where('unidad_id', $detalle["unidad_id"])
            ->where('bodega_id', $user->sede_id)
            ->where('empresa_id', $user->empresa_id)
            ->first();

        if (!$bodega_articulo) {
            return [
                'error' => true,
                'code' => 403,
                'message' => 'El producto ' . $detalle["articulo"]["nombre"] . ' no está disponible en inventario.'
            ];
        }

        if ($bodega_articulo->cantidad < $detalle["cantidad_item"]) {
            return [
                'error' => true,
                'code' => 403,
                'message' => 'El producto ' . $detalle["articulo"]["nombre"] . ' no tiene suficiente cantidad en inventario.'
            ];
        }

        $bodega_articulo->update([
            "cantidad" => $bodega_articulo->cantidad - $detalle["cantidad_item"]
        ]);

        return ['error' => false];
    }
}
