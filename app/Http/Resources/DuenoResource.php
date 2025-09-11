<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DuenoResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'persona_id' => $this->persona_id,
            'fecha_nacimiento' => optional($this->fecha_nacimiento)->toDateString(),
            'cuil' => $this->cuil,
            'cuil_cobrador' => $this->cuil_cobrador,
            'cbu_alias' => $this->cbu_alias,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'observaciones' => $this->observaciones,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
