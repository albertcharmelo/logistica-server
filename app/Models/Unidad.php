<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unidad extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'matricula',
        'marca',
        'modelo',
        'anio',
        'observacion',
    ];
}
