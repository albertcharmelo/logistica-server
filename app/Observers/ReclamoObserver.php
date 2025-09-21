<?php

namespace App\Observers;

use App\Models\Reclamo;
use App\Models\ReclamoComment;
use App\Models\ReclamoLog;
use Illuminate\Support\Facades\Auth;

class ReclamoObserver
{
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


            // Comentario del sistema
            ReclamoComment::create([
                'reclamo_id'  => $reclamo->id,
                'message'     => sprintf('Estado cambiado de %s a %s', $this->labelStatus($old), $this->labelStatus($new)),
                'sender_type' => 'sistema',
                'persona_id'  => null,
                'agente_id'   => null,
                'creator_id'  => null,
            ]);
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
