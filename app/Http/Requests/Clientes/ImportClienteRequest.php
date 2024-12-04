<?php

namespace App\Http\Requests\Clientes;

use Illuminate\Foundation\Http\FormRequest;

class ImportClienteRequest extends FormRequest
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
            'POST' => [
                'import_file' => 'required|file|mimes:xls,xlsx,csv'
            ]
        };
    }

    public function messages(): array
    {
        return [
            'import_file.required' => 'El documento es obligatorio',
            'import_file.file' => 'El documento debe ser un archivo',
            'import_file.mimes' => 'El documento debe ser de tipo (xls,xlsx,csv)',
        ];
    }
}
