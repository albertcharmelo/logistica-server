<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReclamoAttachFilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'archivo_ids'   => ['required', 'array', 'min:1'],
            'archivo_ids.*' => ['integer', 'exists:archivos,id'],
        ];
    }
}
