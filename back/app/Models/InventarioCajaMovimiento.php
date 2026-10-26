<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventarioCajaMovimiento extends Model
{
    use SoftDeletes;

    protected $table = 'inventario_caja_movimientos';

    protected $fillable = [
        'user_id',
        'date',
        'producto_id',
        'tipo',
        'cantidad',
        'comment',
    ];

    // Categoría de productos que el vendedor cuenta al abrir su caja
    public const CATEGORIA = 'Refrescos y Bebidas';

    /**
     * Cuadre del inventario de un vendedor en un día:
     * queda = inicial + aumentos - vendido (ventas ACTIVAS del usuario ese día).
     * Si se anula una venta, lo vendido se corrige solo.
     */
    public static function resumen(int $userId, string $date): array
    {
        $movs = self::where('user_id', $userId)->where('date', $date)->get();

        $vendidos = VentaDetalle::join('ventas as v', 'v.id', '=', 'venta_detalles.venta_id')
            ->where('v.user_id', $userId)
            ->where('v.date', $date)
            ->where('v.status', 'ACTIVO')
            ->where('v.type', 'INGRESO')
            ->whereNull('v.deleted_at')
            ->groupBy('venta_detalles.product_id')
            ->selectRaw('venta_detalles.product_id, SUM(venta_detalles.qty) as qty')
            ->pluck('qty', 'product_id');

        $productos = Producto::withTrashed()
            ->where(function ($q) use ($movs) {
                $q->where(function ($w) {
                    $w->where('categoria', self::CATEGORIA)->where('active', 1)->whereNull('deleted_at');
                })->orWhereIn('id', $movs->pluck('producto_id'));
            })
            ->orderBy('ord')
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'unit']);

        $items = $productos->map(function ($p) use ($movs, $vendidos) {
            $propios = $movs->where('producto_id', $p->id);
            $inicial = (float) $propios->where('tipo', 'INICIAL')->sum('cantidad');
            $aumento = (float) $propios->where('tipo', 'AUMENTO')->sum('cantidad');
            $vendido = (float) ($vendidos[$p->id] ?? 0);

            return [
                'producto_id' => $p->id,
                'name'        => $p->name,
                'price'       => (float) $p->price,
                'inicial'     => $inicial,
                'aumento'     => $aumento,
                'vendido'     => $vendido,
                'queda'       => $inicial + $aumento - $vendido,
                'aumentos'    => $propios->where('tipo', 'AUMENTO')->values(),
            ];
        })->values();

        return [
            'user_id'    => $userId,
            'date'       => $date,
            'registrado' => $movs->where('tipo', 'INICIAL')->isNotEmpty(),
            'items'      => $items,
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id')->withTrashed();
    }
}
