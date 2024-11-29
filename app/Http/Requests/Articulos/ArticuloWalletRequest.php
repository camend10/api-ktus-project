<?php

namespace App\Http\Requests\Articulos;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticuloWalletRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $user = auth('api')->user();

        // Mezclar 'empresa_id' al request basado en el usuario autenticado
        $this->merge([
            'empresa_id' => $user->empresa_id,
            'segmento_cliente_id' => ($this->input('segmento_cliente_id') == 9999999) ? null : $this->input('segmento_cliente_id'),
            'sede_id' => ($this->input('sede_id') == 9999999) ? null : $this->input('sede_id'),
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
        return match ($this->method()) {
            'POST' => [
                'articulo_id' => [
                    'required',
                    Rule::unique('articulo_wallets')->where(function ($query) {
                        return $query
                            ->where('unidad_id', $this->unidad_id)
                            ->where('empresa_id', $this->empresa_id)
                            ->where('sede_id', $this->sede_id) // Comparación directa con sede_id
                            ->where(function ($q) {
                                if (!is_null($this->segmento_cliente_id)) {
                                    $q->where('segmento_cliente_id', $this->segmento_cliente_id);
                                } else {
                                    $q->whereNull('segmento_cliente_id'); // Comparación explícita de NULL
                                }
                            });
                    })
                ],
                'unidad_id' => 'required',
                'precio' => 'required',
                'sede_id' => 'integer|nullable',
                'segmento_cliente_id' => 'integer|nullable',
                'empresa_id' => 'integer|nullable',
                'estado' => 'integer|nullable',
            ],
            'PUT' => [
                'articulo_id' => [
                    'required',
                    Rule::unique('articulo_wallets')->where(function ($query) {
                        return $query
                            ->where('unidad_id', $this->unidad_id)
                            ->where('empresa_id', $this->empresa_id)
                            ->where('sede_id', $this->sede_id) // Comparación directa con sede_id
                            ->where(function ($q) {
                                if (!is_null($this->segmento_cliente_id)) {
                                    $q->where('segmento_cliente_id', $this->segmento_cliente_id);
                                } else {
                                    $q->whereNull('segmento_cliente_id'); // Comparación explícita de NULL
                                }
                            });
                    })->ignore($this->id), // Ignorar el registro actual al editar
                ],
                'unidad_id' => 'required',
                'precio' => 'required',
                'sede_id' => 'integer|nullable',
                'segmento_cliente_id' => 'integer|nullable',
                'empresa_id' => 'integer|nullable',
                'estado' => 'integer|nullable',
            ],
        };
    }


    public function messages(): array
    {
        return [
            'articulo_id.required' => 'El articulo es obligatorio',
            'unidad_id.required' => 'La unidad es obligatoria',
            'precio.required' => 'El precio es obligatorio',
            'articulo_id.unique' => 'Ya existe un registro con esta combinación de artículo, unidad, sede, tipo de cliente',
            // 'articulo_id.unique' => 'Ya existe un registro con esta combinación de artículo: '
            //     . $this->articulo_id
            //     . ', unidad: ' . $this->unidad_id
            //     . ', sede: ' . ($this->sede_id ?? 'NULL')
            //     . ', tipo de cliente: ' . ($this->segmento_cliente_id ?? 'NULL'),
        ];
    }
}
