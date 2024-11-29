<?php

namespace App\Http\Requests\Proveedores;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProveedorRequest extends FormRequest
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
        return match ($this->route('id') ? 'PUT' : $this->method()) {
            'POST' => [
                'nombres' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('proveedores')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })
                ],
                'tipo_identificacion' => 'required',
                'identificacion' => [
                    'required',
                    Rule::unique('proveedores')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })
                ],
                'dv' => 'nullable',
                'apellidos' => 'nullable',
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('proveedores')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);;
                    })
                ],
                'direccion' => 'nullable',
                'celular' => 'required',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
                'empresa_id' => 'required',
                'imagen' => 'nullable|file|image|max:2048',
                'estado' => 'integer|nullable'
            ],
            'PUT' => [                
                'nombres' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('proveedores')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })->ignore($this->id)
                ],
                'tipo_identificacion' => 'required',
                'identificacion' => [
                    'required',
                    Rule::unique('proveedores')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })->ignore($this->id)
                ],
                'dv' => 'nullable',
                'apellidos' => 'nullable',
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('proveedores')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);;
                    })->ignore($this->id)
                ],
                'direccion' => 'nullable',
                'celular' => 'required',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
                'empresa_id' => 'required',
                'imagen' => 'nullable|file|image|max:2048',
                'estado' => 'integer|nullable'
            ],
        };
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre ó razón social es obligatorio',
            'nombres.string' => 'El nombre ó razón social debe ser una cadena de caracteres',
            'nombres.max' => 'El máximo de caracteres del nombre ó razón social es 100',
            'nombres.unique' => 'Ya existe un registro con este ó razón social para la empresa seleccionada',
            'tipo_identificacion.required' => 'El tipo de identificación es obligatoria',
            'identificacion.required' => 'La identificación es obligatoria',
            'identificacion.unique' => 'Ya existe un registro con esta identificación para la empresa seleccionada',
            'email.email' => 'Correo no valido',
            'email.required' => 'El correo es obligatorio',
            'email.max' => 'El maximo de caracteres del email es 100',
            'email.unique' => 'Ya existe un registro con este correo para la empresa seleccionada',
            'celular.required' => 'El celular es obligatorio',
            'departamento_id.required' => 'El departamento es obligatorio',
            'municipio_id.required' => 'El municipio es obligatorio',
            'empresa_id.required' => 'La empresa es obligatoria',
            'empresa_id.required' => 'La empresa es obligatoria',
            
        ];
    }
}
