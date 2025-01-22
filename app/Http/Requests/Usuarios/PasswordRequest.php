<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PasswordRequest extends FormRequest
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
                'password' => 'required|string|min:8|max:30',
                'newpassword' => 'required|string|min:8|max:30',                
            ],
            'PUT' =>  [
                'password' => 'required|string|min:8|max:30',
                'newpassword' => 'required|string|min:8|max:30', 
            ]
        };
    }

    public function messages(): array
    {
        return [
            'password.required' => 'El password es obligatorio',
            'password.string' => 'El password debe ser una cadena de caracteres',
            'password.max' => 'El maximo de caracteres del password es 30',
            'password.min' => 'El minimo de caracteres del password es 8',
            'newpassword.required' => 'El nuevo password es obligatorio',
            'newpassword.string' => 'El nuevo password es obligatorio debe ser una cadena de caracteres',
            'newpassword.max' => 'El maximo de caracteres del nuevo password es 30',
            'newpassword.min' => 'El minimo de caracteres del nuevo password es 8',
        ];
    }
}
