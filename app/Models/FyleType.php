<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FyleType extends Model
{
    use SoftDeletes;
    protected $table = 'fyle_types';

    protected $fillable = [
        'nombre',
        'vence',
    ];

    protected $casts = [
        'vence' => 'boolean',
    ];
}
