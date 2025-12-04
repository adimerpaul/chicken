<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlmacenInsumoDetalle extends Model
{
    protected $table = 'almacen_insumo_detalles';

    protected $fillable = [
        'almacen_insumo_movimiento_id',
        'almacen_id',
        'insumo_id',
        'cantidad',
        'costo',
        'subtotal',
    ];

    public function movimiento()
    {
        return $this->belongsTo(AlmacenInsumoMovimiento::class, 'almacen_insumo_movimiento_id');
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class, 'almacen_id');
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class, 'insumo_id');
    }
}
