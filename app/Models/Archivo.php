<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Archivo extends Model
{
    use SoftDeletes;
    protected $table = 'archivos';

    protected $fillable = [
        'persona_id',
        'tipo_archivo_id',
        'carpeta',
        'ruta',
        'disk',
        'nombre_original',
        'mime',
        'size',
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }

    public function tipo()
    {
        return $this->belongsTo(FyleType::class, 'tipo_archivo_id');
    }
}
