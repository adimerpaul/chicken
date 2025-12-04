<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlmacenCompraDetalle extends Model
{
    use SoftDeletes;
    protected $table = 'almacen_compra_detalles';

    protected $fillable = [
        'almacen_compra_id',
        'almacen_id',
        'cantidad',
        'costo',
        'subtotal',
    ];

    public function compra()
    {
        return $this->belongsTo(AlmacenCompra::class, 'almacen_compra_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }
}
