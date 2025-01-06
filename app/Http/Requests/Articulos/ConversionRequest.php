<?php

namespace App\Http\Requests\Articulos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ConversionRequest extends FormRequest
{
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
        return match ($this->method()) {           
            'POST' => [
                'articulo_id' => 'required',
                'bodega_id' => 'required',
                'unidad_inicio_id' => 'required',
                'unidad_final_id' => 'required',
                'user_id' => 'required',
                'cantidad_inicial' => 'required',
                'cantidad_final' => 'required',
                'cantidad_convertida' => 'required',
                'empresa_id' => 'required',
                'sede_id' => 'required',
                'estado' => 'integer|nullable',
                'descripcion' => 'nullable|max:200',
            ],
            'PUT' => [
                'articulo_id' => 'required',
                'bodega_id' => 'required',
                'unidad_inicio_id' => 'required',
                'unidad_final_id' => 'required',
                'user_id' => 'required',
                'cantidad_inicial' => 'required',
                'cantidad_final' => 'required',
                'cantidad_convertida' => 'required',
                'empresa_id' => 'required',
                'sede_id' => 'required',
                'estado' => 'integer|nullable',
                'descripcion' => 'nullable|max:200',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'articulo_id.required' => 'El articulo es obligatorio',
            'bodega_id.required' => 'La bodega es obligatoria',
            'unidad_inicio_id.required' => 'Es necesario seleccionar una unidad',
            'unidad_final_id.required' => 'La unidad de conversión es requerida',
            'user_id.required' => 'El usuario es requerido',
            'cantidad_inicial.required' => 'Es necesario digitar la cantidad inicial',
            'cantidad_final.required' => 'Es necesario digitar la cantidad final',
            'cantidad_convertida.required' => 'Es necesario digitar la cantidad inicial y la cantidad final',
            'empresa_id.required' => 'La empresa es obligatoria',
            'sede_id.required' => 'La sede es obligatoria',
        ];
    }
}
