<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransporteTemporal extends Model
{
    use SoftDeletes;
    protected $table = 'transporte_temporals';
    protected $fillable = [
        'persona_id',
        'guia_remito',
        'valor_viaje',
        'origen',
        'destino',
        'estado_servicio_id',
    ];

    protected $casts = [
        'valor_viaje' => 'decimal:2',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
