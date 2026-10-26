<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;
    protected $table = 'ventas';

    protected $fillable = [
        'date','time','total','name','user_id','client_id',
        'type','status','mesa','pago','numero','comment','llamada',
        'monto_efectivo','monto_qr','monto_tarjeta'
    ];

//    protected $casts = [
//        'date' => 'date',
//        'time' => 'datetime:H:i:s',
//    ];

    protected static function booted()
    {
        // EFECTIVO / QR / TARJETA llevan el total completo a su monto;
        // MIXTO conserva los montos que se enviaron al cobrar
        static::saving(function (Venta $v) {
            if ($v->pago === 'MIXTO') return;
            $total = (float) $v->total;
            $v->monto_efectivo = $v->pago === 'EFECTIVO' ? $total : 0;
            $v->monto_qr       = $v->pago === 'QR' ? $total : 0;
            $v->monto_tarjeta  = $v->pago === 'TARJETA' ? $total : 0;
        });
    }

    public function detalles() {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }
    function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
//    function client() {
//        return $this->belongsTo(Client::class, 'client_id');
//    }
}
