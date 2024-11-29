<?php

namespace App\Http\Requests\MetodoPagos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MetodoPagoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepara los datos antes de la validación.
     */
    protected function prepareForValidation()
    {
        // Convertir 9999999 a NULL
        if (empty($this->metodo_pago_id) || $this->metodo_pago_id == 9999999) {
            $this->merge(['metodo_pago_id' => null]);
        }
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
                    Rule::unique('metodo_pago')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'empresa_id' => 'required',
                'metodo_pago_id' => 'integer|nullable',
                'estado' => 'integer|nullable'
            ],
            'PUT' => [
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('metodo_pago')->ignore($this->id)->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'empresa_id' => 'required',
                'metodo_pago_id' => 'integer|nullable',
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
            'empresa_id.required' => 'La empresa es obligatoria'
        ];
    }
}
