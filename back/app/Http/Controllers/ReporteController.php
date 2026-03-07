<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    private function buildSistemaReporteByTipo(string $tipo, ?string $df, ?string $dt): array
    {
        $rows = DB::table('insumos as i')
            ->leftJoin('insumo_productos as ip', 'ip.insumo_id', '=', 'i.id')
            ->leftJoin('venta_detalles as d', function ($join) {
                $join->on('d.product_id', '=', 'ip.producto_id')
                    ->whereNull('d.deleted_at');
            })
            ->leftJoin('ventas as v', function ($join) use ($df, $dt) {
                $join->on('v.id', '=', 'd.venta_id')
                    ->where('v.type', 'INGRESO')
                    ->where('v.status', '!=', 'ANULADO')
                    ->whereNull('v.deleted_at');
                if ($df) {
                    $join->where('v.date', '>=', $df);
                }
                if ($dt) {
                    $join->where('v.date', '<=', $dt);
                }
            })
            ->whereNull('i.deleted_at')
            ->where('i.tipo_insumo', $tipo)
            ->select(
                'i.id as insumo_id',
                'i.nombre',
                'i.unidad',
                'i.costo',
                'i.stock',
                DB::raw('COALESCE(SUM(ip.cantidad * d.qty), 0) as usado')
            )
            ->groupBy('i.id', 'i.nombre', 'i.unidad', 'i.costo', 'i.stock')
            ->orderBy('i.nombre')
            ->get();

        $result = [];
        $resumen = [
            'cantidad_usada' => 0,
            'stock_actual' => 0,
            'cantidad_total' => 0,
            'costo_usado' => 0,
            'costo_actual' => 0,
        ];

        foreach ($rows as $row) {
            $costo = (float)$row->costo;
            $usado = (float)$row->usado;
            $stock = (float)$row->stock;
            $total = $stock + $usado;

            $costo_usado = round($usado * $costo, 2);
            $costo_actual = round($stock * $costo, 2);

            $result[] = [
                'insumo_id' => (int)$row->insumo_id,
                'nombre' => $row->nombre,
                'unidad' => $row->unidad,
                'total_cant' => round($total, 2),
                'usado' => round($usado, 2),
                'stock_actual' => round($stock, 2),
                'costo' => round($costo, 2),
                'costo_usado' => $costo_usado,
                'costo_actual' => $costo_actual,
            ];

            $resumen['cantidad_usada'] += $usado;
            $resumen['stock_actual'] += $stock;
            $resumen['cantidad_total'] += $total;
            $resumen['costo_usado'] += $costo_usado;
            $resumen['costo_actual'] += $costo_actual;
        }

        foreach ($resumen as $k => $v) {
            $resumen[$k] = round($v, 2);
        }

        return [
            'items' => $result,
            'resumen' => $resumen,
        ];
    }
    // GET /api/reportes/productos-resumen?date_from=...&date_to=...
    public function productosResumen(Request $request)
    {
        $df = $request->get('date_from');
        $dt = $request->get('date_to');

        // SOLO INGRESOS NO ANULADOS
        $q = DB::table('ventas as v')
            ->join('venta_detalles as d', 'd.venta_id', '=', 'v.id')
            ->join('productos as p', 'p.id', '=', 'd.product_id')
            ->when($df, fn($qq) => $qq->where('v.date', '>=', $df))
            ->when($dt, fn($qq) => $qq->where('v.date', '<=', $dt))
            ->whereNull('v.deleted_at')
            ->whereNull('d.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('v.type', 'INGRESO')
            ->where('v.status', '!=', 'ANULADO')
            // ✅ solo categorías que quieres
            ->whereIn('p.categoria', ['Pollos', 'Refrescos y Bebidas'])
            ->select(
                'p.id as product_id',
                'p.name',
                'p.categoria',
                'p.image',
                DB::raw('SUM(d.qty) as qty'),
                // si tu detalle no tiene price, cambia a SUM(d.subtotal)
                DB::raw('SUM(d.qty * d.price) as total')
            )
            ->groupBy('p.id', 'p.name', 'p.categoria', 'p.image')
            ->orderByDesc('total')
            ->get();

        return response()->json($q);
    }

    // GET /api/reportes/ventas?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD&user_id=...
    public function ventas(Request $request)
    {
        $df  = $request->get('date_from');
        $dt  = $request->get('date_to');
        $uid = $request->get('user_id');

        $base = DB::table('ventas as v')
            ->when($df,  fn ($q) => $q->where('v.date', '>=', $df))
            ->when($dt,  fn ($q) => $q->where('v.date', '<=', $dt))
            ->when($uid, fn ($q) => $q->where('v.user_id', $uid))
            // >>> ignorar ventas soft-deleted
            ->whereNull('v.deleted_at');

        $applyIngresosConDetalle = function ($q) {
            return $q
                ->where('v.type', 'INGRESO')
                ->where('v.status', '!=', 'ANULADO')
                ->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('venta_detalles as vd')
                        ->whereColumn('vd.venta_id', 'v.id')
                        ->whereNull('vd.deleted_at');
                });
        };

        // Totales principales
        $ingresos = $applyIngresosConDetalle(clone $base)->sum('v.total');

        $egresos = (clone $base)
            ->where('v.type', 'EGRESO')
            ->where('v.status', '!=', 'ANULADO')
            ->sum('v.total');

        $ventasCt = (clone $base)
            ->where('v.status', '!=', 'ANULADO')
            ->count();

        // Ítems vendidos
        $items = (clone $base)
            ->join('venta_detalles as d', 'd.venta_id', '=', 'v.id')
            // >>> ignorar detalles soft-deleted (si la tabla tiene deleted_at)
            ->whereNull('d.deleted_at')
            ->where('v.status', '!=', 'ANULADO')
            ->sum('d.qty');

        $ticket_prom = $ventasCt ? round($ingresos / $ventasCt, 2) : 0;

        // Pagos (incluye QR)
        $pagos = $applyIngresosConDetalle(clone $base)
            ->select(
                'v.pago',
                DB::raw('COUNT(*) as items'),
                DB::raw('SUM(v.total) as total')
            )
            ->groupBy('v.pago')
            ->get();

        $qr_total = $applyIngresosConDetalle(clone $base)
            ->where('v.pago', 'QR')
            ->sum('v.total');

        $qr_items = $applyIngresosConDetalle(clone $base)
            ->where('v.pago', 'QR')
            ->count();

        // Mesas
        $mesas = (clone $base)
            ->select(
                'v.mesa',
                DB::raw('COUNT(*) as items'),
                DB::raw('SUM(v.total) as total')
            )
            ->where('v.status', '!=', 'ANULADO')
            ->groupBy('v.mesa')
            ->get();

        // Serie por día
        $por_dia = (clone $base)
            ->select(
                'v.date',
                DB::raw("SUM(CASE WHEN v.type='INGRESO' AND v.status!='ANULADO' AND EXISTS (SELECT 1 FROM venta_detalles vd WHERE vd.venta_id = v.id AND vd.deleted_at IS NULL) THEN v.total ELSE 0 END) as ingreso"),
                DB::raw("SUM(CASE WHEN v.type='EGRESO'  AND v.status!='ANULADO' THEN v.total ELSE 0 END) as egreso")
            )
            ->groupBy('v.date')
            ->orderBy('v.date')
            ->get()
            ->map(function ($r) {
                $r->neto = (float)$r->ingreso - (float)$r->egreso;
                return $r;
            });

        // Totales por usuario
        $por_usuario = (clone $base)
            ->leftJoin('users as u', 'u.id', '=', 'v.user_id')
            ->select(
                'v.user_id',
                DB::raw("COALESCE(u.name,'—') as name"),
                DB::raw("SUM(CASE WHEN v.type='INGRESO' AND v.status!='ANULADO' AND EXISTS (SELECT 1 FROM venta_detalles vd WHERE vd.venta_id = v.id AND vd.deleted_at IS NULL) THEN v.total ELSE 0 END) as ingreso"),
                DB::raw("SUM(CASE WHEN v.type='EGRESO'  AND v.status!='ANULADO' THEN v.total ELSE 0 END) as egreso")
            )
            // >>> opcional: si users también tiene deleted_at
            // ->whereNull('u.deleted_at')
            ->groupBy('v.user_id', 'u.name')
            ->get()
            ->map(function ($r) {
                $r->neto = (float)$r->ingreso - (float)$r->egreso;
                return $r;
            });

        return response()->json([
            'filters' => [
                'date_from' => $df,
                'date_to'   => $dt,
                'user_id'   => $uid,
            ],
            'kpis' => [
                'ingresos'        => round($ingresos, 2),
                'egresos'         => round($egresos, 2),
                'neto'            => round($ingresos - $egresos, 2),
                'ventas'          => (int)$ventasCt,
                'items'           => (float)$items,
                'ticket_promedio' => (float)$ticket_prom,
            ],
            'pagos'       => $pagos,
            'qr'          => ['total' => round($qr_total, 2), 'items' => (int)$qr_items],
            'mesas'       => $mesas,
            'por_dia'     => $por_dia,
            'por_usuario' => $por_usuario,
        ]);
    }

    // GET /api/reportes/insumos?date_from=...&date_to=...&user_id=...
    public function insumos(Request $request)
    {
        $df  = $request->get('date_from');
        $dt  = $request->get('date_to');
        $uid = $request->get('user_id');

        // Consumo de insumos a partir de ventas (INGRESO, no anuladas)
        $q = DB::table('ventas as v')
            ->join('venta_detalles as d', 'd.venta_id', '=', 'v.id')
            ->join('insumo_productos as ip', 'ip.producto_id', '=', 'd.product_id')
            ->join('insumos as i', 'i.id', '=', 'ip.insumo_id')
            ->when($df,  fn ($qq) => $qq->where('v.date', '>=', $df))
            ->when($dt,  fn ($qq) => $qq->where('v.date', '<=', $dt))
            ->when($uid, fn ($qq) => $qq->where('v.user_id', $uid))
            ->where('v.type', 'INGRESO')
            ->where('v.status', '!=', 'ANULADO')
            ->whereNull('v.deleted_at')
            ->whereNull('d.deleted_at')
            ->whereNull('i.deleted_at')
            ->where('i.no_contar', 0); // 👈 NO CONTAR

        $rows = $q->select(
            'i.id as insumo_id',
            'i.nombre',
            'i.unidad',
            'i.costo',
            'i.stock', // stock actual en almacén
            DB::raw('SUM(ip.cantidad * d.qty) as usado') // cantidad utilizada
        )
            ->groupBy('i.id', 'i.nombre', 'i.unidad', 'i.costo', 'i.stock')
            ->orderBy('i.nombre')
            ->get();

        $costo_total          = 0;
        $cantidad_usada_total = 0;
        $stock_actual_total   = 0;

        foreach ($rows as $r) {
            $r->usado        = (float)$r->usado;
            $r->stock_actual = (float)$r->stock;              // lo que tengo hoy
            $r->total_cant   = $r->stock_actual + $r->usado;  // aprox. lo que tenía al inicio del periodo

            $r->costo_total  = round(((float)$r->costo) * $r->usado, 2);

            $costo_total          += $r->costo_total;
            $cantidad_usada_total += $r->usado;
            $stock_actual_total   += $r->stock_actual;

            unset($r->stock); // ya no lo necesitamos con ese nombre
        }

        // Ingresos del mismo rango para estimar ganancia
        $ingresos = DB::table('ventas as v')
            ->when($df,  fn ($qq) => $qq->where('v.date', '>=', $df))
            ->when($dt,  fn ($qq) => $qq->where('v.date', '<=', $dt))
            ->when($uid, fn ($qq) => $qq->where('v.user_id', $uid))
            ->where('v.type', 'INGRESO')
            ->where('v.status', '!=', 'ANULADO')
            ->whereNull('v.deleted_at') // >>> ignorar ventas soft-deleted
            ->sum('v.total');

        $listaExacta = [
            'Agua 600 ml',
            'No retornable 500 ml',
            'Retornable 1 Litro',
            'Retornable 1,5 Litros',
            'No retornable 2 litros',
            'Acuarius 2 litros',
            'Del Valle 2 litros',
            'Pollo (presa)',
            'Papa (por costo)',
            'Arroz (Por Costo)',
        ];

        $rows_filtrados = (clone $q)
            ->whereIn('i.nombre', $listaExacta)
            ->select(
                'i.id as insumo_id',
                'i.nombre',
                'i.unidad',
                'i.costo',
                'i.stock',
                DB::raw('SUM(ip.cantidad * d.qty) as usado')
            )
            ->groupBy('i.id', 'i.nombre', 'i.unidad', 'i.costo', 'i.stock')
            ->orderByRaw("FIELD(i.nombre, '".implode("','", $listaExacta)."')")
            ->get();

        $filtrado_costo_total = 0;
        $filtrado_qty_usada   = 0;
        $filtrado_stock_total = 0;

        foreach ($rows_filtrados as $r) {
            $r->usado        = (float)$r->usado;
            $r->stock_actual = (float)$r->stock;
            $r->total_cant   = $r->stock_actual + $r->usado;
            $r->costo_total  = round(((float)$r->costo) * $r->usado, 2);

            $filtrado_costo_total += $r->costo_total;
            $filtrado_qty_usada   += $r->usado;
            $filtrado_stock_total += $r->stock_actual;

            unset($r->stock);
        }

        return response()->json([
            'consumo' => $rows,
            'resumen' => [
                'costo_insumos'  => round($costo_total, 2),
                'ingresos'       => round($ingresos, 2),
                'ganancia_aprox' => round($ingresos - $costo_total, 2),
                'cantidad_usada' => round($cantidad_usada_total, 2),
                'stock_actual'   => round($stock_actual_total, 2),
                'cantidad_total' => round($cantidad_usada_total + $stock_actual_total, 2),
            ],

            // 👇 NUEVO
            'consumo_filtrado' => $rows_filtrados,
            'resumen_filtrado' => [
                'costo_insumos'  => round($filtrado_costo_total, 2),
                'cantidad_usada' => round($filtrado_qty_usada, 2),
                'stock_actual'   => round($filtrado_stock_total, 2),
                'cantidad_total' => round($filtrado_qty_usada + $filtrado_stock_total, 2),
            ],
        ]);
    }

    // GET /api/reportes/insumos-sistema?date_from=...&date_to=...
    public function insumosSistema(Request $request)
    {
        $df = $request->get('date_from');
        $dt = $request->get('date_to');

        return response()->json([
            'filters' => [
                'date_from' => $df,
                'date_to' => $dt,
            ],
            'alimentos' => $this->buildSistemaReporteByTipo('alimento', $df, $dt),
            'gaseosas' => $this->buildSistemaReporteByTipo('bebida', $df, $dt),
            'extras' => $this->buildSistemaReporteByTipo('extra', $df, $dt),
        ]);
    }
}
