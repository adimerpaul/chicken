<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use Illuminate\Http\Request;

class CierreCajaController extends Controller
{
    // Registrar cierre de caja del usuario autenticado
    public function store(Request $request)
    {
        $user = $request->user(); // auth()->user()
        $date = $request->input('date', now()->toDateString());

        $request->validate([
            'monto_efectivo' => 'required|numeric|min:0',
            'observacion'    => 'nullable|string',
        ]);

        // Base: ventas del usuario en la fecha
        $base = Venta::where('user_id', $user->id)
            ->whereDate('date', $date)
            ->where('status', 'ACTIVO');

        $totalIngresos = (clone $base)->where('type', 'INGRESO')->sum('total');
        $totalEgresos  = (clone $base)->where('type', 'EGRESO')->sum('total');
        $totalCajaIni  = (clone $base)->where('type', 'CAJA')->sum('total');
        $tickets       = (clone $base)->where('type', 'INGRESO')->count();

        $montoSistema = $totalIngresos + $totalCajaIni - $totalEgresos;
        $montoEfectivo = (float) $request->input('monto_efectivo', 0);
        $diferencia = $montoEfectivo - $montoSistema;

        $cierre = CierreCaja::create([
            'user_id'            => $user->id,
            'date'               => $date,
            'total_ingresos'     => $totalIngresos,
            'total_egresos'      => $totalEgresos,
            'total_caja_inicial' => $totalCajaIni,
            'tickets'            => $tickets,
            'monto_efectivo'     => $montoEfectivo,
            'monto_sistema'      => $montoSistema,
            'diferencia'         => $diferencia,
            'observacion'        => $request->input('observacion'),
        ]);

        return $cierre->load('user');
    }

    // Ver un cierre (para reimprimir)
    public function show(CierreCaja $cierreCaja)
    {
        return $cierreCaja->load('user');
    }

    // Último cierre del usuario autenticado
    public function ultimo(Request $request)
    {
        $user = $request->user();
        $cierre = CierreCaja::with('user')
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->firstOrFail();

        return $cierre;
    }
}
