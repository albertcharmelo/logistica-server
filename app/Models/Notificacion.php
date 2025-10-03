<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Events\NotificationCreated;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'type',
        'description',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected $appends = [
        'is_read',
    ];

    protected function isRead(): Attribute
    {
        return Attribute::make(
            get: fn() => (bool) $this->read_at,
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(function (Notificacion $notification) {
            event(new NotificationCreated($notification));
        });
    }
}
