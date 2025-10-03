<?php

namespace App\Events;

use App\Models\ReclamoComment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class ReclamoCommentCreated implements ShouldBroadcastNow
{
    use SerializesModels;

    public function __construct(public ReclamoComment $comment) {}

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('reclamos.' . $this->comment->reclamo_id);
    }

    public function broadcastAs(): string
    {
        return 'comment.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id'           => $this->comment->id,
            'reclamo_id'   => $this->comment->reclamo_id,
            'sender_type'  => $this->comment->sender_type, // persona | agente | sistema | creador
            'sender_user_id'    => $this->comment->sender_user_id,
            'sender_persona_id' => $this->comment->sender_persona_id,
            'message'      => $this->comment->message,
            'meta'         => $this->comment->meta,
            'created_at'   => optional($this->comment->created_at)->toISOString(),
        ];
    }
}
