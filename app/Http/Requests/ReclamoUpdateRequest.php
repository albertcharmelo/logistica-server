<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReclamoUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'agente_id'       => ['nullable', 'integer', 'exists:users,id'],
            'reclamo_type_id' => ['nullable', 'integer', 'exists:reclamo_types,id'],
            'detalle'         => ['nullable', 'string'],
            'status'          => ['nullable', 'string', 'in:creado,asignado_al_area,en_proceso,pendiente_de_resolucion,solucionado'],
        ];
    }
}
