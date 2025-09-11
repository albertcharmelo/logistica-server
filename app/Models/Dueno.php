<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dueno extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'persona_id',
        'fecha_nacimiento',
        'cuil',
        'cuil_cobrador',
        'cbu_alias',
        'email',
        'telefono',
        'observaciones',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
