<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClienteStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'max:100'],
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:500'],
            'documento_fiscal' => ['nullable', 'string', 'max:255'],
            'sucursales' => ['nullable', 'array'],
            'sucursales.*.nombre' => ['required_with:sucursales', 'string', 'max:255'],
            'sucursales.*.direccion' => ['nullable', 'string', 'max:500'],
        ];
    }
}
