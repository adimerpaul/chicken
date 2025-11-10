<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompraDetalle extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'compra_id', 'insumo_id', 'cantidad', 'costo', 'subtotal'
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
    protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
