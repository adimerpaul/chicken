<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InsumoProducto extends Model
{
    //            $table->unsignedBigInteger('insumo_id')->index();
    //            $table->unsignedBigInteger('producto_id')->index();
    //            $table->double('cantidad')->default(1);
    //            $table->foreign('insumo_id')->references('id')->on('insumos')->cascadeOnDelete();
    //            $table->foreign('producto_id')->references('id')->on('productos')->cascadeOnDelete();
    //            $table->softDeletes();
    use SoftDeletes;
    protected $fillable = [
        'insumo_id', 'producto_id', 'cantidad'
    ];
    protected $hidden = [
        'created_at', 'updated_at', 'deleted_at'
    ];
    function  insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
    function  producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
