<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransporteTemporalResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'persona_id' => $this->persona_id,
            'guia_remito' => $this->guia_remito,
            'valor_viaje' => $this->valor_viaje,
            'origen' => $this->origen,
            'destino' => $this->destino,
            'estado_servicio_id' => $this->estado_servicio_id,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
