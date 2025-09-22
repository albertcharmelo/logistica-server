<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ReclamoCommentNotification implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(
        public int $userId,
        public int $reclamoId,
        public int $commentId,
        public string $message,
        public ?string $role = null // 'agente' | 'creador' | null
    ) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('users.' . $this->userId);
    }

    public function broadcastAs(): string
    {
        return 'reclamo.comment.notification';
    }

    public function broadcastWith(): array
    {
        return [
            'reclamo_id' => $this->reclamoId,
            'comment_id' => $this->commentId,
            'message'    => $this->message,
            'role'       => $this->role,
        ];
    }
}
