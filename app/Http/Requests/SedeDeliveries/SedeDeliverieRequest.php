<?php

namespace App\Http\Requests\SedeDeliveries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SedeDeliverieRequest extends FormRequest
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
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('sede_deliveries')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'direccion' => 'nullable',
                'empresa_id' => 'required',
                'sede_id' => 'required',
                'estado' => 'integer|nullable'
            ],
            'PUT' => [
                'nombre' => [
                    'required',
                    'string',
                    'max:200',
                    Rule::unique('sede_deliveries')->ignore($this->id)->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'direccion' => 'nullable',
                'empresa_id' => 'required',
                'sede_id' => 'required',
                'estado' => 'integer|nullable'
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
            'empresa_id.required' => 'La empresa es obligatoria',
            'sede_id.required' => 'La sede es obligatoria',
        ];
    }
}
