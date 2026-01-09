<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Insumo;
use App\Models\Venta;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    // GET /compras
    public function index()
    {
        return Compra::withCount('detalles')
            ->orderBy('id', 'desc')
            ->get();
    }

    // GET /compras/{id}
    public function show($id)
    {
        return Compra::with(['detalles.insumo'])->findOrFail($id);
    }

    // POST /compras   (crea compra + detalles + suma totales + incrementa stock)
    public function store(Request $request)
    {
        // Estructura esperada:
        // {
        //   "fecha":"2025-11-10",
        //   "proveedor":"Fulano",
        //   "nota":"opc",
        //   "detalles":[
        //     {"insumo_id":1,"cantidad":5,"costo":12.5},
        //     {"insumo_id":3,"cantidad":2,"costo":7}
        //   ]
        // }

        return DB::transaction(function () use ($request) {
            $compra = Compra::create([
                'fecha'     => $request->input('fecha', now()->toDateString()),
                'proveedor' => $request->input('proveedor'),
                'nota'      => $request->input('nota'),
                'total'     => 0, // se calcula abajo
                'estado'    => 'ACTIVO',
            ]);

            $total = 0;
            foreach ((array) $request->input('detalles', []) as $d) {
                $cantidad = (float) ($d['cantidad'] ?? 0);
                $costo    = (float) ($d['costo']    ?? 0);
                $subtotal = $cantidad * $costo;
                $total   += $subtotal;

                $det = CompraDetalle::create([
                    'compra_id' => $compra->id,
                    'insumo_id' => $d['insumo_id'],
                    'cantidad'  => $cantidad,
                    'costo'     => $costo,
                    'subtotal'  => $subtotal,
                ]);

                // Incrementa stock de insumo
                $insumo = Insumo::findOrFail($d['insumo_id']);
                $insumo->stock = ($insumo->stock ?? 0) + $cantidad;
                // (opcional) actualizar último costo:
                $insumo->costo = $costo;
                $insumo->save();
            }

            $compra->update(['total' => $total]);

//            $crear compra en venta
//            $data = $request->validate([
//                'name'    => ['required','string','max:180'],  // descripción del gasto
//                'total'   => ['required','numeric','min:0.01'],
//                'pago'    => ['nullable','string','max:40'],   // EFECTIVO | QR
//                'comment' => ['nullable','string','max:500'],
//            ]);
//
//            return DB::transaction(function () use ($data, $request) {
            $data = [
                    'name'    => 'Gasto por compra insumos ID '.$compra->id,
                    'total'   => $total,
                    'pago'    => 'EFECTIVO',
                    'comment' => 'Gasto generado automáticamente al registrar compra de insumos ID '.$compra->id,
            ];
                $now  = Carbon::now();
                $date = $now->toDateString();
                $time = $now->toTimeString();

                $numero = (int) (Venta::where('date', $date)->max('numero') ?? 0) + 1;
//
                $venta = Venta::create([
                    'date'    => $date,
                    'time'    => $time,
                    'total'   => (float)$data['total'],
                    'name'    => $data['name'], // descripción del gasto
                    'user_id' => optional($request->user())->id,
                    'client_id' => null,

                    'type'    => 'EGRESO',
                    'status'  => 'ACTIVO',
                    'mesa'    => 'GASTO',
                    'pago'    => $data['pago'] ?? 'EFECTIVO',
                    'llamada' => null,
                    'comment' => $data['comment'] ?? null,
                    'numero'  => $numero,
                ]);

            return Compra::with(['detalles.insumo'])->find($compra->id);
        });
    }

    // PUT /compras/{compra}/anular   (revierte stock y marca ANULADO)
    public function anular($id)
    {
        return DB::transaction(function () use ($id) {
            $compra = Compra::with('detalles')->findOrFail($id);

            if ($compra->estado === 'ANULADO') {
                return response()->json(['message' => 'La compra ya está anulada'], 422);
            }

            // Revertir stock
            foreach ($compra->detalles as $det) {
                $insumo = Insumo::find($det->insumo_id);
                if ($insumo) {
                    $insumo->stock = ($insumo->stock ?? 0) - (float) $det->cantidad;
                    $insumo->save();
                }
            }

            $compra->estado = 'ANULADO';
            $compra->save();

            return response()->json(['message' => 'Compra anulada y stock revertido']);
        });
    }

    // DELETE /compras/{id}  (borra; si está ACTIVO primero revierte stock)
    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $compra = Compra::with('detalles')->findOrFail($id);

            if ($compra->estado === 'ACTIVO') {
                foreach ($compra->detalles as $det) {
                    $insumo = Insumo::find($det->insumo_id);
                    if ($insumo) {
                        $insumo->stock = ($insumo->stock ?? 0) - (float) $det->cantidad;
                        $insumo->save();
                    }
                }
            }

            $compra->delete();
            return response()->json(['message' => 'Compra eliminada']);
        });
    }
    public function report(Request $req)
    {
        // filtros: fechaInicio, fechaFin, q (texto libre: proveedor/nota)
        $ini = $req->input('fechaInicio');
        $fin = $req->input('fechaFin');
        $q   = trim((string)$req->input('q', ''));

        $query = Compra::withCount('detalles')->orderBy('id', 'desc');

        if ($ini) $query->whereDate('fecha', '>=', $ini);
        if ($fin) $query->whereDate('fecha', '<=', $fin);
        if ($q !== '') {
            $query->where(function($s) use ($q){
                $s->where('proveedor', 'like', "%{$q}%")
                    ->orWhere('nota', 'like', "%{$q}%")
                    ->orWhere('estado', 'like', "%{$q}%");
            });
        }
        return $query->get();
    }

    public function resumenInsumos(Request $req)
    {
        // Devuelve por insumo (en un rango): cantidad comprada y gasto
        $ini = $req->input('fechaInicio');
        $fin = $req->input('fechaFin');
        $q   = trim((string)$req->input('q', '')); // buscar por nombre de insumo opcional

        $det = \DB::table('compra_detalles as d')
            ->join('compras as c', 'c.id', '=', 'd.compra_id')
            ->join('insumos as i', 'i.id', '=', 'd.insumo_id')
            ->select(
                'i.id as insumo_id',
                'i.nombre',
                'i.unidad',
                \DB::raw('SUM(d.cantidad) as comprado'),
                \DB::raw('SUM(d.subtotal) as gasto'),
                \DB::raw('MAX(d.costo) as ultimo_costo')
            )
            ->when($ini, fn($q1) => $q1->whereDate('c.fecha', '>=', $ini))
            ->when($fin, fn($q1) => $q1->whereDate('c.fecha', '<=', $fin))
            ->whereNull('c.deleted_at')
            ->where('c.estado', '!=', 'ANULADO')
            ->groupBy('i.id','i.nombre','i.unidad');

        if ($q !== '') $det->having('i.nombre', 'like', "%{$q}%");

        $resumen = $det->get();

        // Adjunta stock actual del insumo para mostrar en la grilla
        $stocks = Insumo::select('id','stock','costo')->get()->keyBy('id');
        $resumen = $resumen->map(function($r) use ($stocks){
            $st = $stocks[$r->insumo_id] ?? null;
            $r->stock_actual = $st?->stock ?? 0;
            $r->costo_actual = $st?->costo ?? 0;
            return $r;
        });

        return $resumen;
    }

}
