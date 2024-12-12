<?php

namespace App\Services\Facturas;

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
            return Factura::FilterAdvance($data)
                ->where("empresa_id", $user->empresa_id)
                ->where("sede_id", $user->sede_id)
                ->orderBy("id", "desc")
                ->paginate(20);
        } else {
            return Factura::FilterAdvance($data)
                ->where("empresa_id", $user->empresa_id)
                ->orderBy("id", "desc")
                ->paginate(20);
        }
    }

    public function getAllFacturas($data)
    {

        return Factura::FilterAdvance($data)
            ->where("estado", 1)
            ->where("empresa_id", $data["empresa_id"])
            ->orderBy("id", "desc")
            ->get();
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

        $resp = Factura::findOrFail($id);

        $resp->update($request);

        return $resp;
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
        return Factura::findOrFail($id);
    }
}
