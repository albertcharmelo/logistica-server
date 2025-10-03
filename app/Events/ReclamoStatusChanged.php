<?php

namespace App\Events;

use App\Models\Reclamo;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ReclamoStatusChanged implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public Reclamo $reclamo, public string $old, public string $new) {}

    public function broadcastOn()
    {
        return [
            new PrivateChannel('reclamos.' . $this->reclamo->id),
            new PrivateChannel('users.' . $this->reclamo->creator_id),
            new PrivateChannel('users.' . $this->reclamo->agente_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'reclamo.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'reclamo_id' => $this->reclamo->id,
            'old'        => $this->old,
            'new'        => $this->new,
            'updated_at' => optional($this->reclamo->updated_at)->toISOString(),
        ];
    }
}
