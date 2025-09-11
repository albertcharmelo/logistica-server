<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursal extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nombre',
        'direccion',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }
}
