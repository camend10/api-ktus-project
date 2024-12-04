<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ClienteRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        // Registra los datos que llegan al request
        // Log::info('Datos recibidos en ClienteRequest:', $this->all());

        // Si necesitas detener la ejecución para depurar:
        // dd($this->all());
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
                'nombres' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('clientes')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })
                ],
                'tipo_identificacion' => 'required',
                'identificacion' => [
                    'required',
                    Rule::unique('clientes')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })
                ],
                'dv' => 'nullable',
                'apellidos' => 'nullable',
                'email' => [
                    'nullable',
                    'email',
                    'max:100',
                    Rule::unique('clientes')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                        ;
                    })
                ],
                'direccion' => 'nullable',
                'celular' => 'required',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
                'empresa_id' => 'required',
                'sede_id' => 'integer|nullable',
                'estado' => 'integer|nullable',
                // 'fecha_nacimiento' => 'nullable',
                'fecha_nacimiento' => 'nullable|date_format:Y-m-d',
                'user_id' => 'integer|nullable',
                'is_parcial' => 'integer|nullable',
                'genero_id' => 'integer|nullable',
                'segmento_cliente_id' => 'required',
            ],
            'PUT' => [
                'nombres' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('clientes')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })->ignore($this->id)
                ],
                'tipo_identificacion' => 'required',
                'identificacion' => [
                    'required',
                    Rule::unique('clientes')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })->ignore($this->id)
                ],
                'dv' => 'nullable',
                'apellidos' => 'nullable',
                'email' => [
                    'nullable',
                    'email',
                    'max:100',
                    Rule::unique('clientes')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                        ;
                    })->ignore($this->id)
                ],
                'direccion' => 'nullable',
                'celular' => 'required',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
                'empresa_id' => 'required',
                'sede_id' => 'integer|nullable',
                'estado' => 'integer|nullable',
                // 'fecha_nacimiento' => 'nullable',
                'fecha_nacimiento' => 'nullable|date_format:Y-m-d',
                'user_id' => 'integer|nullable',
                'is_parcial' => 'integer|nullable',
                'genero_id' => 'integer|nullable',
                'segmento_cliente_id' => 'required',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'nombres.required' => 'El nombre ó razón social es obligatorio',
            'nombres.string' => 'El nombre ó razón social debe ser una cadena de caracteres',
            'nombres.max' => 'El máximo de caracteres del nombre ó razón social es 100',
            'nombres.unique' => 'Ya existe un registro con este nombre ó razón social para la empresa seleccionada',
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
            'segmento_cliente_id.required' => 'El segmento de cliente es obligatorio',
            'date_format' => 'El campo :attribute no coincide con el formato :format.',


        ];
    }
}
