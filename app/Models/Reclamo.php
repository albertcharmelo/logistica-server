<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reclamo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'persona_id',
        'reclamo_type_id',
        'agente_id',
        'detalle',
        'status',
        'fecha_alta', // <-- permitir asignación masiva
    ];

    protected $casts = [
        // ...otros casts
        'fecha_alta' => 'datetime', // o 'date' si usas campo date
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function agente()
    {
        return $this->belongsTo(User::class, 'agente_id');
    }

    public function tipo()
    {
        return $this->belongsTo(ReclamoType::class, 'reclamo_type_id');
    }

    public function archivos()
    {
        return $this->belongsToMany(Archivo::class, 'reclamo_archivo')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(ReclamoComment::class);
    }

    public function logs()
    {
        return $this->hasMany(ReclamoLog::class);
    }
}
