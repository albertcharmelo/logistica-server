<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    use SoftDeletes;
    protected $table = 'personas';
    protected $fillable = [
        'apellidos',
        'nombres',
        'cuil',
        'telefono',
        'email',
        'pago',
        'cbu_alias',
        'combustible',
        'unidad_id',
        'cliente_id',
        'sucursal_id',
        'estado_id',
        'tipo',
        'observaciontarifa',
        'tarifaespecial',
        'observaciones',
    ];

    protected $casts = [
        'combustible' => 'boolean',
        'pago' => 'decimal:2',
        'tarifaespecial' => 'integer',
    ];

    public function unidad()
    {
        return $this->belongsTo(Unidad::class);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function estado()
    {
        return $this->belongsTo(Estado::class);
    }

    public function dueno()
    {
        return $this->hasOne(Dueno::class);
    }

    public function transporteTemporal()
    {
        return $this->hasOne(TransporteTemporal::class);
    }

    public function archivos()
    {
        return $this->hasMany(Archivo::class);
    }
}
