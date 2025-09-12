<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dueno extends Model
{
    use SoftDeletes;
    protected $table = 'duenos';
    protected $fillable = [
        'nombreapellido',
        'persona_id',
        'fecha_nacimiento',
        'cuil',
        'cuil_cobrador',
        'cbu_alias',
        'email',
        'telefono',
        'observaciones',
    ];

    /**
     * Default attribute values.
     * When no nombreapellido is provided, use 'Sin nombre'.
     */
    protected $attributes = [
        'nombreapellido' => 'Sin nombre',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
