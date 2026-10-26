<?php

namespace App\Http\Controllers;

use App\Models\InventarioCajaMovimiento;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Inventario de refrescos por caja (vendedor).
 * 1) Al abrir su caja el vendedor cuenta los refrescos que tiene -> INICIAL.
 * 2) Si le reponen durante el turno lo registra -> AUMENTO.
 * 3) Las ventas descuentan solas; al cierre se imprime inicial + aumentos - vendido.
 */
class InventarioCajaController extends Controller
{
    // GET /inventario-caja?date=&user_id=
    public function index(Request $request)
    {
        [$userId, $date] = $this->contexto($request);

        return InventarioCajaMovimiento::resumen($userId, $date);
    }

    // POST /inventario-caja/inicial
    // body: { date, user_id?, items: [{ producto_id, cantidad }] }
    public function inicial(Request $request)
    {
        $data = $request->validate([
            'items'               => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|exists:productos,id',
            'items.*.cantidad'    => 'nullable|numeric|min:0',
        ]);
        [$userId, $date] = $this->contexto($request, true);

        DB::transaction(function () use ($data, $userId, $date) {
            foreach ($data['items'] as $item) {
                InventarioCajaMovimiento::updateOrCreate(
                    [
                        'user_id'     => $userId,
                        'date'        => $date,
                        'producto_id' => $item['producto_id'],
                        'tipo'        => 'INICIAL',
                    ],
                    ['cantidad' => $item['cantidad'] ?? 0]
                );
            }
        });

        return InventarioCajaMovimiento::resumen($userId, $date);
    }

    // POST /inventario-caja/aumento
    // body: { date, user_id?, producto_id, cantidad, comment? }
    public function aumento(Request $request)
    {
        $data = $request->validate([
            'producto_id' => 'required|integer|exists:productos,id',
            'cantidad'    => 'required|numeric|not_in:0',
            'comment'     => 'nullable|string|max:255',
        ]);
        [$userId, $date] = $this->contexto($request, true);

        InventarioCajaMovimiento::create([
            'user_id'     => $userId,
            'date'        => $date,
            'producto_id' => $data['producto_id'],
            'tipo'        => 'AUMENTO',
            'cantidad'    => $data['cantidad'],
            'comment'     => $data['comment'] ?? null,
        ]);

        return InventarioCajaMovimiento::resumen($userId, $date);
    }

    // DELETE /inventario-caja/{id}  (solo aumentos)
    public function destroy(Request $request, $id)
    {
        $mov = InventarioCajaMovimiento::where('tipo', 'AUMENTO')->findOrFail($id);

        if (!$this->esAdmin($request)) {
            abort_unless((int) $mov->user_id === (int) $request->user()->id, 403, 'No autorizado.');
            abort_unless($mov->date === Carbon::now()->toDateString(), 400, 'Solo se puede corregir el inventario del día actual.');
        }

        $mov->delete();

        return InventarioCajaMovimiento::resumen((int) $mov->user_id, $mov->date);
    }

    /**
     * Usuario y fecha sobre los que se trabaja.
     * El vendedor solo ve/edita lo suyo; para editar, solo el día actual.
     * El administrador puede elegir usuario y fecha.
     */
    private function contexto(Request $request, bool $escritura = false): array
    {
        $hoy  = Carbon::now()->toDateString();
        $date = $request->input('date') ?: $hoy;

        if ($this->esAdmin($request)) {
            return [(int) ($request->input('user_id') ?: $request->user()->id), $date];
        }

        abort_if($escritura && $date !== $hoy, 400, 'Solo se puede registrar el inventario del día actual.');

        return [(int) $request->user()->id, $date];
    }

    private function esAdmin(Request $request): bool
    {
        return $request->user()->role === 'Administrador';
    }
}
