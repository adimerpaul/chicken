<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fecha', 'hora', 'proveedor', 'total', 'estado', 'nota', 'pago',
        'monto_efectivo', 'monto_qr'
    ];

    protected $hidden = ['deleted_at', 'updated_at','created_at'];

    protected static function booted()
    {
        // EFECTIVO / QR llevan el total completo a su monto; MIXTO conserva lo enviado
        static::saving(function (Compra $c) {
            if ($c->pago === 'MIXTO') return;
            $total = (float) $c->total;
            $c->monto_efectivo = $c->pago === 'EFECTIVO' ? $total : 0;
            $c->monto_qr       = $c->pago === 'QR' ? $total : 0;
        });
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }
}
