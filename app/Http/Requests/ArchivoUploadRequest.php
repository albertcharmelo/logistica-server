<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchivoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo' => ['required', 'file', 'max:10240'], // 10MB
            'carpeta' => ['required', 'string', 'max:255'],
            'origen_id' => ['required', 'integer', 'exists:personas,id'],
            'nombre_original' => ['nullable', 'string', 'max:255'],
            'tipo_archivo_id' => ['required', 'integer', 'exists:fyle_types,id'],
        ];
    }
}
