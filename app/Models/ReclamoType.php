<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReclamoType extends Model
{
    use SoftDeletes;

    protected $fillable = ['nombre', 'slug'];

    public function reclamos()
    {
        return $this->hasMany(Reclamo::class, 'reclamo_type_id');
    }
}
