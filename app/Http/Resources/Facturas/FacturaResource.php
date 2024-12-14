<?php

namespace App\Http\Resources\Facturas;

use App\Http\Resources\Clientes\ClienteResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FacturaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'total_venta' => $this->resource->total_venta,
            'total_descuento' => $this->resource->total_descuento,
            'total_iva' => $this->resource->total_iva,
            'descripcion' => $this->resource->descripcion ?? '',
            'user_id' => $this->resource->user_id,
            'cliente_id' => $this->resource->cliente_id,
            'empresa_id' => $this->resource->empresa_id,
            'sede_id' => $this->resource->sede_id,
            'estado' => $this->resource->estado,
            'segmento_cliente_id' => $this->resource->segmento_cliente_id,
            'sub_total' => $this->resource->sub_total,
            'estado_factura' => $this->resource->estado_factura,
            'estado_pago' => $this->resource->estado_pago,
            'deuda' => $this->resource->deuda,
            'pago_out' => $this->resource->pago_out,
            'fecha_validacion' => $this->resource->fecha_validacion ?? '',
            'fecha_pago_total' => $this->resource->fecha_pago_total ?? '',
            "created_format_at" => $this->resource->created_at ? $this->resource->created_at->format("Y-m-d h:i A") : '',

            'usuario' => $this->resource->usuario,
            'cliente' => new ClienteResource($this->resource->cliente),
            'empresa' => $this->resource->empresa,
            'sede' => $this->resource->sede,
            'segmento' => $this->resource->segmento,
            'detalles' => $this->resource->detalles_facturas,
            'factura_deliverie' => $this->resource->factura_deliverie,
            'factura_pago' => $this->resource->factura_pago,
        ];
    }
}
