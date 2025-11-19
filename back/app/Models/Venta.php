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
        'type','status','mesa','pago','numero','comment','llamada'
    ];

//    protected $casts = [
//        'date' => 'date',
//        'time' => 'datetime:H:i:s',
//    ];

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
