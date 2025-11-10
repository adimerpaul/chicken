<?php
namespace App\Http\Controllers;

use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class VentaController extends Controller
{
    // GET /sales (listado básico)
    public function index(Request $request)
    {
        $per = min(max((int)$request->get('per_page', 20), 5), 100);

        $q = \App\Models\Venta::query()
            ->when($request->filled('date'), fn($qb)      => $qb->where('date', $request->date))
            ->when($request->filled('date_from'), fn($qb) => $qb->where('date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn($qb)   => $qb->where('date', '<=', $request->date_to))
            ->when($request->filled('type'), fn($qb)      => $qb->where('type', $request->type))
            ->when($request->filled('status'), fn($qb)    => $qb->where('status', $request->status))
            ->when($request->filled('mesa'), fn($qb)      => $qb->where('mesa', $request->mesa))
            ->when($request->filled('pago'), fn($qb)      => $qb->where('pago', $request->pago))
            ->when($request->filled('q'), function($qb) use ($request){
                $q = $request->q;
                $qb->where(function($w) use ($q){
                    $w->where('name','like',"%$q%")
                        ->orWhere('numero','like',"%$q%")
                        ->orWhere('comment','like',"%$q%");
                });
            })
            ->with('detalles');

        // Summary del conjunto filtrado
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


    // POST /sales   (crear venta desde el carrito)
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

            'products'   => ['required','array','min:1'],
            'products.*.id'           => ['nullable','integer'],
            'products.*.name'         => ['required','string','max:255'],
            'products.*.price'        => ['required','numeric','min:0'],
            'products.*.cantidadSale' => ['required','numeric','min:1'],
        ]);

        return DB::transaction(function () use ($data, $request) {
            $now  = Carbon::now();
            $date = $now->toDateString();
            $time = $now->toTimeString();

            // correlativo diario
            $numero = (int) (Venta::where('date', $date)->max('numero') ?? 0) + 1;

            // total desde el carrito
            $total = 0;
            foreach ($data['products'] as $item) {
                $total += (float)$item['price'] * (float)$item['cantidadSale'];
            }

            $venta = Venta::create([
                'date'   => $date,
                'time'   => $time,
                'total'  => $total,
                'name'   => $data['client']['name'] ?? 'SN',
                'user_id'=> optional($request->user())->id,   // si usas Sanctum
                'client_id' => null,                           // opcional si tienes tabla clients
                'type'   => $data['type']   ?? 'INGRESO',
                'status' => $data['status'] ?? 'ACTIVO',
                'mesa'   => $data['mesa']   ?? 'MESA',
                'pago'   => $data['pago']   ?? 'EFECTIVO',
                'llamada'=> $data['llamada'] ?? null,
                'comment'=> $data['comment'] ?? null,
                'numero' => $numero,
            ]);

            $detalles = [];
            foreach ($data['products'] as $item) {
                $qty = (float)$item['cantidadSale'];
                $price = (float)$item['price'];
                $detalles[] = new VentaDetalle([
                    'product_id' => $item['id'] ?? null,
                    'name'       => $item['name'],
                    'price'      => $price,
                    'qty'        => $qty,
                    'subtotal'   => $price * $qty,
                ]);

                // Si quieres manejar stock, este es el lugar para restar/sumar.
                // Producto::where('id', $item['id'])->decrement('stock', $qty);
            }
            $venta->detalles()->saveMany($detalles);

            // respuesta completa para imprimir
            return $venta->load('detalles');
        });
    }

    // GET /sales/{venta}
    public function show(Venta $sale)
    {
        return $sale->load('detalles');
    }
}
