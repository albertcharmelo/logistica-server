<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estado extends Model
{
    use SoftDeletes;
    protected $table = 'estados';
    protected $fillable = ['nombre'];

    public function personas()
    {
        return $this->hasMany(Persona::class);
    }
}
