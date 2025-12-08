<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    public function resumen(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        // FILTRO FECHAS BÁSICO SOBRE TABLA 'ventas'
        $salesQuery = DB::table('ventas')
            ->whereNull('deleted_at');

        if ($dateFrom) {
            $salesQuery->where('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $salesQuery->where('date', '<=', $dateTo);
        }

        // 1) VENTAS BRUTAS (solo INGRESO ACTIVO)
        $ventasBrutas = (clone $salesQuery)
            ->where('type', 'INGRESO')
            ->where('status', 'ACTIVO')
            ->sum('total');

        // 2) GASTOS / EGRESOS (EGRESO ACTIVO)
        $gastos = (clone $salesQuery)
            ->where('type', 'EGRESO')
            ->where('status', 'ACTIVO')
            ->sum('total');

        // 3) COSTO DE INSUMOS VENDIDOS (COGS)
        // venta_detalles -> ventas -> productos -> insumo_productos -> insumos
        $costoInsumosVendidosQuery = DB::table('venta_detalles as vd')
            ->join('ventas as v', 'v.id', '=', 'vd.venta_id')
            ->join('productos as p', 'p.id', '=', 'vd.product_id')
            ->join('insumo_productos as ip', function ($join) {
                $join->on('ip.producto_id', '=', 'p.id')
                    ->whereNull('ip.deleted_at');
            })
            ->join('insumos as i', 'i.id', '=', 'ip.insumo_id')
            ->whereNull('v.deleted_at')
            ->whereNull('p.deleted_at')
            ->whereNull('i.deleted_at')
            ->where('v.type', 'INGRESO')
            ->where('v.status', 'ACTIVO');

        if ($dateFrom) {
            $costoInsumosVendidosQuery->where('v.date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $costoInsumosVendidosQuery->where('v.date', '<=', $dateTo);
        }

        $costoInsumosVendidos = $costoInsumosVendidosQuery
            ->sum(DB::raw('vd.qty * ip.cantidad * i.costo'));

        // 4) UTILIDADES
        $utilidadBruta = $ventasBrutas - $costoInsumosVendidos;
        $utilidadNeta  = $ventasBrutas - $costoInsumosVendidos - $gastos;

        return response()->json([
            'date_from'               => $dateFrom,
            'date_to'                 => $dateTo,
            'ventas_brutas'           => round($ventasBrutas, 2),
            'costo_insumos_vendidos'  => round($costoInsumosVendidos, 2),
            'gastos'                  => round($gastos, 2),
            'utilidad_bruta'          => round($utilidadBruta, 2),
            'utilidad_neta'           => round($utilidadNeta, 2),
        ]);
    }
}
