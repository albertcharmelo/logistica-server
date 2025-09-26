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

    public function reclamo()
    {
        return $this->belongsTo(Reclamo::class);
    }
    public function agente()
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }
    public function persona()
    {
        return $this->belongsTo(Persona::class, 'sender_persona_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // 👇 Nombre siempre disponible para la vista/API
    protected $appends = ['author_name'];

    public function getAuthorNameAttribute(): string
    {
        if ($this->sender_type === 'user' && $this->agente) {
            return (string) $this->agente->name;
        }
        if ($this->sender_type === 'persona' && $this->persona) {
            // ajusta al campo real (full_name, nombre, etc.)
            return (string) ($this->persona->full_name ?? $this->persona->nombre ?? 'Persona');
        }
        return 'Usuario';
    }
}
