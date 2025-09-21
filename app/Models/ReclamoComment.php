<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReclamoComment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'reclamo_id',
        'sender_type',
        'creator_id',          // 👈 nuevo
        'sender_persona_id',
        'sender_user_id',
        'message',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function reclamo()
    {
        return $this->belongsTo(Reclamo::class);
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class, 'sender_persona_id');
    }

    public function agente()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
    public function creator() // opcional para mostrar en UI
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
