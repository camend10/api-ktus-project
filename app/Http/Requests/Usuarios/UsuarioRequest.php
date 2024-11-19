<?php

namespace App\Http\Requests\Usuarios;

use Illuminate\Foundation\Http\FormRequest;

class UsuarioRequest extends FormRequest
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
                'tipo_doc_id' => 'required',
                'identificacion' => 'required|max:20|unique:users,identificacion',
                'name' => 'required|string|max:50',
                'email' => 'required|email|max:100|unique:users,email',
                'password' => 'required|string|min:8|max:30',
                'usuario' => 'required|max:20|unique:users,usuario',
                'direccion' => 'string|nullable',
                'celular' => 'required',
                'estado' => 'integer|nullable',
                'empresa_id' => 'required',
                'role_id' => 'required',
                'imagen' => 'nullable|file|image|max:2048',
                'genero_id' => 'required',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
                'fecha_nacimiento' => 'required',
                'sede_id' => 'integer|nullable',
                'sedes' => 'array|nullable', // Si es un array opcional
            ],
            'PUT' =>  [
                'tipo_doc_id' => 'required',
                'identificacion' => 'required|max:20|unique:users,identificacion,' . $this->id,
                'name' => 'required|string|max:50',
                'email' => 'required|email|max:100|unique:users,email,' . $this->id,
                'password' => 'required|string|min:8|max:30',
                'usuario' => 'required|max:20|unique:users,usuario,' . $this->id,
                'direccion' => 'string|nullable',
                'celular' => 'required',
                'estado' => 'integer|nullable',
                'empresa_id' => 'integer',
                'role_id' => 'required',
                'imagen' => 'nullable|file|image|max:2048',
                'genero_id' => 'integer',
                'departamento_id' => 'required',
                'municipio_id' => 'required',
                'fecha_nacimiento' => 'string|nullable',
                'sede_id' => 'integer|nullable',
                'sedes' => 'array|nullable', // Si es un array opcional
            ]
        };
    }

    public function messages(): array
    {
        return [
            'tipo_doc_id.required' => 'El tipo de identificación es obligatorio',
            'identificacion.required' => 'La identificación es obligatoria',
            'identificacion.unique' => 'La identificación ya existe',
            'identificacion.max' => 'El maximo de caracteres de la identificacion es 20',
            'name.required' => 'El nombre es obligatorio',
            'name.string' => 'El nombre debe ser una cadena de caracteres',
            'name.max' => 'El maximo de caracteres del nombre es 50',
            'email.email' => 'Correo no valido',
            'email.required' => 'El correo es obligatorio',
            'email.max' => 'El maximo de caracteres del email es 100',
            'email.unique' => 'El correo ya existe',
            'password.required' => 'La clave es obligatoria',
            // 'password.confirmed' => 'Las claves no coinciden',
            'password.string' => 'La clave debe ser una cadena de caracteres',
            'password.max' => 'El maximo de caracteres de la clave es 30',
            'password.min' => 'El minimo de caracteres de la clave es 8',
            'usuario.required' => 'El nombre de usuario es obligatoria',
            'usuario.unique' => 'El nombre de usuario ya existe',
            'usuario.max' => 'El maximo de caracteres del nombre de usuario es 20',
            // 'password_confirmation.required' => 'Confirmar la clave es obligatorio',
            'celular.required' => 'El celular es obligatorio',
            'role_id.required' => 'El rol es obligatorio',
            'departamento_id.required' => 'El departamento es obligatorio',
            'municipio_id.required' => 'El municipio es obligatorio',
            'empresa_id.required' => 'La empresa es obligatoria',
            'genero_id.required' => 'El genero es obligatorio',
            // 'sede_id.required' => 'La sede es obligatoria',
            'fecha_nacimiento.required' => 'La fecha de nacimiento es obligatoria',
        ];
    }
}
