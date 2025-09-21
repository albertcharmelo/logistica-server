<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReclamoStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'persona_id'      => ['required', 'integer', 'exists:personas,id'],
            'agente_id'       => ['nullable', 'integer', 'exists:users,id'],
            'reclamo_type_id' => ['required', 'integer', 'exists:reclamo_types,id'],
            'detalle'         => ['nullable', 'string'],
            'status'          => ['nullable', 'string', 'in:creado,asignado_al_area,en_proceso,pendiente_de_resolucion,solucionado'],
            'archivo_ids'     => ['array'],
            'archivo_ids.*'   => ['integer', 'exists:archivos,id'],
        ];
    }
}
