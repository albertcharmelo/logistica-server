<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnidadStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'matricula' => ['nullable', 'string', 'max:255'],
            'marca' => ['nullable', 'string', 'max:255'],
            'modelo' => ['nullable', 'string', 'max:255'],
            'anio' => ['nullable', 'string', 'max:50'],
            'observacion' => ['nullable', 'string'],
        ];
    }
}
