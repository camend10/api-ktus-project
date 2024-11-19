<?php

namespace App\Http\Requests\Empresas;

use Illuminate\Foundation\Http\FormRequest;

class EmpresaRequest extends FormRequest
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
            'POST' =>  [
                'nit_empresa' => 'required|max:20|unique:empresa,nit_empresa',
                'dv' => 'required|integer',
                'nombre' => 'required|string|max:100|unique:empresa,nombre',
                'email' => 'required|email|max:100|unique:empresa,email',
                'direccion' => 'required',
                'telefono' =>  'integer|nullable',
                'celular' => 'required|integer',
                'web' => 'string|nullable',
                'estado' => 'integer|nullable',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
            ],
            'PUT' =>  [
                'nit_empresa' => 'required|max:20|unique:empresa,nit_empresa,' . $this->id,
                'dv' => 'required|integer',
                'nombre' => 'required|string|max:100|unique:empresa,nombre,' . $this->id,
                'email' => 'required|email|max:100|unique:empresa,email,' . $this->id,
                'direccion' => 'required',
                'telefono' =>  'integer|nullable',
                'celular' => 'required|integer',
                'web' => 'string|nullable',
                'estado' => 'integer|nullable',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
            ],
        };
    }


    public function messages(): array
    {
        return [
            'nit_empresa.required' => 'El nit es obligatorio',
            'nit_empresa.max' => 'El maximo de caracteres del nit es 20',
            'nit_empresa.unique' => 'El nit ya existe',
            'dv.required' => 'El dv es obligatorio',
            'dv.integer' => 'El dv debe contener solo numeros',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de caracteres',
            'nombre.max' => 'El maximo de caracteres del nombre es 100',
            'nombre.unique' => 'El nombre ya existe',
            'email.email' => 'Correo no valido',
            'email.required' => 'El correo es obligatorio',
            'email.max' => 'El maximo de caracteres del email es 100',
            'email.unique' => 'El correo ya existe',
            'direccion.required' => 'La dirección es obligatoria',
            'celular.required' => 'El celular es obligatorio',
            'celular.integer' => 'El celular debe contener solo numeros',
            'departamento_id.required' => 'El departamento es obligatorio',
            'municipio_id.required' => 'El municipio es obligatorio',
        ];
    }
}
