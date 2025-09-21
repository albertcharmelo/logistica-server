<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReclamoResource extends JsonResource
{
    /**
     * Transforma el modelo Reclamo a un array.
     *
     * Relacion(es) recomendadas para cargar desde el controlador:
     *  ->with(['persona.cliente','persona.sucursal','persona.unidad','agente','creator','tipo'])
     *  ->withCount('comments')
     */
    public function toArray($request)
    {
        return [
            // Base
            'id'             => $this->id,
            'persona_id'     => $this->persona_id,
            'reclamo_type_id' => $this->reclamo_type_id,
            'agente_id'      => $this->agente_id,
            'creator_id'     => $this->creator_id,
            'detalle'        => $this->detalle,
            'status'         => $this->status,              // creado, asignado_al_area, en_proceso, pendiente_resolucion, solucionado
            'created_at'     => optional($this->created_at)->toISOString(),
            'updated_at'     => optional($this->updated_at)->toISOString(),
            'deleted_at'     => optional($this->deleted_at)->toISOString(),

            // Relaciones (se devuelven solo si fueron "loaded" en el controlador)
            'persona' => $this->whenLoaded('persona', function () {
                return [
                    'id'         => $this->persona->id,
                    'nombres'    => $this->persona->nombres ?? null,
                    'apellidos'  => $this->persona->apellidos ?? null,
                    'cuil'       => $this->persona->cuil ?? null,
                    'telefono'   => $this->persona->telefono ?? null,
                    'email'      => $this->persona->email ?? null,
                    // anidados útiles para tu pantalla
                    'cliente'    => $this->persona->relationLoaded('cliente') && $this->persona->cliente
                        ? ['id' => $this->persona->cliente->id, 'nombre' => $this->persona->cliente->nombre]
                        : null,
                    'sucursal'   => $this->persona->relationLoaded('sucursal') && $this->persona->sucursal
                        ? ['id' => $this->persona->sucursal->id, 'nombre' => $this->persona->sucursal->nombre]
                        : null,
                    'unidad'     => $this->persona->relationLoaded('unidad') && $this->persona->unidad
                        ? ['id' => $this->persona->unidad->id, 'matricula' => $this->persona->unidad->matricula]
                        : null,
                    'agente'     => $this->persona->relationLoaded('agente') && $this->persona->agente
                        ? ['id' => $this->persona->agente->id, 'name' => $this->persona->agente->name]
                        : null,
                    'created_at' => optional($this->persona->created_at)->toISOString(),
                ];
            }),

            'agente' => $this->whenLoaded('agente', function () {
                return [
                    'id'    => $this->agente->id,
                    'name'  => $this->agente->name,
                    'email' => $this->agente->email,
                ];
            }),

            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id'    => $this->creator->id,
                    'name'  => $this->creator->name,
                    'email' => $this->creator->email,
                ];
            }),

            'tipo' => $this->whenLoaded('tipo', function () {
                return [
                    'id'     => $this->tipo->id,
                    'nombre' => $this->tipo->nombre,
                ];
            }),

            // Archivos asociados (si los cargas + si ya tienes ArchivoResource)
            'archivos' => $this->whenLoaded('archivos', function () {
                // si tienes pivot, puedes exponerlo aquí si hace falta
                return ArchivoResource::collection($this->archivos);
            }),

            // Métricas rápidas para la UI (solo si usas withCount/with en el controller)
            'comments_count' => $this->when(isset($this->comments_count), (int) $this->comments_count),

            // Último comentario, si lo precargas (ej: ->with(['comments' => fn($q) => $q->latest()->limit(1)]))
            'last_comment' => $this->when($this->relationLoaded('comments') && $this->comments->count() > 0, function () {
                $c = $this->comments->first(); // si cargaste limit(1)
                return [
                    'id'          => $c->id,
                    'sender_type' => $c->sender_type, // persona | agente | sistema
                    'message'     => $c->message,
                    'created_at'  => optional($c->created_at)->toISOString(),
                ];
            }),
        ];
    }
}
