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
        $q = Venta::query()
            ->when($request->filled('date'),  fn($qb)=>$qb->where('date', $request->date))
            ->when($request->filled('type'),  fn($qb)=>$qb->where('type', $request->type))
            ->when($request->filled('status'),fn($qb)=>$qb->where('status',$request->status))
            ->orderByDesc('id')
            ->with('detalles');

        return $q->paginate(50);
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
