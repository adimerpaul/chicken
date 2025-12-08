<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use Illuminate\Http\Request;

class CierreCajaController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'date'           => ['required', 'date'],
            'monto_efectivo' => ['required', 'numeric'],
            'monto_qr'       => ['required', 'numeric'],
            'observacion'    => ['nullable', 'string'],
        ]);

        $date = $data['date'];

        $stats = $this->buildStatsForDate($date);

        $montoSistemaEfectivo = $stats['monto_sistema'];
        $ingresosQr           = $stats['ingresos_qr'];

        $esperadoTotal = $montoSistemaEfectivo + $ingresosQr;
        $contadoTotal  = $data['monto_efectivo'] + $data['monto_qr'];
        $diferenciaTotal = $contadoTotal - $esperadoTotal;

        $cierre = CierreCaja::create([
            'user_id'            => optional($request->user())->id,
            'date'               => $date,
            'total_ingresos'     => $stats['total_ingresos'],
            'total_egresos'      => $stats['total_egresos'],
            'total_caja_inicial' => $stats['total_caja_inicial'],
            'tickets'            => $stats['tickets'],
            'monto_efectivo'     => $data['monto_efectivo'],
            'monto_qr'           => $data['monto_qr'],
            'monto_sistema'      => $montoSistemaEfectivo,
            'diferencia'         => $diferenciaTotal,
            'observacion'        => $data['observacion'] ?? null,
        ]);

        $cierre->ingresos_efectivo = $stats['ingresos_efectivo'];
        $cierre->ingresos_qr       = $stats['ingresos_qr'];
        $cierre->ingresos_tarjeta  = $stats['ingresos_tarjeta'];
        $cierre->ingresos_online   = $stats['ingresos_online'];
        $cierre->esperado_total    = $esperadoTotal;
        $cierre->contado_total     = $contadoTotal;

        return response()->json($cierre->load('user'));
    }

    public function show(CierreCaja $cierreCaja)
    {
        $stats = $this->buildStatsForDate($cierreCaja->date);

        $montoSistemaEfectivo = $stats['monto_sistema'];
        $ingresosQr           = $stats['ingresos_qr'];

        $esperadoTotal = $montoSistemaEfectivo + $ingresosQr;
        $contadoTotal  = ($cierreCaja->monto_efectivo ?? 0) + ($cierreCaja->monto_qr ?? 0);
        $diferencia    = $contadoTotal - $esperadoTotal;

        $cierreCaja->total_ingresos     = $stats['total_ingresos'];
        $cierreCaja->total_egresos      = $stats['total_egresos'];
        $cierreCaja->total_caja_inicial = $stats['total_caja_inicial'];
        $cierreCaja->tickets            = $stats['tickets'];
        $cierreCaja->monto_sistema      = $montoSistemaEfectivo;
        $cierreCaja->diferencia         = $diferencia;

        $cierreCaja->ingresos_efectivo  = $stats['ingresos_efectivo'];
        $cierreCaja->ingresos_qr        = $stats['ingresos_qr'];
        $cierreCaja->ingresos_tarjeta   = $stats['ingresos_tarjeta'];
        $cierreCaja->ingresos_online    = $stats['ingresos_online'];
        $cierreCaja->esperado_total     = $esperadoTotal;
        $cierreCaja->contado_total      = $contadoTotal;

        return response()->json($cierreCaja->load('user'));
    }

    public function ultimo(Request $request)
    {
        $cierre = CierreCaja::with('user')
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        if (!$cierre) {
            return response()->json(['message' => 'No hay cierres de caja registrados'], 404);
        }

        return $this->show($cierre);
    }

    protected function buildStatsForDate(string $date): array
    {
        $ventas = Venta::whereDate('date', $date)
            ->where('status', 'ACTIVO')
            ->get();

        $ingresosEfectivo = $ventas->where('type', 'INGRESO')->where('pago', 'EFECTIVO')->sum('total');
        $ingresosQr       = $ventas->where('type', 'INGRESO')->where('pago', 'QR')->sum('total');
        $ingresosTarjeta  = $ventas->where('type', 'INGRESO')->where('pago', 'TARJETA')->sum('total');
        $ingresosOnline   = $ventas->where('type', 'INGRESO')->where('pago', 'ONLINE')->sum('total');

        $totalCajaIni     = $ventas->where('type', 'CAJA')->sum('total');
        $totalEgresos     = $ventas->where('type', 'EGRESO')->sum('total');
        $tickets          = $ventas->where('type', 'INGRESO')->count();

        $totalIngresos    = $ingresosEfectivo + $ingresosQr + $ingresosTarjeta + $ingresosOnline;

        $montoSistema = $totalCajaIni + $ingresosEfectivo - $totalEgresos;

        return [
            'ingresos_efectivo'   => $ingresosEfectivo,
            'ingresos_qr'         => $ingresosQr,
            'ingresos_tarjeta'    => $ingresosTarjeta,
            'ingresos_online'     => $ingresosOnline,
            'total_ingresos'      => $totalIngresos,
            'total_egresos'       => $totalEgresos,
            'total_caja_inicial'  => $totalCajaIni,
            'tickets'             => $tickets,
            'monto_sistema'       => $montoSistema,
        ];
    }
}
