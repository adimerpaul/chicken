<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentaDetalle extends Model
{
    use SoftDeletes;
    protected $table = 'venta_detalles';

    protected $fillable = [
        'venta_id','product_id','name','price','qty','subtotal'
    ];

    public function venta() {
        return $this->belongsTo(Venta::class, 'venta_id');
    }
}
