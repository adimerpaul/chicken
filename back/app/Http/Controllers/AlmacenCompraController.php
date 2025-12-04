<?php

namespace App\Http\Controllers;

use App\Models\Almacen;
use App\Models\AlmacenCompra;
use App\Models\AlmacenCompraDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AlmacenCompraController extends Controller
{
    // GET /compras-almacen
    public function index()
    {
        return AlmacenCompra::withCount('detalles')
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->limit(100)
            ->get();
    }

    // POST /compras-almacen
    // payload:
    // {
    //   fecha: '2025-01-01',
    //   proveedor: 'X',
    //   nota: 'Y',
    //   detalles: [
    //     { almacen_id: 1, cantidad: 10, costo: 5.5 },
    //     ...
    //   ]
    // }
    public function store(Request $request)
    {
        $data = $request->validate([
            'fecha'                 => 'required|date',
            'proveedor'             => 'nullable|string|max:255',
            'nota'                  => 'nullable|string',
            'detalles'              => 'required|array|min:1',
            'detalles.*.almacen_id' => 'required|integer|exists:almacenes,id',
            'detalles.*.cantidad'   => 'required|numeric|min:0.01',
            'detalles.*.costo'      => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data) {
            $compra = AlmacenCompra::create([
                'fecha'     => $data['fecha'],
                'proveedor' => $data['proveedor'] ?? null,
                'nota'      => $data['nota'] ?? null,
                'total'     => 0,
                'estado'    => 'ACTIVO',
            ]);

            $total = 0;

            foreach ($data['detalles'] as $item) {
                $subtotal = $item['cantidad'] * $item['costo'];

                AlmacenCompraDetalle::create([
                    'almacen_compra_id' => $compra->id,
                    'almacen_id'        => $item['almacen_id'],
                    'cantidad'          => $item['cantidad'],
                    'costo'             => $item['costo'],
                    'subtotal'          => $subtotal,
                ]);

                // actualizar stock y costo del almacén
                $almacen = Almacen::findOrFail($item['almacen_id']);
                $almacen->stock = ($almacen->stock ?? 0) + $item['cantidad'];
                $almacen->costo = $item['costo']; // último costo
                $almacen->save();

                $total += $subtotal;
            }

            $compra->total = $total;
            $compra->save();

            return $compra->load(['detalles.almacen']);
        });
    }

    // GET /compras-almacen/{compra}
    public function show($id)
    {
//        $compra->load(['detalles.almacen']);
//        return $compra;
        $compra = AlmacenCompra::with(['detalles.almacen'])->findOrFail($id);
        return $compra;
    }

    // PUT /compras-almacen/{compra}
    // Solo actualizar datos generales (no stock)
    public function update(Request $request, AlmacenCompra $compra)
    {
        $data = $request->validate([
            'fecha'     => 'sometimes|date',
            'proveedor' => 'nullable|string|max:255',
            'nota'      => 'nullable|string',
        ]);

        $compra->update($data);

        return $compra->fresh();
    }

    // PUT /compras-almacen/{compra}/anular
    public function anular(AlmacenCompra $compra)
    {
        if ($compra->estado === 'ANULADO') {
            return response()->json([
                'message' => 'La compra ya está anulada.'
            ], 400);
        }

        return DB::transaction(function () use ($compra) {
            $compra->load('detalles.almacen');

            foreach ($compra->detalles as $det) {
                if ($det->almacen) {
                    $almacen = $det->almacen;
                    $almacen->stock = max(0, ($almacen->stock ?? 0) - $det->cantidad);
                    $almacen->save();
                }
            }

            $compra->estado = 'ANULADO';
            $compra->save();

            return response()->json([
                'message' => 'Compra anulada y stock revertido.',
                'compra'  => $compra
            ]);
        });
    }

    // POST /compras-almacen/report
    // body: { fechaInicio, fechaFin, q }
    public function report(Request $request)
    {
        $fi = $request->input('fechaInicio');
        $ff = $request->input('fechaFin');
        $q  = $request->input('q');

        $query = AlmacenCompra::withCount('detalles')
            ->when($fi, fn ($qq) => $qq->where('fecha', '>=', $fi))
            ->when($ff, fn ($qq) => $qq->where('fecha', '<=', $ff))
            ->when($q, function ($qq) use ($q) {
                $q = trim($q);
                $qq->where(function ($sub) use ($q) {
                    $sub->where('proveedor', 'like', "%{$q}%")
                        ->orWhere('nota', 'like', "%{$q}%")
                        ->orWhere('estado', 'like', "%{$q}%");
                });
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc');

        return $query->get();
    }

    // GET /compras-almacen/{compra}/pdf
    public function pdf(AlmacenCompra $compra)
    {
        $compra->load(['detalles.almacen']);

        $pdf = Pdf::loadView('pdf.compra_almacen', [
            'compra' => $compra,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream('COMPRA_ALMACEN_' . $compra->id . '.pdf');
    }
}
