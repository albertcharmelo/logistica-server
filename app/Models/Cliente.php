<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;
    protected $table = 'clientes';
    protected $fillable = [
        'codigo',
        'nombre',
        'direccion',
        'documento_fiscal',
    ];

    public function sucursales()
    {
        return $this->hasMany(Sucursal::class);
    }

    public function personas()
    {
        // placeholder relation - implement Persona model if exists
        return $this->hasMany(\App\Models\Persona::class);
    }
}
