<?php

namespace App\Http\Resources\Clientes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
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
            'tipo_identificacion' => $this->resource->tipo_identificacion,
            'identificacion' => $this->resource->identificacion,
            'dv' => $this->resource->dv,
            'nombres' => $this->resource->nombres,
            'apellidos' => $this->resource->apellidos ?? '',
            'email' => $this->resource->email ?? '',
            'direccion' => $this->resource->direccion ?? '',
            'celular' => $this->resource->celular,
            'departamento_id' => $this->resource->departamento_id,
            'municipio_id' => $this->resource->municipio_id,
            'empresa_id' => $this->resource->empresa_id,
            'sede_id' => $this->resource->sede_id ?? 9999999,
            'estado' => $this->resource->estado,
            'fecha_nacimiento' => $this->resource->fecha_nacimiento ? Carbon::parse($this->resource->fecha_nacimiento)->format('Y-m-d'): '',
            'user_id' => $this->resource->user_id,
            'is_parcial' => $this->resource->is_parcial,
            'genero_id' => $this->resource->genero_id,
            'segmento_cliente_id' => $this->resource->segmento_cliente_id,
            "created_format_at" => $this->resource->created_at ? $this->resource->created_at->format("Y-m-d h:i A") : '',

            'empresa' => $this->resource->empresa,
            'sigla' => $this->resource->tipodocumento->sigla,
            'tipodocumento' => $this->resource->tipodocumento,
            'departamento' => $this->resource->departamento,
            'municipio' => $this->resource->municipio,
            'sede' => $this->resource->sede,
            'usuario' => $this->resource->usuario,
            'segmento' => $this->resource->segmento,
            'genero' => $this->resource->genero,
        ];
    }
}
