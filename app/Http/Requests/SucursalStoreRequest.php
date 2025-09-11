<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SucursalStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['required', 'string', 'max:500'],
        ];
    }
}
