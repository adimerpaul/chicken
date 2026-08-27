<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventarioInicial extends Model
{
    protected $table = 'inventario_iniciales';

    protected $fillable = [
        'insumo_id',
        'fecha',
        'cantidad',
        'user_id',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
