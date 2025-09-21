<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReclamoCommentStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ajusta a políticas
    }

    /**
     * Normaliza el payload antes de validar:
     * - Deriva sender_type según prioridad: persona_id > creator_id > agente_id > sistema
     * - Mapea persona_id -> sender_persona_id y agente_id -> sender_user_id para compatibilidad interna
     */
    protected function prepareForValidation(): void
    {
        $personaId = $this->input('persona_id') ?? $this->input('sender_persona_id');
        $agenteId  = $this->input('agente_id') ?? $this->input('sender_user_id');
        $creatorId = $this->input('creator_id');

        // Prioridad: persona > creador > agente > sistema
        $senderType = 'sistema';
        if (!empty($personaId)) {
            $senderType = 'persona';
        } elseif (!empty($creatorId)) {
            $senderType = 'creador';
        } elseif (!empty($agenteId)) {
            $senderType = 'agente';
        }

        $this->merge([
            'sender_type'        => $senderType,
            'sender_persona_id'  => $personaId,
            'sender_user_id'     => $agenteId,
            // creator_id se pasa tal cual para guardarse en la columna creator_id
            'creator_id'         => $creatorId,
        ]);
    }

    public function rules(): array
    {
        return [
            // reclamo_id viene por la ruta, el controlador lo establece
            'message'           => ['required', 'string'],
            'meta'              => ['nullable', 'array'],

            // campos de entrada "públicos"
            'persona_id'        => ['nullable', 'integer', 'exists:personas,id'],
            'agente_id'         => ['nullable', 'integer', 'exists:users,id'],
            'creator_id'        => ['nullable', 'integer', 'exists:users,id'],

            // campos internos normalizados (rellenados en prepareForValidation)
            'sender_type'       => ['required', 'in:persona,agente,sistema,creador'],
            'sender_persona_id' => ['nullable', 'integer', 'exists:personas,id'],
            'sender_user_id'    => ['nullable', 'integer', 'exists:users,id'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $type = $this->input('sender_type');
            if ($type === 'persona' && !$this->filled('sender_persona_id')) {
                $v->errors()->add('sender_persona_id', 'sender_persona_id es obligatorio cuando sender_type=persona.');
            }
            if ($type === 'agente' && !$this->filled('sender_user_id')) {
                $v->errors()->add('sender_user_id', 'sender_user_id es obligatorio cuando sender_type=agente.');
            }
            if ($type === 'creador' && !$this->filled('creator_id')) {
                $v->errors()->add('creator_id', 'creator_id es obligatorio cuando sender_type=creador.');
            }
        });
    }
}
