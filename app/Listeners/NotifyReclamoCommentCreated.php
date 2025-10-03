<?php

namespace App\Listeners;

use App\Events\ReclamoCommentCreated;
use App\Models\Notificacion;
use App\Models\ReclamoComment;

class NotifyReclamoCommentCreated
{
    public function handle(ReclamoCommentCreated $event): void
    {
        $comment = $event->comment;
        $reclamo = $comment->reclamo; // requires relation; else fetch by id
        if (!$reclamo) {
            $reclamo = \App\Models\Reclamo::find($comment->reclamo_id);
        }
        if (!$reclamo) return;

        $authorUserId = $comment->sender_user_id;

        // Regla:
        // - Si escribe el agente => notificar al creador
        // - Si escribe el creador => notificar al agente
        // - Si escribe otro usuario => notificar a ambos (agente y creador)
        $creatorId = $reclamo->creator_id ?? $reclamo->created_by ?? null;
        $agentId   = $reclamo->agente_id ?? null;

        $recipients = [];
        if ($agentId && $authorUserId && (int)$authorUserId === (int)$agentId) {
            if ($creatorId) $recipients[] = (int) $creatorId;
        } elseif ($creatorId && $authorUserId && (int)$authorUserId === (int)$creatorId) {
            if ($agentId) $recipients[] = (int) $agentId;
        } else {
            // Otro usuario: notificar a ambos (si existen)
            if ($creatorId) $recipients[] = (int) $creatorId;
            if ($agentId) $recipients[] = (int) $agentId;
        }

        // Incluir a todos los participantes previos del hilo (quienes ya comentaron antes)
        $participantIds = ReclamoComment::query()
            ->where('reclamo_id', $reclamo->id)
            ->whereNotNull('sender_user_id')
            ->pluck('sender_user_id')
            ->map(fn($v) => (int) $v)
            ->all();
        $recipients = array_merge($recipients, $participantIds);

        // Evitar duplicados y jamás notificar al autor por error
        $recipients = array_values(array_unique(array_filter($recipients, fn($id) => $id && (int)$id !== (int)$authorUserId)));

        foreach ($recipients as $userId) {
            Notificacion::create([
                'user_id'     => (int) $userId,
                'entity_type' => 'reclamo',
                'entity_id'   => $reclamo->id,
                'type'        => 'comentario',
                'description' => 'Nuevo comentario en el reclamo #' . $reclamo->id,
            ]);
        }
    }
}
