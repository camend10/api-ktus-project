<?php

namespace App\Http\Resources\Articulo;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticuloResource extends JsonResource
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
            'sku' => $this->resource->sku,
            'nombre' => $this->resource->nombre,
            'descripcion' => $this->resource->descripcion ?? '',
            'precio_general' => $this->resource->precio_general,
            'punto_pedido' => $this->resource->punto_pedido,
            'tipo' => $this->resource->tipo,
            'imagen' => $this->resource->imagen != 'SIN-IMAGEN' ? env("APP_URL") . "storage/" . $this->resource->imagen : env("APP_URL") . "storage/articulos/blank-image.svg",
            'iva_id' => $this->resource->iva_id ?? 9999999,
            'empresa_id' => $this->resource->empresa_id,
            'estado' => $this->resource->estado ?? 9999999,
            'especificaciones' => is_string($this->resource->especificaciones)
                ? json_decode($this->resource->especificaciones, true)
                : ($this->resource->especificaciones ?? []),
            'categoria_id' => $this->resource->categoria_id ?? 9999999,
            'is_gift' => $this->resource->is_gift ?? 1,
            'descuento_maximo' => $this->resource->descuento_maximo ?? 0,
            'descuento_minimo' => $this->resource->descuento_minimo ?? 0,
            'tiempo_de_abastecimiento' => $this->resource->tiempo_de_abastecimiento ?? 0,
            'disponibilidad' => $this->resource->disponibilidad ?? 9999999,
            'peso' => $this->resource->peso ?? 0,
            'ancho' => $this->resource->ancho ?? 0,
            'alto' => $this->resource->alto ?? 0,
            'largo' => $this->resource->largo ?? 0,
            'user_id' => $this->resource->user_id,
            'punto_pedido_unidad_id' => $this->resource->punto_pedido_unidad_id ?? 9999999,
            'is_discount' => $this->resource->is_discount ?? 1,
            'impuesto' => $this->resource->impuesto ?? 9999999,
            'proveedor_id' => $this->resource->proveedor_id ?? 9999999,
            'iva' => $this->resource->iva ? $this->resource->iva : null,
            'empresa' => $this->resource->empresa,
            'categoria' => $this->resource->categoria,
            'usuario' => $this->resource->usuario,
            'unidad_punto_pedido' => $this->resource->unidad_punto_pedido ? $this->resource->unidad_punto_pedido : null,
            'proveedor' => $this->resource->proveedor ? $this->resource->proveedor : null,
            'bodegas_articulos' => $this->resource->bodegas_articulos->map(function ($bodega) {
                return [
                    "id" => $bodega->id,
                    "unidad" => $bodega->unidad,
                    "bodega" => $bodega->bodega,
                    "cantidad" => $bodega->cantidad
                ];
            }),
            'articulos_wallets' => $this->resource->articulos_wallets->map(function ($wallet) {
                return [
                    "id" => $wallet->id,
                    "unidad" => $wallet->unidad,
                    "sede" => $wallet->sede,
                    "segmento_cliente" => $wallet->segmento_cliente,
                    "precio" => $wallet->precio,
                    "sede_id_premul" => $wallet->sede ? $wallet->sede->id : null,
                    "segmento_cliente_id_premul" => $wallet->segmento_cliente ? $wallet->segmento_cliente->id : null,
                ];
            }),
        ];
    }
}
