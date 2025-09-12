<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PersonaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombres,
            'apellido' => $this->apellidos,
            'documento' => $this->cuil,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'cuil' => $this->cuil,
            'pago' => $this->pago,
            'cbu_alias' => $this->cbu_alias,
            'combustible' => (bool) $this->combustible,
            'tipo_key' => $this->tipo,
            'tipo' => $this->tipo_nombre,
            'full_name' => trim(($this->nombres ?? '') . ' ' . ($this->apellidos ?? '')),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'unidad' => $this->whenLoaded('unidad', fn() => new \App\Http\Resources\UnidadResource($this->unidad)),
            'cliente' => $this->whenLoaded('cliente', fn() => new \App\Http\Resources\ClienteResource($this->cliente)),
            'sucursal' => $this->whenLoaded('sucursal', fn() => new \App\Http\Resources\SucursalResource($this->sucursal)),
            'dueno' => $this->whenLoaded('dueno', fn() => new \App\Http\Resources\DuenoResource($this->dueno)),
            'transporte_temporal' => $this->whenLoaded('transporteTemporal', fn() => new \App\Http\Resources\TransporteTemporalResource($this->transporteTemporal)),
            'archivos' => $this->whenLoaded('archivos', fn() => \App\Http\Resources\ArchivoResource::collection($this->archivos)),
            'agente' => $this->whenLoaded('agente', function () {
                if (!$this->agente) {
                    return null;
                }
                return [
                    'id' => $this->agente->id,
                    'name' => $this->agente->name,
                    'email' => $this->agente->email,
                ];
            }),
            'estado' => $this->estado_id,
            'observaciontarifa' => $this->observaciontarifa,
            'tarifaespecial' => $this->tarifaespecial,
            'observaciones' => $this->observaciones,
        ];
    }
}
