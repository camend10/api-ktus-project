<?php

namespace App\Http\Requests\Sedes;

use Illuminate\Foundation\Http\FormRequest;

class SedeRequest extends FormRequest
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
                'codigo' => 'required|string|max:20|unique:sedes,codigo',
                'nombre' => 'required|string|max:100|unique:sedes,nombre',
                'direccion' => 'required',
                'celular' => 'required|integer',
                'telefono' => 'nullable',
                'identificacion_responsable' => 'required|integer',
                'responsable' => 'required',
                'telefono_responsable' => 'required|integer',
                'empresa_id' => 'required',
                'estado' => 'integer|nullable',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
            ],
            'PUT' =>  [
                'codigo' => 'required|string|max:20|unique:sedes,codigo,' . $this->id,
                'nombre' => 'required|string|max:100|unique:sedes,nombre,' . $this->id,
                'direccion' => 'required',
                'celular' => 'required|integer',
                'telefono' => 'nullable',
                'identificacion_responsable' => 'required|integer',
                'responsable' => 'required',
                'telefono_responsable' => 'required|integer',
                'empresa_id' => 'required',
                'estado' => 'integer|nullable',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El codigo es obligatorio',
            'codigo.string' => 'El codigo debe ser una cadena de caracteres',
            'codigo.max' => 'El maximo de caracteres del codigo es 20',
            'codigo.unique' => 'El codigo ya existe',
            'nombre.required' => 'El nombre es obligatorio',
            'nombre.string' => 'El nombre debe ser una cadena de caracteres',
            'nombre.max' => 'El maximo de caracteres del nombre es 100',
            'nombre.unique' => 'El nombre ya existe',
            'celular.required' => 'El celular es obligatorio',
            'celular.integer' => 'El celular debe contener solo numeros',
            'direccion.required' => 'La dirección es obligatoria',
            'identificacion_responsable.required' => 'La identificación del responsable es obligatoria',
            'identificacion_responsable.integer' => 'La identificación del responsable debe contener solo numeros',
            'responsable.required' => 'El responsable es obligatorio',
            'telefono_responsable.required' => 'El telefono del responsable es obligatorio',
            'telefono_responsable.integer' => 'El telefono del responsable debe contener solo numeros',
            'empresa_id.required' => 'La empresa es obligatoria',
            'departamento_id.required' => 'El departamento es obligatorio',
            'municipio_id.required' => 'El municipio es obligatorio',
        ];
    }
}
