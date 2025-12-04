<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Almacen extends Model
{
    use SoftDeletes;
    protected $table = 'almacenes';

    protected $fillable = [
        'nombre',
        'unidad',
        'stock',
        'costo',
        'min_stock',
        'descripcion',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
