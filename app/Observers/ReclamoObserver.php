<?php

namespace App\Observers;

use App\Models\Reclamo;
use App\Models\ReclamoLog;
use Illuminate\Support\Facades\Auth;

class ReclamoObserver
{
    public function updating(Reclamo $reclamo): void
    {
        if ($reclamo->isDirty('status')) {
            ReclamoLog::create([
                'reclamo_id' => $reclamo->id,
                'old_status' => $reclamo->getOriginal('status'),
                'new_status' => $reclamo->status,
                'changed_by' => Auth::id(),
            ]);
        }
    }
}
