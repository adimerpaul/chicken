<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    protected $table = 'cierre_cajas';

    protected $fillable = [
        'user_id',
        'date',
        'total_ingresos',
        'total_egresos',
        'total_caja_inicial',
        'tickets',
        'monto_efectivo',
        'monto_sistema',
        'diferencia',
        'observacion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
