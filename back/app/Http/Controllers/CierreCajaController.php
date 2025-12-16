<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use Illuminate\Http\Request;

class CierreCajaController extends Controller
{
    // CierreCajaController.php

    public function reporteUltimo(Request $request)
    {
        // Última fecha con ventas
        $fecha = Venta::orderByDesc('date')->value('date');

        if (!$fecha) {
            return response()->json(['message' => 'No hay ventas'], 404);
        }

        // Ventas ACTIVAS del día (TODOS LOS USUARIOS)
        $ventas = Venta::whereDate('date', $fecha)
            ->where('status', 'ACTIVO')
            ->with('user')
            ->get();

        // Agrupar por usuario
        $usuarios = $ventas->groupBy('user_id')->map(function ($rows) {
            $user = $rows->first()->user;

            $efSistema = $rows->where('type', 'INGRESO')
                ->where('pago', 'EFECTIVO')
                ->sum('total');

            $qrSistema = $rows->where('type', 'INGRESO')
                ->where('pago', 'QR')
                ->sum('total');

            // Buscar cierre del usuario (si existe)
            $cierre = CierreCaja::where('user_id', $user->id)
                ->where('date', $rows->first()->date)
                ->first();

            $efContado = (float) ($cierre->monto_efectivo ?? 0);
            $qrContado = (float) ($cierre->monto_qr ?? 0);

            return [
                'user_name'      => $user->name,
                'ef_sistema'     => (float) $efSistema,
                'ef_contado'     => $efContado,
                'dif_efectivo'  => $efContado - $efSistema,
                'qr_sistema'     => (float) $qrSistema,
                'qr_contado'     => $qrContado,
                'dif_qr'         => $qrContado - $qrSistema,
            ];
        })->values();

        return response()->json([
            'date' => $fecha,
            'resumen' => [
                'efectivo' => $usuarios->sum('ef_sistema'),
                'qr'       => $usuarios->sum('qr_sistema'),
            ],
            'usuarios' => $usuarios
        ]);
    }

    protected function buildStatsForDateAndUser(string $date, int $userId): array
    {
        $ventas = Venta::whereDate('date', $date)
            ->where('status', 'ACTIVO')
            ->where('user_id', $userId)
            ->get();

        $ingresosEfectivo = (float) $ventas->where('type', 'INGRESO')->where('pago', 'EFECTIVO')->sum('total');
        $ingresosQr       = (float) $ventas->where('type', 'INGRESO')->where('pago', 'QR')->sum('total');
        $ingresosTarjeta  = (float) $ventas->where('type', 'INGRESO')->where('pago', 'TARJETA')->sum('total');
        $ingresosOnline   = (float) $ventas->where('type', 'INGRESO')->where('pago', 'ONLINE')->sum('total');

        $totalCajaIni     = (float) $ventas->where('type', 'CAJA')->sum('total');
        $totalEgresos     = (float) $ventas->where('type', 'EGRESO')->sum('total');
        $tickets          = (int)   $ventas->where('type', 'INGRESO')->count();

        $totalIngresos    = $ingresosEfectivo + $ingresosQr + $ingresosTarjeta + $ingresosOnline;

        // ✅ Sistema efectivo = Caja inicial + ingresos efectivo - egresos
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
            'monto_sistema'       => (float) $montoSistema,
        ];
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'           => ['required', 'date'],
            'monto_efectivo' => ['required', 'numeric'],
            'monto_qr'       => ['required', 'numeric'],
            'observacion'    => ['nullable', 'string'],
        ]);

        $date = $data['date'];
//        solo permite una vez
        $existing = CierreCaja::where('user_id', optional($request->user())->id)
            ->where('date', $date)
            ->first();
        if ($existing) {
            return response()->json([
                'message' => 'Ya existe un cierre de caja para este usuario en la fecha indicada'
            ], 400);
        }

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
