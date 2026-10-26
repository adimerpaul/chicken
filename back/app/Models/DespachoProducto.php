<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DespachoProducto extends Model
{
    use SoftDeletes;

    protected $table = 'despacho_productos';

    protected $fillable = [
        'nombre',
        'unidad',
        'precio',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];
}
