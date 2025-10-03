<?php

namespace App\Listeners;

use App\Events\ReclamoFilesAttached;
use App\Models\Notificacion;

class NotifyReclamoFilesAttached
{
    public function handle(ReclamoFilesAttached $event): void
    {
        $reclamo = $event->reclamo;
        $count = count($event->archivoIds);
        $description = $count === 1
            ? 'Se adjuntó 1 archivo al reclamo #' . $reclamo->id
            : 'Se adjuntaron ' . $count . ' archivos al reclamo #' . $reclamo->id;

        $recipientIds = [];
        if ($reclamo->agente_id) $recipientIds[] = (int) $reclamo->agente_id;
        if ($reclamo->creator_id) $recipientIds[] = (int) $reclamo->creator_id;
        $recipientIds = array_values(array_unique($recipientIds));

        foreach ($recipientIds as $userId) {
            Notificacion::create([
                'user_id'     => $userId,
                'entity_type' => 'reclamo',
                'entity_id'   => $reclamo->id,
                'type'        => 'adjunto',
                'description' => $description,
            ]);
        }
    }
}
