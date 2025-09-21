<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReclamoLog extends Model
{
    protected $fillable = [
        'reclamo_id',
        'old_status',
        'new_status',
        'changed_by',
    ];

    public function reclamo()
    {
        return $this->belongsTo(Reclamo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
