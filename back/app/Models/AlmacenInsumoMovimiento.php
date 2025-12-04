<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlmacenInsumoMovimiento extends Model
{
    use SoftDeletes;

//    protected $table = 'almacen_insumo_detalles';

    protected $fillable = [
        'fecha',
        'nota',
        'total',
        'estado',
    ];

//    protected $casts = [
//        'fecha' => 'date',
//    ];

    public function detalles()
    {
        return $this->hasMany(AlmacenInsumoDetalle::class, 'almacen_insumo_movimiento_id');
    }
}
