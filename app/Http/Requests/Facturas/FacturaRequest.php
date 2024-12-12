<?php

namespace App\Http\Requests\Facturas;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FacturaRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'total_venta' => $this->total_venta ?? 0,
            'total_descuento' => $this->total_descuento ?? 0,
            'total_iva' => $this->total_iva ?? 0,
            'detalle_factura' => is_string($this->detalle_factura) ? json_decode($this->detalle_factura, true) : $this->detalle_factura,
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
                'total_venta' => 'required|numeric',
                'total_descuento' => 'required|numeric',
                'total_iva' => 'required|numeric',
                'descripcion' => 'nullable|max:200',
                'user_id' => 'nullable|integer',
                'cliente_id' => 'required|integer',
                'empresa_id' => 'nullable|integer',
                'sede_id' => 'nullable|integer',
                'estado' => 'nullable|integer',
                'segmento_cliente_id' => 'required|integer',
                'sub_total' => 'required|numeric',
                'estado_factura' => 'nullable|integer',
                'estado_pago' => 'nullable|integer',
                'deuda' => 'nullable|numeric',
                'pago_out' => 'nullable|numeric',
                'fecha_validacion' => 'nullable|date',
                'fecha_pago_total' => 'nullable|date',

                'detalle_factura' => 'required|array',

                'sede_deliverie_id' => 'nullable|numeric',
                'fecha_entrega' => 'nullable|date',
                'departamento_id' => 'nullable|integer',
                'municipio_id' => 'nullable|integer',
                'direccion_deliverie' => 'nullable|string',
                'agencia_deliverie' => 'nullable|string',
                'encargado_deliverie' => 'nullable|string',
                'documento_deliverie' => 'nullable|string',
                'celular_deliverie' => 'nullable|string',

                'imagen' => 'nullable|file|image|max:2048',
                'monto_pago' => 'nullable|numeric',
                'metodo_pago_id' => 'nullable|integer',
                'banco_id' => 'nullable|integer',
            ],
            'PUT' => [
                'total_venta' => 'required|numeric',
                'total_descuento' => 'required|numeric',
                'total_iva' => 'required|numeric',
                'descripcion' => 'nullable|max:200',
                'user_id' => 'nullable|integer',
                'cliente_id' => 'required|integer',
                'empresa_id' => 'nullable|integer',
                'sede_id' => 'nullable|integer',
                'estado' => 'nullable|integer',
                'segmento_cliente_id' => 'required|integer',
                'sub_total' => 'required|numeric',
                'estado_factura' => 'nullable|integer',
                'estado_pago' => 'nullable|integer',
                'deuda' => 'nullable|numeric',
                'pago_out' => 'nullable|numeric',
                'fecha_validacion' => 'nullable|date',
                'fecha_pago_total' => 'nullable|date',

                'detalle_factura' => 'required|array',

                'sede_deliverie_id' => 'nullable|numeric',
                'fecha_entrega' => 'nullable|date',
                'departamento_id' => 'nullable|integer',
                'municipio_id' => 'nullable|integer',
                'direccion_deliverie' => 'nullable|string',
                'agencia_deliverie' => 'nullable|string',
                'encargado_deliverie' => 'nullable|string',
                'documento_deliverie' => 'nullable|string',
                'celular_deliverie' => 'nullable|string',

                'imagen' => 'nullable|file|image|max:2048',
                'monto_pago' => 'nullable|numeric',
                'metodo_pago_id' => 'nullable|integer',
                'banco_id' => 'nullable|integer',
            ],
        };
    }

    public function messages(): array
    {
        return [
            'total_venta.required' => 'El total de la venta es obligatorio.',
            'total_venta.numeric' => 'El total de la venta debe ser un número.',
            'total_descuento.required' => 'El total del descuento es obligatorio.',
            'total_descuento.numeric' => 'El total del descuento debe ser un número.',
            'total_iva.required' => 'El total del IVA es obligatorio.',
            'total_iva.numeric' => 'El total del IVA debe ser un número.',
            'descripcion.max' => 'La descripción no puede tener más de 200 caracteres.',
            'domicilio.required' => 'El domicilio es obligatorio.',
            'domicilio.string' => 'El domicilio debe ser un texto válido.',
            'user_id.integer' => 'El ID del usuario debe ser un número entero.',
            'cliente_id.required' => 'El cliente es obligatorio.',
            'cliente_id.integer' => 'El ID del cliente debe ser un número entero.',
            'empresa_id.integer' => 'El ID de la empresa debe ser un número entero.',
            'sede_id.integer' => 'El ID de la sede debe ser un número entero.',
            'estado.integer' => 'El estado debe ser un número entero.',
            'segmento_cliente_id.required' => 'El segmento del cliente es obligatorio.',
            'segmento_cliente_id.integer' => 'El segmento del cliente debe ser un número entero.',
            'sub_total.required' => 'El subtotal es obligatorio.',
            'sub_total.numeric' => 'El subtotal debe ser un número.',
            // 'estado_factura.required' => 'El estado de la factura es obligatorio.',
            'estado_factura.integer' => 'El estado de la factura debe ser un número entero.',
            'estado_pago.integer' => 'El estado del pago debe ser un número entero.',
            'deuda.numeric' => 'La deuda debe ser un número.',
            'pago_out.numeric' => 'El pago debe ser un número.',
            'fecha_validacion.date' => 'La fecha de validación debe ser una fecha válida.',
            'fecha_pago_total.date' => 'La fecha de pago total debe ser una fecha válida.',
            'detalle_factura.required' => 'Necesitas ingresar al menos un articulo al detalle',
        ];
    }
}
