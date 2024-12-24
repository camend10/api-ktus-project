<?php

namespace App\Http\Requests\Movimientos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlantillaRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'detalles_plantillas' => is_string($this->detalles_plantillas) ? json_decode($this->detalles_plantillas, true) : $this->detalles_plantillas,
        ]);
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
                'codigo' => [
                    'required',
                    Rule::unique('plantillas')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'nombre' => [
                    'required',
                    Rule::unique('plantillas')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    }),
                ],
                'observacion' => 'nullable|max:200',
                'empresa_id' => 'required|integer',
                'sede_id' => 'required|integer',
                'estado' => 'required|integer',
                'user_id' => 'required|integer',

                'detalles_plantillas' => 'required|array',
            ],
            'PUT' => [
                'codigo' => [
                    'required',
                    Rule::unique('plantillas')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })->ignore($this->id), // Ignorar el ID actual al validar la unicidad
                ],
                'nombre' => [
                    'required',
                    Rule::unique('plantillas')->where(function ($query) {
                        return $query->where('empresa_id', $this->empresa_id);
                    })->ignore($this->id), // Ignorar el ID actual al validar la unicidad
                ],
                'observacion' => 'nullable|max:200',
                'empresa_id' => 'required|integer',
                'sede_id' => 'required|integer',
                'estado' => 'required|integer',
                'user_id' => 'required|integer',

                'detalles_plantillas' => 'required|array',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'codigo.required' => 'El codigo es obligatorio.',
            'codigo.unique' => 'El código ya existe para esta empresa.',
            'nombre.required' => 'El codigo es obligatorio.',
            'nombre.unique' => 'El nombre ya existe para esta empresa.',
            'empresa_id.required' => 'La empresa es obligatoria.',
            'sede_id.required' => 'La sede es obligatoria.',
            'sede_id.required' => 'El estado es obligatorio.',
            'estado.integer' => 'El estado debe ser un número entero.',
            'user_id.required' => 'El usuario es obligatorio.',
            'empresa_id.integer' => 'El ID de la empresa debe ser un número entero.',
            'sede_id.integer' => 'El ID de la sede debe ser un número entero.',
            'estado.integer' => 'El estado debe ser un número entero.',
            'user_id.integer' => 'El ID del usuario debe ser un número entero.',

            'detalles_plantillas.required' => 'Necesitas ingresar al menos un articulo al detalle',
        ];
    }
}
