<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReclamoArchivoUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajusta si necesitas políticas/permisos
        return true;
    }

    public function rules(): array
    {
        return [
            'reclamo_id'      => ['required', 'integer', 'exists:reclamos,id'],
            'tipo_archivo_id' => ['required', 'integer', 'exists:fyle_types,id'],
            'archivo'         => ['required', 'file', 'max:10240'], // 10 MB (ajusta si querés)
            'nombre_original' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'reclamo_id.required'      => 'El reclamo es obligatorio.',
            'reclamo_id.integer'       => 'El reclamo debe ser un ID válido.',
            'reclamo_id.exists'        => 'El reclamo seleccionado no existe.',
            'tipo_archivo_id.required' => 'El tipo de archivo es obligatorio.',
            'tipo_archivo_id.integer'  => 'El tipo de archivo debe ser un ID válido.',
            'tipo_archivo_id.exists'   => 'El tipo de archivo seleccionado no existe.',
            'archivo.required'         => 'Debes adjuntar un archivo.',
            'archivo.file'             => 'El adjunto debe ser un archivo.',
            'archivo.max'              => 'El archivo no puede superar los :max KB.',
            'nombre_original.string'   => 'El nombre original debe ser texto.',
            'nombre_original.max'      => 'El nombre original no puede superar 255 caracteres.',
        ];
    }

    public function attributes(): array
    {
        return [
            'reclamo_id'      => 'reclamo',
            'tipo_archivo_id' => 'tipo de archivo',
            'archivo'         => 'archivo',
            'nombre_original' => 'nombre original',
        ];
    }
}
