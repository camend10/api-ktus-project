<?php

namespace App\Http\Requests\Iva;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IvaRequest extends FormRequest
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
                'porcentaje' => [
                    'required',
                    'numeric',
                    Rule::unique('iva')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })
                ],
                'empresa_id' => 'required',
                'estado' => 'integer|nullable'
            ],
            'PUT' => [
                'porcentaje' => [
                    'required',
                    'numeric',
                    Rule::unique('iva')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })->ignore($this->id) // Ignora el registro actual en caso de actualización
                ],
                'empresa_id' => 'required',
                'estado' => 'integer|nullable'
            ],
        };
    }

    public function messages(): array
    {
        return [
            'porcentaje.required' => 'El porcentaje es obligatorio',
            'porcentaje.numeric' => 'El porcentaje debe ser un valor numerico',
            'porcentaje.unique' => 'Ya existe un registro con este porcentaje para la empresa seleccionada',
            'empresa_id.required' => 'La empresa es obligatoria',
        ];
    }
}
