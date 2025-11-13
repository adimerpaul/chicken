<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteController extends Controller
{
    // GET /api/reportes/ventas?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD&user_id=...
    public function ventas(Request $request)
    {
        $df  = $request->get('date_from');
        $dt  = $request->get('date_to');
        $uid = $request->get('user_id');

        $base = DB::table('ventas as v')
            ->when($df,  fn ($q) => $q->where('v.date', '>=', $df))
            ->when($dt,  fn ($q) => $q->where('v.date', '<=', $dt))
            ->when($uid, fn ($q) => $q->where('v.user_id', $uid));

        // Totales principales
        $ingresos = (clone $base)
            ->where('v.type', 'INGRESO')
            ->where('v.status', '!=', 'ANULADO')
            ->sum('v.total');

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
            ->where('v.status', '!=', 'ANULADO')
            ->sum('d.qty');

        $ticket_prom = $ventasCt ? round($ingresos / $ventasCt, 2) : 0;

        // Pagos (incluye QR)
        $pagos = (clone $base)
            ->select(
                'v.pago',
                DB::raw('COUNT(*) as items'),
                DB::raw('SUM(v.total) as total')
            )
            ->where('v.status', '!=', 'ANULADO')
            ->groupBy('v.pago')
            ->get();

        $qr_total = (clone $base)
            ->where('v.pago', 'QR')
            ->where('v.status', '!=', 'ANULADO')
            ->sum('v.total');

        $qr_items = (clone $base)
            ->where('v.pago', 'QR')
            ->where('v.status', '!=', 'ANULADO')
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
                DB::raw("SUM(CASE WHEN v.type='INGRESO' AND v.status!='ANULADO' THEN v.total ELSE 0 END) as ingreso"),
                DB::raw("SUM(CASE WHEN v.type='EGRESO'  AND v.status!='ANULADO' THEN v.total ELSE 0 END) as egreso")
            )
            ->groupBy('v.date')
            ->orderBy('v.date')
            ->get()
            ->map(function ($r) {
                $r->neto = (float)$r->ingreso - (float)$r->egreso;
                return $r;
            });

        // Totales por usuario (por si luego quieres usarlo)
        $por_usuario = (clone $base)
            ->leftJoin('users as u', 'u.id', '=', 'v.user_id')
            ->select(
                'v.user_id',
                DB::raw("COALESCE(u.name,'—') as name"),
                DB::raw("SUM(CASE WHEN v.type='INGRESO' AND v.status!='ANULADO' THEN v.total ELSE 0 END) as ingreso"),
                DB::raw("SUM(CASE WHEN v.type='EGRESO'  AND v.status!='ANULADO' THEN v.total ELSE 0 END) as egreso")
            )
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
            'pagos'      => $pagos,
            'qr'         => ['total' => round($qr_total, 2), 'items' => (int)$qr_items],
            'mesas'      => $mesas,
            'por_dia'    => $por_dia,
            'por_usuario'=> $por_usuario,
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
            ->where('v.status', '!=', 'ANULADO');

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
            ->sum('v.total');

        return response()->json([
            'consumo' => $rows,
            'resumen' => [
                // COSTOS
                'costo_insumos'  => round($costo_total, 2),
                'ingresos'       => round($ingresos, 2),
                'ganancia_aprox' => round($ingresos - $costo_total, 2),
                // CANTIDADES (almacén)
                'cantidad_usada' => round($cantidad_usada_total, 2),
                'stock_actual'   => round($stock_actual_total, 2),
                'cantidad_total' => round($cantidad_usada_total + $stock_actual_total, 2),
            ],
        ]);
    }
}
