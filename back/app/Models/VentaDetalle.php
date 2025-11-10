<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    protected $table = 'venta_detalles';

    protected $fillable = [
        'venta_id','product_id','name','price','qty','subtotal'
    ];

    public function venta() {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
