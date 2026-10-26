<?php

namespace App\Http\Controllers;

use App\Models\Despacho;
use App\Models\DespachoProducto;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Despachos de pollo.
 * 1) Quien recibe (permiso "Despachos") registra los productos que llegan,
 *    la cantidad, el peso que pesó y comentarios -> estado PENDIENTE.
 * 2) El administrador (permiso "Despachos admin") completa los datos de la
 *    nota del proveedor (envase, peso, tara, p. bruto, p. neto, precio) -> COMPLETADO.
 *    Así se compara el peso de la nota contra el peso realmente recibido.
 */
class DespachoController extends Controller
{
    // POST /despachos/report
    // body: { fechaInicio, fechaFin, q, estado }
    public function report(Request $request)
    {
        $this->puedeVer($request);

        $fi     = $request->input('fechaInicio');
        $ff     = $request->input('fechaFin');
        $q      = $request->input('q');
        $estado = $request->input('estado');

        return Despacho::with(['user:id,name,username', 'admin:id,name,username'])
            ->withCount('detalles')
            ->when($fi, fn ($qq) => $qq->where('fecha', '>=', $fi))
            ->when($ff, fn ($qq) => $qq->where('fecha', '<=', $ff))
            ->when($estado, fn ($qq) => $qq->where('estado', $estado))
            ->when($q, function ($qq) use ($q) {
                $q = trim($q);
                $qq->where(function ($sub) use ($q) {
                    $sub->where('numero_nota', 'like', "%{$q}%")
                        ->orWhere('proveedor', 'like', "%{$q}%")
                        ->orWhere('comentario', 'like', "%{$q}%")
                        ->orWhere('comentario_admin', 'like', "%{$q}%");
                });
            })
            ->orderBy('fecha', 'desc')
            ->orderBy('id', 'desc')
            ->get();
    }

    // GET /despachos/{id}
    public function show(Request $request, $id)
    {
        $this->puedeVer($request);

        return Despacho::with(['detalles.producto', 'user:id,name,username', 'admin:id,name,username'])
            ->findOrFail($id);
    }

    // POST /despachos  (quien recibe)
    public function store(Request $request)
    {
        $this->puedeVer($request);
        $data = $request->validate($this->reglasRecepcion());

        return DB::transaction(function () use ($data, $request) {
            $despacho = Despacho::create([
                'fecha'       => $data['fecha'],
                'numero_nota' => $data['numero_nota'] ?? null,
                'proveedor'   => $data['proveedor'] ?? null,
                'comentario'  => $data['comentario'] ?? null,
                'estado'      => 'PENDIENTE',
                'user_id'     => $request->user()->id,
            ]);

            foreach ($data['detalles'] as $item) {
                $despacho->detalles()->create([
                    'despacho_producto_id' => $item['despacho_producto_id'],
                    'cantidad'             => $item['cantidad'] ?? 0,
                    'peso_recibido'        => $item['peso_recibido'] ?? 0,
                    'comentario'           => $item['comentario'] ?? null,
                ]);
            }

            $this->recalcular($despacho);

            return $despacho->load('detalles.producto');
        });
    }

    // PUT /despachos/{id}  (quien recibe, mientras esté PENDIENTE)
    public function update(Request $request, $id)
    {
        $this->puedeVer($request);
        $despacho = Despacho::findOrFail($id);

        if ($despacho->estado !== 'PENDIENTE') {
            return response()->json(['message' => 'Solo se puede editar un despacho pendiente.'], 400);
        }
        if (!$this->esAdmin($request) && (int) $despacho->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Solo quien registró el despacho puede editarlo.'], 403);
        }

        $data = $request->validate($this->reglasRecepcion());

        return DB::transaction(function () use ($despacho, $data) {
            $despacho->update([
                'fecha'       => $data['fecha'],
                'numero_nota' => $data['numero_nota'] ?? null,
                'proveedor'   => $data['proveedor'] ?? null,
                'comentario'  => $data['comentario'] ?? null,
            ]);

            $despacho->detalles()->delete();
            foreach ($data['detalles'] as $item) {
                $despacho->detalles()->create([
                    'despacho_producto_id' => $item['despacho_producto_id'],
                    'cantidad'             => $item['cantidad'] ?? 0,
                    'peso_recibido'        => $item['peso_recibido'] ?? 0,
                    'comentario'           => $item['comentario'] ?? null,
                ]);
            }

            $this->recalcular($despacho);

            return $despacho->load('detalles.producto');
        });
    }

