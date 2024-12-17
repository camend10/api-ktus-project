<?php

namespace App\Http\Resources\Movimientos;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SolicitudResource extends JsonResource
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
            'fecha_emision' => Carbon::parse($this->resource->fecha_emision)->format('Y-m-d'),
            'tipo_movimiento' => $this->resource->tipo_movimiento,
            'observacion' => $this->resource->observacion ?? '',
            'observacion_entrega' => $this->resource->observacion_entrega ?? '',
            'destino' => $this->resource->destino,
            'total' => $this->resource->total,
            'user_id' => $this->resource->user_id,
            'bodega_id' => $this->resource->bodega_id,
            'plantilla_id' => $this->resource->plantilla_id,
            'proveedor_id' => $this->resource->proveedor_id,
            'empresa_id' => $this->resource->empresa_id,
            'sede_id' => $this->resource->sede_id,
            'estado' => $this->resource->estado,
            'fecha_entrega' => $this->resource->fecha_entrega ? Carbon::parse($this->resource->fecha_entrega)->format('Y-m-d h:i A') : NULL,

            "created_format_at" => $this->resource->created_at ? $this->resource->created_at->format("Y-m-d h:i A") : '',

            'usuario' => $this->resource->usuario,
            'empresa' => $this->resource->empresa,
            'sede' => $this->resource->sede,
            'bodega' => $this->resource->bodega,
            'proveedor' => $this->resource->proveedor,
            'detalles_movimientos' => $this->resource->detalles_movimientos,
        ];
    }
}
