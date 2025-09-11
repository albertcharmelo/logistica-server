<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArchivoResource extends JsonResource
{
    public function toArray($request): array
    {
        $direct = ($this->disk === 'public_direct') || ($this->ruta && str_starts_with($this->ruta, 'documentos/'));
        return [
            'id' => $this->id,
            // display name; prefer nombre_original else basename of ruta
            'nombre' => $this->nombre_original ?: basename($this->ruta ?? ''),
            // short type name from related FyleType if loaded
            'tipo' => $this->when($this->relationLoaded('tipo'), fn() => $this->tipo?->nombre),
            // public URL when on public disk
            'url' => $this->when($this->ruta, function () use ($direct) {
                if ($direct) {
                    return url($this->ruta);
                }
                if ($this->disk === 'public') {
                    $base = rtrim(config('filesystems.disks.public.url', asset('storage')), '/');
                    return $base . '/' . ltrim($this->ruta, '/');
                }
                return null;
            }),
            // backend metadata
            'size' => $this->size,
            'mime_type' => $this->mime,
            'ruta' => $this->ruta,
            'nombre_original' => $this->nombre_original,
            'fecha_vencimiento' => $this->fecha_vencimiento?->toDateString(),
            'tipo_archivo' => $this->when($this->relationLoaded('tipo'), function () {
                return [
                    'id' => $this->tipo?->id,
                    'nombre' => $this->tipo?->nombre,
                    'vence' => (bool) ($this->tipo?->vence),
                ];
            }),
        ];
    }
}
