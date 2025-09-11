<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ClienteResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'codigo' => $this->codigo,
            'nombre' => $this->nombre,
            'direccion' => $this->direccion,
            'documento_fiscal' => $this->documento_fiscal,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'sucursales_count' => $this->sucursales()->count(),
            'personas_count' => method_exists($this, 'personas') ? $this->personas()->count() : 0,
            'sucursales' => \App\Http\Resources\SucursalResource::collection($this->whenLoaded('sucursales') ?? $this->sucursales),
            'personas' => [],
        ];
    }
}
