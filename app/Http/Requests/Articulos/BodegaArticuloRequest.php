<?php

namespace App\Http\Requests\Articulos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BodegaArticuloRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        $user = auth('api')->user();

        $this->merge([
            'empresa_id' => $user->empresa_id, // Agregar 'empresa_id' desde el usuario autenticado
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
        return match ($this->method()) {
            'POST' => [
                'articulo_id' => 'required',
                'bodega_id' => 'required',
                'unidad_id' => 'required',
                'cantidad' => 'required',
                'empresa_id' => 'integer|nullable',
                'estado' => 'integer|nullable'
            ],
            'PUT' => [
                'articulo_id' => [
                    'required',
                    Rule::unique('bodegas_articulos')->where(function ($query) {
                        return $query->where('bodega_id', $this->bodega_id)
                            ->where('unidad_id', $this->unidad_id)
                            ->where('empresa_id', $this->empresa_id);
                    })
                        ->ignore($this->id), // Ignorar el registro actual en caso de edición
                ],
                'bodega_id' => 'required',
                'unidad_id' => 'required',
                'cantidad' => 'required',
                'empresa_id' => 'integer|nullable',
                'estado' => 'integer|nullable'
            ],
        };
    }

    public function messages(): array
    {
        return [
            'articulo_id.required' => 'El articulo es obligatorio',
            'bodega_id.required' => 'La bodega es obligatoria',
            'unidad_id.required' => 'La unidad es obligatoria',
            'cantidad.required' => 'La cantidad es obligatoria',
            'articulo_id.unique' => 'La combinación de bodega, unidad y artículo ya existe en el sistema.',
        ];
    }
}
