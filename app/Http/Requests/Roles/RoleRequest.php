<?php

namespace App\Http\Requests\Roles;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
                'name' => 'required|string|max:50|unique:roles,name',
                'permissions' => 'array|nullable', // Si es un array opcional
            ],
            'PUT' =>  [
                'name' => 'required|string|max:50|unique:roles,name,' . $this->id,
                'permissions' => 'array|nullable', // Si es un array opcional
            ],
        };
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre es obligatorio',
            'name.string' => 'El nombre debe ser una cadena de caracteres',
            'name.max' => 'El maximo de caracteres del nombre es 50',
            'name.unique' => 'El nombre ya existe',
        ];
    }
}
