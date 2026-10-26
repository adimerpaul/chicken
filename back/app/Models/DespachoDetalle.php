<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DespachoDetalle extends Model
{
    use SoftDeletes;

    protected $table = 'despacho_detalles';

    protected $fillable = [
        'despacho_id',
        'despacho_producto_id',
        'cantidad',
        'peso_recibido',
        'comentario',
        'envase',
        'peso_envase',
        'tara',
        'peso_bruto',
        'peso_neto',
        'precio',
        'importe',
    ];

    protected $appends = ['diferencia'];

    // kg que faltan: lo que dice la nota (p. neto) menos lo que se pesó al recibir
    public function getDiferenciaAttribute()
    {
        if ((float) $this->peso_neto <= 0) {
            return 0;
        }
        return round((float) $this->peso_neto - (float) $this->peso_recibido, 2);
    }

    public function despacho()
    {
        return $this->belongsTo(Despacho::class, 'despacho_id');
    }

    public function producto()
    {
        return $this->belongsTo(DespachoProducto::class, 'despacho_producto_id')->withTrashed();
    }
}
