<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlmacenCompra extends Model
{
    use SoftDeletes;

    protected $table = 'almacen_compras';

    protected $fillable = [
        'fecha',
        'proveedor',
        'nota',
        'total',
        'estado',
    ];

//    protected $casts = [
//        'fecha' => 'date',
//    ];

    public function detalles()
    {
        return $this->hasMany(AlmacenCompraDetalle::class, 'almacen_compra_id');
    }
}
