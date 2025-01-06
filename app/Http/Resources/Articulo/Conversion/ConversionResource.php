<?php

namespace App\Http\Resources\Articulo\Conversion;

use App\Http\Resources\Articulo\ArticuloResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConversionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->resource->id,
            "articulo_id" => $this->resource->articulo_id,
            'articulo' => new ArticuloResource($this->resource->articulo),
            "bodega_id" => $this->resource->bodega_id,
            "bodega" => $this->resource->bodega,
            "unidad_inicio_id" => $this->resource->unidad_inicio_id,
            "unidad_inicio" => $this->resource->unidad_inicio,
            "unidad_final_id" => $this->resource->unidad_final_id,
            "unidad_final" => $this->resource->unidad_final,
            "user_id" => $this->resource->user_id,
            "usuario" => $this->resource->usuario,
            "empresa_id" => $this->resource->empresa_id,
            "empresa" => $this->resource->empresa,
            "sede_id" => $this->resource->sede_id,
            "sede" => $this->resource->sede,
            "estado" => $this->resource->estado,
            "cantidad_inicial" => $this->resource->cantidad_inicial,
            "cantidad_final" => $this->resource->cantidad_final,
            "cantidad_convertida" => $this->resource->cantidad_convertida,
            "descripcion" => $this->resource->descripcion,
            "created_format_at" => $this->resource->created_at ? $this->resource->created_at->format("Y-m-d h:i A") : '',
        ];
    }
}
