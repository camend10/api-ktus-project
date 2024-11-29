<?php

namespace App\Http\Requests\Articulos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticuloRequest extends FormRequest
{
    protected function prepareForValidation()
    {

        $this->merge([
            'bodegas_articulos' => is_string($this->bodegas_articulos) ? json_decode($this->bodegas_articulos, true) : $this->bodegas_articulos,
            'articulos_wallets' => is_string($this->articulos_wallets) ? json_decode($this->articulos_wallets, true) : $this->articulos_wallets,
            'especificaciones' => is_string($this->especificaciones) ? json_decode($this->especificaciones, true) : $this->especificaciones,
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return match ($this->route('id') ? 'PUT' : $this->method()) {
            'POST' => [
                'sku' => [
                    'required',
                    'max:25',
                    Rule::unique('articulos')->ignore($this->id)->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('articulos')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'descripcion' => 'nullable|max:200',
                'precio_general' => 'required|numeric',
                'punto_pedido' => 'required|integer',
                'tipo' => 'nullable',
                'imagen' => 'nullable|file|image|max:2048',
                'iva_id' => 'required',
                'empresa_id' => 'required',
                'estado' => 'integer|nullable',
                'especificaciones' => 'nullable',
                'categoria_id' => 'required',
                'is_gift' => 'nullable',
                'descuento_maximo' => 'required|numeric',
                'descuento_minimo' => 'required|numeric',
                'tiempo_de_abastecimiento' => 'required|integer',
                'disponibilidad' => 'required',
                'peso' => 'numeric|nullable',
                'ancho' => 'numeric|nullable',
                'alto' => 'numeric|nullable',
                'largo' => 'numeric|nullable',
                'punto_pedido_unidad_id' => 'required',
                'is_discount' => 'required',
                'impuesto' => 'required',
                'proveedor_id' => 'integer|nullable',
                'bodegas_articulos' => 'required|array',
                'bodegas_articulos.*.bodega.id' => 'required|integer',
                'bodegas_articulos.*.unidad.id' => 'required|integer',
                'bodegas_articulos.*.cantidad' => 'required|numeric|min:0',
                'articulos_wallets' => 'required|array',
                'articulos_wallets.*.unidad.id' => 'required|integer',
                'articulos_wallets.*.precio' => 'required|numeric|min:0',
                'articulos_wallets.*.sede_id_premul' => 'nullable|integer',
                'articulos_wallets.*.segmento_cliente_id_premul' => 'nullable|integer',
            ],
            'PUT' => [
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('categorias')->ignore($this->id)->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'sku' => [
                    'required',
                    'max:25',
                    Rule::unique('articulos')->ignore($this->id)->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'descripcion' => 'nullable|max:200',
                'precio_general' => 'required|numeric',
                'punto_pedido' => 'required|integer',
                'tipo' => 'nullable',
                'imagen' => 'nullable|file|image|max:2048',
                'iva_id' => 'required',
                'empresa_id' => 'required',
                'estado' => 'integer|nullable',
                'especificaciones' => 'nullable',
                'categoria_id' => 'required',
                'is_gift' => 'nullable',
                'descuento_maximo' => 'required',
                'descuento_minimo' => 'required',
                'tiempo_de_abastecimiento' => 'required|integer',
                'disponibilidad' => 'required',
                'peso' => 'numeric|nullable',
                'ancho' => 'numeric|nullable',
                'alto' => 'numeric|nullable',
                'largo' => 'numeric|nullable',
                'punto_pedido_unidad_id' => 'required',
                'is_discount' => 'required',
                'impuesto' => 'required',
                'proveedor_id' => 'integer|nullable',
                // 'bodegas_articulos' => 'required|array',
                // 'bodegas_articulos' => 'required|array',
                'especificaciones' => 'nullable|array',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de caracteres',
            'nombre.max' => 'El máximo de caracteres del nombre es 100',
            'nombre.unique' => 'Ya existe un registro con este nombre para la empresa seleccionada',
            'sku.required' => 'El código es obligatorio',
            'sku.max' => 'El máximo de caracteres del código es 25',
            'sku.unique' => 'Ya existe un registro con este código para la empresa seleccionada',
            'descripcion.max' => 'El máximo de caracteres de la descripción son 200',
            'empresa_id.required' => 'La empresa es obligatoria',
            'precio_general.required' => 'El precio es obligatorio',
            'punto_pedido.required' => 'El punto de pedido es obligatorio',
            'punto_pedido.integer' => 'El punto de pedido debe ser un número entero',
            'descuento_maximo.required' => 'El descuento máximo es obligatorio',
            'descuento_minimo.required' => 'El descuento mínimo es obligatorio',
            'disponibilidad.required' => 'La disponibilidad es obligatoria',
            'iva_id.required' => 'El iva es obligatorio',
            'categoria_id.required' => 'La categoria es obligatoria',
            'punto_pedido_unidad_id.required' => 'La unidad del punto de pedido es obligatoria',
            'is_discount.required' => 'Especifique si el articulo tiene descuento',
            'impuesto.required' => 'Especifique si el articulo tiene impuesto',
            'bodegas_articulos.required' => 'Necesitas ingresar al menos un registro de existencia de articulos',
            'articulos_wallets.required' => 'Necesitas ingresar al menos un listado de precios al articulos',
            'tiempo_de_abastecimiento.required' => 'El tiempo de abastecimiento es obligatorio',
            'tiempo_de_abastecimiento.integer' => 'El tiempo de abastecimiento debe ser un número entero',
        ];
    }
}
