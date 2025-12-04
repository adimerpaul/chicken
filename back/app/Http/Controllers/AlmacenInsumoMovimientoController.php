<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\AlmacenInsumoDetalle;
use App\Models\Insumo;
use App\Models\AlmacenInsumoMovimiento;
use App\Models\AlmacenInsumoMovimientoDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmacenInsumoMovimientoController extends Controller
{
    // GET /movimientos-almacen-insumos
    public function index()
    {
        return AlmacenInsumoMovimiento::withCount('detalles')
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();
    }

    // POST /movimientos-almacen-insumos
    // {
    //   fecha: '2025-12-04',
    //   nota: 'Reposición línea caliente',
    //   detalles: [
    //     { almacen_id: 1, insumo_id: 3, cantidad: 5, costo: 2.5 },
    //     ...
    //   ]
    // }
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'                   => 'required|date',
            'nota'                    => 'nullable|string',
            'detalles'                => 'required|array|min:1',
            'detalles.*.almacen_id'   => 'required|integer|exists:almacenes,id',
            'detalles.*.insumo_id'    => 'required|integer|exists:insumos,id',
            'detalles.*.cantidad'     => 'required|numeric|min:0.01',
            'detalles.*.costo'        => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $mov = AlmacenInsumoMovimiento::create([
                'fecha' => $data['fecha'],
                'nota'  => $data['nota'] ?? null,
                'total' => 0,
                'estado'=> 'ACTIVO',
            ]);

            $total = 0;

            foreach ($data['detalles'] as $item) {
                $almacen = Almacen::findOrFail($item['almacen_id']);
                $insumo  = Insumo::findOrFail($item['insumo_id']);

                $cantidad = $item['cantidad'];
                $costo    = $item['costo'] ?? ($almacen->costo ?? 0);
                $subtotal = $cantidad * $costo;

                // Validar stock suficiente en almacén
                if (($almacen->stock ?? 0) < $cantidad) {
                    abort(422, "Stock insuficiente en almacén para {$almacen->nombre}");
                }

                // crear detalle
                AlmacenInsumoDetalle::create([
                    'almacen_insumo_movimiento_id' => $mov->id,
                    'almacen_id'  => $almacen->id,
                    'insumo_id'   => $insumo->id,
                    'cantidad'    => $cantidad,
                    'costo'       => $costo,
                    'subtotal'    => $subtotal,
                ]);

                // actualizar stock
                $almacen->stock = ($almacen->stock ?? 0) - $cantidad;
                $almacen->save();

                $insumo->stock = ($insumo->stock ?? 0) + $cantidad;
                // opcional: actualizar costo del insumo
                $insumo->costo = $costo;
                $insumo->save();

                $total += $subtotal;
            }

            $mov->total = $total;
            $mov->save();

            return $mov->load(['detalles.almacen', 'detalles.insumo']);
        });
    }

    // GET /movimientos-almacen-insumos/{mov}
    public function show(AlmacenInsumoMovimiento $movimiento)
    {
        $movimiento->load(['detalles.almacen', 'detalles.insumo']);
        return $movimiento;
    }

    // PUT /movimientos-almacen-insumos/{mov}/anular
    public function anular(AlmacenInsumoMovimiento $movimiento)
    {
        if ($movimiento->estado === 'ANULADO') {
            return response()->json([
                'message' => 'El movimiento ya está anulado.'
            ], 400);
        }

        return DB::transaction(function () use ($movimiento) {
            $movimiento->load(['detalles.almacen', 'detalles.insumo']);

            foreach ($movimiento->detalles as $det) {
                $almacen = $det->almacen;
                $insumo  = $det->insumo;

                if ($almacen) {
                    $almacen->stock = ($almacen->stock ?? 0) + $det->cantidad;
                    $almacen->save();
                }

                if ($insumo) {
                    $insumo->stock = max(0, ($insumo->stock ?? 0) - $det->cantidad);
                    $insumo->save();
                }
            }

            $movimiento->estado = 'ANULADO';
            $movimiento->save();

            return response()->json([
                'message' => 'Movimiento anulado y stocks restaurados.',
                'movimiento' => $movimiento
            ]);
        });
    }

    // POST /movimientos-almacen-insumos/report
    // body: { fechaInicio, fechaFin, q }
    public function report(Request $request)
    {
        $fi = $request->input('fechaInicio');
        $ff = $request->input('fechaFin');
        $q  = $request->input('q');

        $query = AlmacenInsumoMovimiento::withCount('detalles')
            ->when($fi, fn ($qq) => $qq->where('fecha', '>=', $fi))
            ->when($ff, fn ($qq) => $qq->where('fecha', '<=', $ff))
            ->when($q, function ($qq) use ($q) {
                $q = trim($q);
                $qq->where(function ($sub) use ($q) {
                    $sub->where('nota', 'like', "%{$q}%")
                        ->orWhere('estado', 'like', "%{$q}%");
                });
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        return $query->get();
    }
}
