<?php

namespace App\Events;

use App\Models\Notificacion;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class NotificationCreated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public Notificacion $notification) {}

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('users.' . $this->notification->user_id);
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'          => $this->notification->id,
            'user_id'     => $this->notification->user_id,
            'entity_type' => $this->notification->entity_type,
            'entity_id'   => $this->notification->entity_id,
            'type'        => $this->notification->type,
            'description' => $this->notification->description,
            'read_at'     => optional($this->notification->read_at)?->toISOString(),
            'created_at'  => optional($this->notification->created_at)?->toISOString(),
        ];
    }
}
