<?php

namespace App\Http\Requests\Solicitudes;

use Illuminate\Foundation\Http\FormRequest;

class SolicitudRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        $this->merge([
            'detalles_movimientos' => is_string($this->detalles_movimientos) ? json_decode($this->detalles_movimientos, true) : $this->detalles_movimientos,
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
                'fecha_emision' => 'required|date',
                'tipo_movimiento' => 'required|integer',
                'observacion' => 'nullable|max:200',
                'observacion_entrega' => 'nullable|max:200',
                'destino' => 'required|string',                
                'total' => 'required|numeric',
                'user_id' => 'required|integer',
                'bodega_id' => 'required|integer',
                'plantilla_id' => 'nullable|integer',
                'proveedor_id' => 'required|integer',
                'empresa_id' => 'required|integer',
                'sede_id' => 'required|integer',
                'estado' => 'required|integer',
                'fecha_entrega' => 'nullable|date',

                'detalles_movimientos' => 'required|array',
            ],
            'PUT' => [
                'fecha_emision' => 'required|date',
                'tipo_movimiento' => 'required|integer',
                'observacion' => 'nullable|max:200',
                'observacion_entrega' => 'nullable|max:200',
                'destino' => 'required|string',                
                'total' => 'required|numeric',
                'user_id' => 'required|integer',
                'bodega_id' => 'required|integer',
                'plantilla_id' => 'nullable|integer',
                'proveedor_id' => 'required|integer',
                'empresa_id' => 'required|integer',
                'sede_id' => 'required|integer',
                'estado' => 'required|integer',
                'fecha_entrega' => 'nullable|date',

                'detalles_movimientos' => 'required|array',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'fecha_emision.required' => 'La fecha es obligatoria.',
            'fecha_emision.date' => 'La fecha debe tener formato fecha.',
            'tipo_movimiento.required' => 'El tipo de movimiento es obligatorio.',
            'destino.required' => 'El destino es obligatorio.',
            'total.required' => 'El total es obligatorio.',
            'total.numeric' => 'El total debe ser un número.',
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.integer' => 'El ID del usuario debe ser un número entero.',
            'bodega_id.required' => 'La bodega es obligatoria.',
            'bodega_id.integer' => 'La bodega debe ser un número entero.',
            'proveedor_id.required' => 'La bodega es obligatoria.',
            'empresa_id.integer' => 'El ID de la empresa debe ser un número entero.',
            'sede_id.integer' => 'El ID de la sede debe ser un número entero.',
            'estado.integer' => 'El estado debe ser un número entero.',
            'detalles_movimientos.required' => 'Necesitas ingresar al menos un articulo al detalle',
        ];
    }
}
