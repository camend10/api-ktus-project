<?php

namespace App\Http\Requests\Categorias;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoriaRequest extends FormRequest
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
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('categorias')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'empresa_id' => 'required',
                'imagen' => 'nullable|file|image|max:2048',
                'descripcion' => 'nullable',
                'estado' => 'integer|nullable'
            ],
            'PUT' => [
                'nombre' => [
                    'required',
                    'string',
                    'max:100',
                    Rule::unique('categorias')->ignore($this->id)->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'empresa_id' => 'required',
                'imagen' => 'nullable|file|image|max:2048',
                'descripcion' => 'nullable',
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
