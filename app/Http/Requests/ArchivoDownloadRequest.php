<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ArchivoDownloadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'ruta' => ['required', 'string', 'max:1024'],
        ];
    }
}
