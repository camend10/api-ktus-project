<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmailRequest extends FormRequest
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
        $method = $this->method();

        if ($method === 'POST' && !$this->route('id')) {
            $method = 'POST';
        } elseif ($this->route('id')) {
            $method = 'PUT';
        }

        return match ($method) {

            'POST' => [
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('users')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ]
            ],
            'PUT' =>  [
                'email' => [
                    'required',
                    'email',
                    'max:100',
                    Rule::unique('users')->ignore($this->id)->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ]
            ]
        };
    }

    public function messages(): array
    {
        return [
            'email.email' => 'Correo no valido',
            'email.required' => 'El correo es obligatorio',
            'email.max' => 'El maximo de caracteres del email es 100',
            'email.unique' => 'Ya existe un registro con este correo para la empresa seleccionada'
        ];
    }
}
