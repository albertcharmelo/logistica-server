<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReclamoCommentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'                 => $this->id,
            'reclamo_id'         => $this->reclamo_id,
            'sender_type'        => $this->sender_type,          // persona | agente | sistema | creador
            'sender_persona_id'  => $this->sender_persona_id,
            'sender_user_id'     => $this->sender_user_id,
            'message'            => $this->message,
            'meta'               => $this->meta,
            'created_at'         => optional($this->created_at)->toISOString(),
            'updated_at'         => optional($this->updated_at)->toISOString(),

            // opcional: info básica del remitente para UI
            'persona' => $this->whenLoaded('persona', function () {
                return [
                    'id'       => $this->persona->id,
                    'nombres'  => $this->persona->nombres,
                    'apellidos' => $this->persona->apellidos,
                ];
            }),
            'agente' => $this->whenLoaded('agente', function () {
                return [
                    'id'   => $this->agente->id,
                    'name' => $this->agente->name,
                    'email' => $this->agente->email,
                ];
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id'    => $this->creator->id,
                    'name'  => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            })
        ];
    }
}
