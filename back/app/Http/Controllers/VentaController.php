<?php

namespace App\Http\Controllers;

use App\Models\InsumoProducto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class VentaController extends Controller
{
    public function storeGasto(Request $request)
    {
        $data = $request->validate([
            'name'    => ['required','string','max:180'],  // descripción del gasto
            'total'   => ['required','numeric','min:0.01'],
            'pago'    => ['nullable','string','max:40'],   // EFECTIVO | QR
            'comment' => ['nullable','string','max:500'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            $now  = Carbon::now();
            $date = $now->toDateString();
            $time = $now->toTimeString();

            $numero = (int) (Venta::where('date', $date)->max('numero') ?? 0) + 1;

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

            // 👇 Importante: NO creamos detalles, NO descontamos insumos.
            return $venta->load('user');
        });
    }
    function anular(Venta $sale)
    {
//        solo se puede anular del ida
        $now = Carbon::now();
        if ($sale->date !== $now->toDateString()) {
            return response()->json(['message' => 'Solo se pueden anular ventas del día actual.'], 400);
        }
        $sale->status = 'ANULADO';
        $sale->save();

        $insumosProductosQuery = InsumoProducto::join('insumos as i', 'i.id', '=', 'insumo_productos.insumo_id')
            ->whereIn('insumo_productos.producto_id', function($query) use ($sale) {
                $query->select('product_id')
                    ->from('venta_detalles')
                    ->where('venta_id', $sale->id);
            })
            ->where('i.no_contar', 0);

// ✅ filtra por MESA / LLEVAR de esa venta
        $this->applyMesaFilter($insumosProductosQuery, $sale->mesa ?? 'MESA');

        $insumosProductos = $insumosProductosQuery
            ->select('insumo_productos.*')
            ->get();

        foreach ($insumosProductos as $ip) {
            $detalle = VentaDetalle::where('venta_id', $sale->id)
                ->where('product_id', $ip->producto_id)
                ->first();

            if ($detalle) {
                \App\Models\Insumo::where('id', $ip->insumo_id)
                    ->increment('stock', $ip->cantidad * (float)$detalle->qty);
            }
        }

        return response()->json(['message' => 'Venta anulada correctamente.']);
    }
    private function applyMesaFilter($query, string $mesa)
    {
        $mesa = strtoupper(trim($mesa));

        // Si usas más valores, ajusta aquí
        if ($mesa === 'MESA') {
            $query->where('i.es_mesa', 1);
        } elseif ($mesa === 'LLEVAR') {
            $query->where('i.es_llevar', 1);
        } else {
            // Si llega otro valor (DELIVERY, PEDIDOS YA, etc.)
            // decide qué hacer: NO descontar o descontar ambos.
            // Yo recomiendo: NO descontar, para evitar errores.
            $query->whereRaw('1=0');
        }

        return $query;
    }


    // GET /sales
    public function index(Request $request)
    {
//        $per = min(max((int)$request->get('per_page', 10000), 5), 100);
        $per = 10000;
        $user_id = $request->get('user_id');

        $q = Venta::query()
            ->when($request->filled('date'), fn($qb)      => $qb->where('date', $request->date))
            ->when($request->filled('date_from'), fn($qb) => $qb->where('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($qb)   => $qb->where('date', '<=', $request->date_to))
            ->when($request->filled('type'), fn($qb)      => $qb->where('type', $request->type))
            ->when($request->filled('status'), fn($qb)    => $qb->where('status', $request->status))
            ->when($request->filled('mesa'), fn($qb)      => $qb->where('mesa', $request->mesa))
            ->when($request->filled('pago'), fn($qb)      => $qb->where('pago', $request->pago))
            ->when($user_id, fn($qb) => $qb->where('user_id', $user_id))
            ->when($request->filled('q'), function($qb) use ($request){
                $q = $request->q;
                $qb->where(function($w) use ($q){
                    $w->where('name','like',"%$q%")
                        ->orWhere('numero','like',"%$q%")
                        ->orWhere('comment','like',"%$q%");
                });
            })
            ->with('detalles', 'user');

        $forAgg  = clone $q;
        $summary = [
            'count' => (clone $forAgg)->count(),
            'total' => (clone $forAgg)->sum('total'),
            'by_type' => (clone $forAgg)
                ->select('type', DB::raw('SUM(total) as total'), DB::raw('COUNT(*) as items'))
                ->groupBy('type')
                ->get(),
        ];

        $p = $q->orderByDesc('date')->orderByDesc('time')->paginate($per);

        return response()->json([
            'data'  => $p->items(),
            'meta'  => [
                'current_page' => $p->currentPage(),
                'last_page'    => $p->lastPage(),
                'per_page'     => $p->perPage(),
                'total'        => $p->total(),
                'from'         => $p->firstItem(),
                'to'           => $p->lastItem(),
            ],
            'summary' => $summary
        ]);
    }

    // POST /sales
    public function store(Request $request)
    {
        $data = $request->validate([
            'type'   => ['nullable','string', Rule::in(['INGRESO','EGRESO','CAJA'])],
            'status' => ['nullable','string'],
            'client.ci'   => ['nullable'],
            'client.name' => ['nullable','string','max:180'],
            'mesa'   => ['nullable','string','max:40'],
            'pago'   => ['nullable','string','max:40'],
            'llamada'=> ['nullable','integer','min:0'],
            'comment'=> ['nullable','string'],
            'products'   => ['nullable','array'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            $now  = Carbon::now();
            $date = $now->toDateString();
            $time = $now->toTimeString();

            $type = $data['type'] ?? 'INGRESO';
            if ($type === 'CAJA') {
                $venta = Venta::where('date', $date)
                    ->where('type', 'CAJA')
                    ->first();
                if (!$venta) {
                    $venta = Venta::create([
                        'date'   => $date,
                        'time'   => $time,
                        'total'  => 0,
                        'name'   => 'CAJA DIARIA',
                        'user_id'=> optional($request->user())->id,
                        'client_id' => null,
                        'type'   => 'CAJA',
                        'status' => 'ACTIVO',
                        'mesa'   => 'CAJA',
                        'pago'   => 'EFECTIVO',
                        'llamada'=> null,
                        'comment'=> 'CAJA DIARIA',
                        'numero' => 0,
                    ]);
                }
                $venta->total = $request->get('total', 0);
                $venta->save();
                return $venta;
            }

            $numero = (int) (Venta::where('date', $date)->max('numero') ?? 0) + 1;

            $total = 0;
            foreach ($data['products'] ?? [] as $item) {
                $total += (float)$item['price'] * (float)$item['cantidadSale'];
            }
            if ($total <= 0) {
                $total = $request->get('total', 0);
            }

            $venta = Venta::create([
                'date'   => $date,
                'time'   => $time,
                'total'  => $total,
                'name'   => $data['client']['name'] ?? 'SN',
                'user_id'=> optional($request->user())->id,
                'client_id' => null,
                'type'   => $data['type']   ?? 'INGRESO',
                'status' => $data['status'] ?? 'ACTIVO',
                'mesa'   => $data['mesa']   ?? 'MESA',
                'pago'   => $data['pago']   ?? 'EFECTIVO',
                'llamada'=> $data['llamada'] ?? null,
                'comment'=> $data['comment'] ?? null,
                'numero' => $numero,
            ]);

            $detalles = [];
            foreach ($data['products'] ?? [] as $item) {
                $qty = (float)$item['cantidadSale'];
                $price = (float)$item['price'];
                $detalles[] = new VentaDetalle([
                    'product_id' => $item['id'] ?? null,
                    'name'       => $item['name'],
                    'price'      => $price,
                    'qty'        => $qty,
                    'subtotal'   => $price * $qty,
                ]);

                $mesaVenta = $data['mesa'] ?? 'MESA'; // o $venta->mesa después de crearla

                $insumosProductosQuery = InsumoProducto::join('insumos as i', 'i.id', '=', 'insumo_productos.insumo_id')
                    ->where('insumo_productos.producto_id', $item['id'])
                    ->where('i.no_contar', 0);

// ✅ filtra por MESA / LLEVAR usando flags del insumo
                $this->applyMesaFilter($insumosProductosQuery, $mesaVenta);

                $insumosProductos = $insumosProductosQuery
                    ->select('insumo_productos.*')
                    ->get();

                foreach ($insumosProductos as $ip) {
                    \App\Models\Insumo::where('id', $ip->insumo_id)
                        ->decrement('stock', $ip->cantidad * $qty);
                }
            }
            $venta->detalles()->saveMany($detalles);

            return $venta->load('detalles','user');
        });
    }

    // GET /sales/{venta}
    public function show(Venta $sale)
    {
        return $sale->load('detalles','user');
    }

    // GET /sales/report/by-user
    public function resumenPorUsuario(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');
        $userId   = $request->input('user_id'); // opcional

        $query = Venta::with('user')
            ->where('status', 'ACTIVO');

        if ($dateFrom) {
            $query->whereDate('date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('date', '<=', $dateTo);
        }
        if ($userId) {
            $query->where('user_id', $userId);
        }

        $ventas = $query->get();

        $usuarios = $ventas->groupBy('user_id')->map(function ($rows) {
            $u = $rows->first()->user;

            $ingresos = $rows->where('type', 'INGRESO')->sum('total');
            $egresos  = $rows->where('type', 'EGRESO')->sum('total');
            $cajaIni  = $rows->where('type', 'CAJA')->sum('total');
            $tickets  = $rows->where('type', 'INGRESO')->count();

            return [
                'user_id'       => $u->id,
                'user_name'     => $u->name,
                'total_ingresos'=> $ingresos,
                'total_egresos' => $egresos,
                'total_caja'    => $cajaIni,
                'neto'          => $ingresos + $cajaIni - $egresos,
                'tickets'       => $tickets,
            ];
        })->values();

        $detalles = VentaDetalle::with('venta.user')
            ->whereHas('venta', function ($q) use ($dateFrom, $dateTo, $userId) {
                $q->where('status', 'ACTIVO');
                if ($dateFrom) {
                    $q->whereDate('date', '>=', $dateFrom);
                }
                if ($dateTo) {
                    $q->whereDate('date', '<=', $dateTo);
                }
                if ($userId) {
                    $q->where('user_id', $userId);
                }
                $q->where('type', 'INGRESO');
            })
            ->get();

        $productos = $detalles->groupBy(function ($d) {
            return $d->venta->user_id;
        })->map(function ($rows, $userId) {
            $userName = optional($rows->first()->venta->user)->name;

            $items = $rows->groupBy('product_id')->map(function ($items) {
                $first = $items->first();
                return [
                    'product_id' => $first->product_id,
                    'name'       => $first->name,
                    'qty'        => $items->sum('qty'),
                    'subtotal'   => $items->sum('subtotal'),
                ];
            })->values();

            return [
                'user_id'   => $userId,
                'user_name' => $userName,
                'items'     => $items,
            ];
        })->values();

        // ventas detalladas solo cuando hay user_id (para reporteVentasPorUsuario)
        $ventasDetalladas = null;
        if ($userId) {
            $ventasDetalladas = $ventas->map(function ($v) {
                return [
                    'id'      => $v->id,
                    'numero'  => $v->numero,
                    'date'    => $v->date,
                    'time'    => $v->time,
                    'mesa'    => $v->mesa,
                    'pago'    => $v->pago,
                    'type'    => $v->type,
                    'status'  => $v->status,
                    'total'   => $v->total,
                    'user_id' => $v->user_id,
                ];
            })->values();
        }

        return response()->json([
            'usuarios'  => $usuarios,
            'productos' => $productos,
            'ventas'    => $ventasDetalladas,
        ]);
    }
}
