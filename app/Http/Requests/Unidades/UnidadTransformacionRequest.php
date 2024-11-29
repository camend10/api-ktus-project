<?php

namespace App\Http\Requests\Unidades;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UnidadTransformacionRequest extends FormRequest
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
                'unidad_to_id' => [
                    'required',
                    Rule::unique('unidad_transformacion')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id)
                            ->where('unidad_id', $this->unidad_id);
                    })
                ],
                'unidad_id' => 'required',
                'empresa_id' => 'required',
                'estado' => 'integer|nullable'
            ],
        };
    }

    public function messages(): array
    {
        return [
            'unidad_to_id.required' => 'La unidad es obligatoria',
            'unidad_to_id.unique' => 'Ya existe un registro con esta unidad de transformación para la empresa seleccionada',
            'empresa_id.required' => 'La empresa es obligatoria',
            'unidad_id.required' => 'La unidad seleccionada es obligatoria',
        ];
    }
}
