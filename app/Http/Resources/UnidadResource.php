<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnidadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'matricula' => $this->matricula,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'anio' => $this->anio,
            'observacion' => $this->observacion,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
