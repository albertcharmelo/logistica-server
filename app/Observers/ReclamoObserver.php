<?php

namespace App\Observers;

use App\Events\ReclamoStatusChanged;
use App\Models\Reclamo;
use App\Models\ReclamoComment;
use App\Models\ReclamoLog;
use App\Models\Notificacion;
use Illuminate\Support\Facades\Auth;

class ReclamoObserver
{
    public function created(Reclamo $reclamo): void
    {
        // Notificar a agente y creador que el reclamo fue creado
        $recipientIds = [];
        if ($reclamo->agente_id) $recipientIds[] = (int) $reclamo->agente_id;
        if ($reclamo->creator_id) $recipientIds[] = (int) $reclamo->creator_id;
        $recipientIds = array_values(array_unique($recipientIds));

        foreach ($recipientIds as $userId) {
            Notificacion::create([
                'user_id'     => $userId,
                'entity_type' => 'reclamo',
                'entity_id'   => $reclamo->id,
                'type'        => 'creado',
                'description' => 'Nuevo reclamo #' . $reclamo->id . ' creado',
            ]);
        }
    }

    public function updating(Reclamo $reclamo): void
    {
        if ($reclamo->isDirty('status')) {

            $old = $reclamo->getOriginal('status'); // estado anterior
            $new = $reclamo->status;

            ReclamoLog::create([
                'reclamo_id' => $reclamo->id,
                'old_status' => $old,
                'new_status' => $new,
                'changed_by' => Auth::id(),
            ]);

            event(new ReclamoStatusChanged($reclamo, $old, $reclamo->status));
            // Comentario del sistema
            ReclamoComment::create([
                'reclamo_id'  => $reclamo->id,
                'message'     => sprintf('Estado cambiado de %s a %s', $this->labelStatus($old), $this->labelStatus($new)),
                'sender_type' => 'sistema',
                'persona_id'  => null,
                'agente_id'   => null,
                'creator_id'  => null,
            ]);

            // Notificación por cambio de estado (agente, creador y participantes previos), excluyendo al actor
            $recipientIds = [];
            if ($reclamo->agente_id) $recipientIds[] = (int) $reclamo->agente_id;
            if ($reclamo->creator_id) $recipientIds[] = (int) $reclamo->creator_id;
            $participantIds = \App\Models\ReclamoComment::query()
                ->where('reclamo_id', $reclamo->id)
                ->whereNotNull('sender_user_id')
                ->pluck('sender_user_id')
                ->map(fn($v) => (int) $v)
                ->all();
            $recipientIds = array_merge($recipientIds, $participantIds);
            $actorId = (int) (Auth::id() ?? 0);
            $recipientIds = array_values(array_unique(array_filter($recipientIds, fn($id) => $id && (int)$id !== $actorId)));

            foreach ($recipientIds as $userId) {
                Notificacion::create([
                    'user_id'     => $userId,
                    'entity_type' => 'reclamo',
                    'entity_id'   => $reclamo->id,
                    'type'        => 'estado',
                    'description' => sprintf('Estado cambiado de %s a %s', $this->labelStatus($old), $this->labelStatus($new)),
                ]);
            }
        }

        // Cambio de agente
        if ($reclamo->isDirty('agente_id')) {
            $oldAgente = $reclamo->getOriginal('agente_id');
            $newAgente = $reclamo->agente_id;

            $description = $oldAgente
                ? 'El reclamo fue reasignado a un nuevo agente'
                : 'El reclamo fue asignado a un agente';

            $recipientIds = [];
            if ($newAgente) $recipientIds[] = (int) $newAgente; // nuevo agente
            if ($reclamo->creator_id) $recipientIds[] = (int) $reclamo->creator_id; // creador
            $participantIds = \App\Models\ReclamoComment::query()
                ->where('reclamo_id', $reclamo->id)
                ->whereNotNull('sender_user_id')
                ->pluck('sender_user_id')
                ->map(fn($v) => (int) $v)
                ->all();
            $recipientIds = array_merge($recipientIds, $participantIds);
            $actorId = (int) (Auth::id() ?? 0);
            $recipientIds = array_values(array_unique(array_filter($recipientIds, fn($id) => $id && (int)$id !== $actorId)));

            foreach ($recipientIds as $userId) {
                Notificacion::create([
                    'user_id'     => $userId,
                    'entity_type' => 'reclamo',
                    'entity_id'   => $reclamo->id,
                    'type'        => 'asignacion',
                    'description' => $description,
                ]);
            }
        }
    }

    private function labelStatus(?string $status): string
    {
        return match ($status) {
            'en_proceso'  => 'en proceso',
            'solucionado' => 'solucionado',
            'creado'      => 'creado',
            default       => (string) $status,
        };
    }
}
