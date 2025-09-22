<?php

namespace App\Events;

use App\Models\Reclamo;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ReclamoFilesAttached implements ShouldBroadcast
{
    use SerializesModels;

    public function __construct(public Reclamo $reclamo, public array $archivoIds) {}

    public function broadcastOn()
    {
        return new PrivateChannel('reclamos.' . $this->reclamo->id);
    }

    public function broadcastAs(): string
    {
        return 'reclamo.files.attached';
    }

    public function broadcastWith(): array
    {
        return [
            'reclamo_id'  => $this->reclamo->id,
            'archivo_ids' => $this->archivoIds,
        ];
    }
}
