<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insumo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'unidad',
        'stock',
        'costo',
        'min_stock',
        'descripcion',
        'no_contar',
        'es_mesa',
        'es_llevar',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
}
