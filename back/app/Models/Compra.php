<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fecha', 'proveedor', 'total', 'estado', 'nota'
    ];

    protected $hidden = ['deleted_at', 'updated_at','created_at'];

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }
}
