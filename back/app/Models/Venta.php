<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    protected $table = 'ventas';

    protected $fillable = [
        'date','time','total','name','user_id','client_id',
        'type','status','mesa','pago','numero','comment','llamada'
    ];

//    protected $casts = [
//        'date' => 'date',
//        'time' => 'datetime:H:i:s',
//    ];

    public function detalles() {
        return $this->hasMany(VentaDetalle::class, 'venta_id');
    }
}
