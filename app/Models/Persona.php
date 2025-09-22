<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Persona extends Model
{
    use SoftDeletes;
    protected $table = 'personas';
    public const TIPO_MAP = [
        1 => 'dueno_chofer',
        2 => 'chofer',
        3 => 'transporte_temporal',
        4 => 'otro',
    ];
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
        'agente_id',
        'estado_id',
        'tipo',
        'observaciontarifa',
        'tarifaespecial',
        'observaciones',
        'fecha_alta',
    ];

    protected $casts = [
        'combustible' => 'boolean',
        'pago' => 'decimal:2',
        'tarifaespecial' => 'integer',
        'fecha_alta' => 'date',
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

    public function agente()
    {
        return $this->belongsTo(User::class, 'agente_id');
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

    public function getTipoNombreAttribute(): ?string
    {
        if (is_null($this->tipo)) {
            return null;
        }
        return self::TIPO_MAP[$this->tipo] ?? null;
    }
}