    // PUT /despachos/{id}/completar  (administrador)
    public function completar(Request $request, $id)
    {
        $this->soloAdmin($request);
        $despacho = Despacho::findOrFail($id);

        if ($despacho->estado === 'ANULADO') {
            return response()->json(['message' => 'El despacho está anulado.'], 400);
        }

        $data = $request->validate([
            'fecha'                           => 'required|date',
            'numero_nota'                     => 'nullable|string|max:50',
            'proveedor'                       => 'nullable|string|max:255',
            'cobrador'                        => 'nullable|string|max:255',
            'comentario'                      => 'nullable|string',
            'comentario_admin'                => 'nullable|string',
            'detalles'                        => 'required|array|min:1',
            'detalles.*.despacho_producto_id' => 'required|integer|exists:despacho_productos,id',
            'detalles.*.cantidad'             => 'nullable|numeric|min:0',
            'detalles.*.peso_recibido'        => 'nullable|numeric|min:0',
            'detalles.*.comentario'           => 'nullable|string',
            'detalles.*.envase'               => 'nullable|string|max:50',
            'detalles.*.peso_envase'          => 'nullable|numeric|min:0',
            'detalles.*.tara'                 => 'nullable|numeric|min:0',
            'detalles.*.peso_bruto'           => 'nullable|numeric|min:0',
            'detalles.*.peso_neto'            => 'nullable|numeric|min:0',
            'detalles.*.precio'               => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($despacho, $data, $request) {
            $despacho->update([
                'fecha'            => $data['fecha'],
                'numero_nota'      => $data['numero_nota'] ?? null,
                'proveedor'        => $data['proveedor'] ?? null,
                'cobrador'         => $data['cobrador'] ?? null,
                'comentario'       => $data['comentario'] ?? null,
                'comentario_admin' => $data['comentario_admin'] ?? null,
                'estado'           => 'COMPLETADO',
                'admin_id'         => $request->user()->id,
            ]);

            $productos = DespachoProducto::withTrashed()
                ->whereIn('id', collect($data['detalles'])->pluck('despacho_producto_id'))
                ->get()
                ->keyBy('id');

            $despacho->detalles()->delete();
            foreach ($data['detalles'] as $item) {
                $cantidad = (float) ($item['cantidad'] ?? 0);
                $pesoNeto = (float) ($item['peso_neto'] ?? 0);
                $precio   = (float) ($item['precio'] ?? 0);

                // productos por kilo se cobran por peso neto, el resto por cantidad
                $producto = $productos[$item['despacho_producto_id']];
                $base     = strtoupper($producto->unidad) === 'UND' ? $cantidad : $pesoNeto;

                $despacho->detalles()->create([
                    'despacho_producto_id' => $item['despacho_producto_id'],
                    'cantidad'             => $cantidad,
                    'peso_recibido'        => $item['peso_recibido'] ?? 0,
                    'comentario'           => $item['comentario'] ?? null,
                    'envase'               => $item['envase'] ?? null,
                    'peso_envase'          => $item['peso_envase'] ?? 0,
                    'tara'                 => $item['tara'] ?? 0,
                    'peso_bruto'           => $item['peso_bruto'] ?? 0,
                    'peso_neto'            => $pesoNeto,
                    'precio'               => $precio,
                    'importe'              => round($base * $precio, 2),
                ]);
            }

            $this->recalcular($despacho);

            return $despacho->load('detalles.producto');
        });
    }

    // PUT /despachos/{id}/anular  (administrador)
    public function anular(Request $request, $id)
    {
        $this->soloAdmin($request);
        $despacho = Despacho::findOrFail($id);

        if ($despacho->estado === 'ANULADO') {
            return response()->json(['message' => 'El despacho ya está anulado.'], 400);
        }

        $despacho->estado = 'ANULADO';
        $despacho->save();

        return response()->json([
            'message'  => 'Despacho anulado.',
            'despacho' => $despacho,
        ]);
    }

    // GET /despachos/{id}/pdf
    public function pdf($id)
    {
        $despacho = Despacho::with(['detalles.producto', 'user:id,name,username', 'admin:id,name,username'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('pdf.despacho', [
            'despacho' => $despacho,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('DESPACHO_' . $despacho->id . '.pdf');
    }

    private function reglasRecepcion(): array
    {
        return [
            'fecha'                           => 'required|date',
            'numero_nota'                     => 'nullable|string|max:50',
            'proveedor'                       => 'nullable|string|max:255',
            'comentario'                      => 'nullable|string',
            'detalles'                        => 'required|array|min:1',
            'detalles.*.despacho_producto_id' => 'required|integer|exists:despacho_productos,id',
            'detalles.*.cantidad'             => 'nullable|numeric|min:0',
            'detalles.*.peso_recibido'        => 'nullable|numeric|min:0',
            'detalles.*.comentario'           => 'nullable|string',
        ];
    }

    private function recalcular(Despacho $despacho): void
    {
        $detalles = $despacho->detalles()->get();

        $despacho->total_peso_recibido = $detalles->sum('peso_recibido');
        $despacho->total_peso_bruto    = $detalles->sum('peso_bruto');
        $despacho->total_peso_neto     = $detalles->sum('peso_neto');
        $despacho->total               = $detalles->sum('importe');
        $despacho->save();
    }

    private function esAdmin(Request $request): bool
    {
        return $request->user()->can('Despachos admin');
    }

    private function puedeVer(Request $request): void
    {
        abort_unless(
            $request->user()->can('Despachos') || $this->esAdmin($request),
            403,
            'No tiene permiso para despachos.'
        );
    }

    private function soloAdmin(Request $request): void
    {
        abort_unless($this->esAdmin($request), 403, 'Solo el administrador puede completar o anular despachos.');
    }
}
