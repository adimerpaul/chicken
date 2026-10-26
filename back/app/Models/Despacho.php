<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Despacho extends Model
{
    use SoftDeletes;

    protected $table = 'despachos';

    protected $fillable = [
        'fecha',
        'numero_nota',
        'proveedor',
        'cobrador',
        'comentario',
        'comentario_admin',
        'total_peso_recibido',
        'total_peso_bruto',
        'total_peso_neto',
        'total',
        'estado',
        'user_id',
        'admin_id',
    ];

    public function detalles()
    {
        return $this->hasMany(DespachoDetalle::class, 'despacho_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id')->withTrashed();
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id')->withTrashed();
    }
}
