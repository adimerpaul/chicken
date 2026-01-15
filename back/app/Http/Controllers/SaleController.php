<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * GET /api/salesResumen?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD
     */
    public function resumen(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        // Base ventas (no borradas)
        $salesQuery = DB::table('ventas as v')
            ->whereNull('v.deleted_at');

        if ($dateFrom) $salesQuery->where('v.date', '>=', $dateFrom);
        if ($dateTo)   $salesQuery->where('v.date', '<=', $dateTo);

        // 1) Ventas brutas (INGRESO ACTIVO)
        $ventasBrutas = (clone $salesQuery)
            ->where('v.type', 'INGRESO')
            ->where('v.status', 'ACTIVO')
            ->sum('v.total');

        // 2) Gastos / egresos (EGRESO ACTIVO)
        $gastos = (clone $salesQuery)
            ->where('v.type', 'EGRESO')
            ->where('v.status', 'ACTIVO')
            ->sum('v.total');

        /**
         * 3) COGS: costo de insumos vendidos
         *
         * ✅ Fix típico del error: si tu total está gigante (miles),
         * es porque el join a insumo_productos multiplica filas.
         * Eso es CORRECTO si el producto tiene varios insumos, pero
         * si la cantidad de insumos es alta, sube bastante el costo.
         *
         * Este cálculo es:
         * SUM( vd.qty * ip.cantidad * i.costo )
         */
        $costoInsumosVendidosQuery = DB::table('venta_detalles as vd')
            ->join('ventas as v', 'v.id', '=', 'vd.venta_id')
            ->join('productos as p', 'p.id', '=', 'vd.product_id')
            ->join('insumo_productos as ip', function ($join) {
                $join->on('ip.producto_id', '=', 'p.id')
                    ->whereNull('ip.deleted_at');
            })
            ->join('insumos as i', function ($join) {
                $join->on('i.id', '=', 'ip.insumo_id')
                    ->whereNull('i.deleted_at');
            })
            ->whereNull('vd.deleted_at')   // por si tu detalle tiene soft deletes
            ->whereNull('v.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('v.type', 'INGRESO')
            ->where('v.status', 'ACTIVO');

        if ($dateFrom) $costoInsumosVendidosQuery->where('v.date', '>=', $dateFrom);
        if ($dateTo)   $costoInsumosVendidosQuery->where('v.date', '<=', $dateTo);

        $costoInsumosVendidos = $costoInsumosVendidosQuery
            ->sum(DB::raw('COALESCE(vd.qty,0) * COALESCE(ip.cantidad,0) * COALESCE(i.costo,0)'));

        // 4) Utilidades
        $utilidadBruta = $ventasBrutas - $costoInsumosVendidos;
        $utilidadNeta  = $ventasBrutas - $costoInsumosVendidos - $gastos;

        return response()->json([
            'date_from'              => $dateFrom,
            'date_to'                => $dateTo,
            'ventas_brutas'          => round($ventasBrutas, 2),
            'costo_insumos_vendidos' => round($costoInsumosVendidos, 2),
            'gastos'                 => round($gastos, 2),
            'utilidad_bruta'         => round($utilidadBruta, 2),
            'utilidad_neta'          => round($utilidadNeta, 2),
        ]);
    }

    /**
     * GET /api/salesResumenProductosMesa?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD
     *
     * Devuelve productos vendidos en MESA agrupados por producto.
     * Campos esperados:
     * - product_id
     * - producto
     * - unidad
     * - cantidad
     * - total_venta
     * - costo_insumos
     * - utilidad
     */
    public function resumenProductosMesa(Request $request)
    {
        return $this->resumenProductosPorTipo($request, 'MESA');
    }

    /**
     * GET /api/salesResumenProductosLlevar?date_from=YYYY-MM-DD&date_to=YYYY-MM-DD
     */
    public function resumenProductosLlevar(Request $request)
    {
        return $this->resumenProductosPorTipo($request, 'LLEVAR');
    }

    /**
     * Helper interno para MESA / LLEVAR
     *
     * ⚠️ Ajusta el campo si no usas "v.mesa".
     * Puede ser: v.tipo_venta, v.tipo_pedido, etc.
     */
    private function resumenProductosPorTipo(Request $request, string $tipoMesa)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        /**
         * Estructura:
         * - venta_detalles (vd): qty, price, subtotal? (según tu DB)
         * - ventas (v): mesa (MESA/LLEVAR), type, status, date
         * - productos (p)
         * - insumo_productos (ip)
         * - insumos (i)
         *
         * total_venta = SUM(vd.qty * vd.price)  (si tu detalle no tiene price, se usa vd.subtotal)
         * costo_insumos = SUM(vd.qty * ip.cantidad * i.costo)
         */
        $q = DB::table('venta_detalles as vd')
            ->join('ventas as v', 'v.id', '=', 'vd.venta_id')
            ->join('productos as p', 'p.id', '=', 'vd.product_id')
            ->leftJoin('insumo_productos as ip', function ($join) {
                $join->on('ip.producto_id', '=', 'p.id')
                    ->whereNull('ip.deleted_at');
            })
            ->leftJoin('insumos as i', function ($join) {
                $join->on('i.id', '=', 'ip.insumo_id')
                    ->whereNull('i.deleted_at');
            })
            ->whereNull('vd.deleted_at')
            ->whereNull('v.deleted_at')
            ->whereNull('p.deleted_at')
            ->where('v.type', 'INGRESO')
            ->where('v.status', 'ACTIVO')
            // ✅ AQUÍ: campo que define MESA/LLEVAR
            ->where('v.mesa', $tipoMesa);

        if ($dateFrom) $q->where('v.date', '>=', $dateFrom);
        if ($dateTo)   $q->where('v.date', '<=', $dateTo);

        /**
         * Si tu venta_detalles NO tiene price, cambia:
         *   SUM(vd.qty * vd.price)
         * por:
         *   SUM(vd.subtotal)
         */
        $rows = $q->select([
            'p.id as product_id',
            'p.name as producto',
            'p.unit as unidad',
            DB::raw('SUM(COALESCE(vd.qty,0)) as cantidad'),

            // total_venta:
            DB::raw('SUM(COALESCE(vd.qty,0) * COALESCE(vd.price,0)) as total_venta'),

            // costo_insumos:
            DB::raw('SUM(COALESCE(vd.qty,0) * COALESCE(ip.cantidad,0) * COALESCE(i.costo,0)) as costo_insumos'),
        ])
            ->groupBy('p.id', 'p.name', 'p.unit')
            ->orderByDesc('total_venta')
            ->get();

        // utilidad calculada en PHP para evitar repetir expresión gigante en SQL
        $out = $rows->map(function ($r) {
            $total = (float) ($r->total_venta ?? 0);
            $costo = (float) ($r->costo_insumos ?? 0);
            return [
                'product_id'    => $r->product_id,
                'producto'      => $r->producto,
                'unidad'        => $r->unidad,
                'cantidad'      => round((float)$r->cantidad, 2),
                'total_venta'   => round($total, 2),
                'costo_insumos' => round($costo, 2),
                'utilidad'      => round($total - $costo, 2),
            ];
        });

        return response()->json($out);
    }
}
