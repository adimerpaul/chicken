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
        $montoSistemaEfectivo = $totalCajaIni + $ingresosEfectivo - $totalEgresos;

        return [
            'ingresos_efectivo'   => $ingresosEfectivo,
            'ingresos_qr'         => $ingresosQr,
            'ingresos_tarjeta'    => $ingresosTarjeta,
            'ingresos_online'     => $ingresosOnline,
            'total_ingresos'      => (float) $totalIngresos,
            'total_egresos'       => (float) $totalEgresos,
            'total_caja_inicial'  => (float) $totalCajaIni,
            'tickets'             => $tickets,
            'monto_sistema'       => (float) $montoSistemaEfectivo,
        ];
    }

    protected function attachComputedFields(CierreCaja $cierre): CierreCaja
    {
        $userId = (int) $cierre->user_id;
        $date   = (string) $cierre->date;

        $stats = $this->buildStatsForDateAndUser($date, $userId);

        $montoSistemaEfectivo = (float) $stats['monto_sistema'];
        $qrSistema            = (float) $stats['ingresos_qr'];

        $efSistema = $montoSistemaEfectivo; // efectivo esperado
        $qrSistema = $qrSistema;

        $efContado = (float) ($cierre->monto_efectivo ?? 0);
        $qrContado = (float) ($cierre->monto_qr ?? 0);

        $esperadoTotal = $efSistema + $qrSistema;
        $contadoTotal  = $efContado + $qrContado;

        $cierre->total_ingresos     = $stats['total_ingresos'];
        $cierre->total_egresos      = $stats['total_egresos'];
        $cierre->total_caja_inicial = $stats['total_caja_inicial'];
        $cierre->tickets            = $stats['tickets'];

        // guardado en tabla:
        $cierre->monto_sistema      = $efSistema;
        $cierre->diferencia         = $contadoTotal - $esperadoTotal;

        // extra para impresión:
        $cierre->ingresos_efectivo  = $stats['ingresos_efectivo'];
        $cierre->ingresos_qr        = $stats['ingresos_qr'];
        $cierre->ingresos_tarjeta   = $stats['ingresos_tarjeta'];
        $cierre->ingresos_online    = $stats['ingresos_online'];

        $cierre->ef_sistema         = $efSistema;
        $cierre->ef_contado         = $efContado;
        $cierre->dif_efectivo       = $efContado - $efSistema;

        $cierre->qr_sistema         = $qrSistema;
        $cierre->qr_contado         = $qrContado;
        $cierre->dif_qr             = $qrContado - $qrSistema;

        $cierre->esperado_total     = $esperadoTotal;
        $cierre->contado_total      = $contadoTotal;

        return $cierre;
    }

    // =========================
    // POST: Cierre por usuario
    // =========================
    public function store(Request $request)
    {
        $user = $request->user();
        $userId = (int) optional($user)->id;

        if (!$userId) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        $data = $request->validate([
            'date'           => ['required', 'date'],
            'monto_efectivo' => ['required', 'numeric'],
            'monto_qr'       => ['required', 'numeric'],
            'observacion'    => ['nullable', 'string'],
        ]);

        $date = $data['date'];

        // ✅ Solo una vez por usuario y fecha
        $existing = CierreCaja::where('user_id', $userId)
            ->where('date', $date)
            ->first();

        if ($existing) {
            $existing = $this->attachComputedFields($existing)->load('user');
            return response()->json([
                'already_exists' => true,
                'message' => 'Ya existe un cierre para este usuario y fecha. Se devolverá para imprimir.',
                'cierre' => $existing
            ], 200);
        }

        // Stats SOLO del usuario
        $stats = $this->buildStatsForDateAndUser($date, $userId);

        $efSistema = (float) $stats['monto_sistema'];
        $qrSistema = (float) $stats['ingresos_qr'];

        $esperadoTotal = $efSistema + $qrSistema;
        $contadoTotal  = (float)$data['monto_efectivo'] + (float)$data['monto_qr'];

        $cierre = CierreCaja::create([
            'user_id'            => $userId,
            'date'               => $date,
            'total_ingresos'     => $stats['total_ingresos'],
            'total_egresos'      => $stats['total_egresos'],
            'total_caja_inicial' => $stats['total_caja_inicial'],
            'tickets'            => $stats['tickets'],

            'monto_efectivo'     => $data['monto_efectivo'],
            'monto_qr'           => $data['monto_qr'],

            'monto_sistema'      => $efSistema,
            'diferencia'         => $contadoTotal - $esperadoTotal,

            'observacion'        => $data['observacion'] ?? null,
        ]);

        $cierre = $this->attachComputedFields($cierre)->load('user');

        return response()->json([
            'already_exists' => false,
            'message' => 'Cierre registrado',
            'cierre' => $cierre
        ], 201);
    }

    // =========================
    // GET: ver un cierre
    // =========================
    public function show(CierreCaja $cierreCaja, Request $request)
    {
        // opcional: seguridad: solo admin o dueño
        $user = $request->user();
        $isAdmin = optional($user)->role === 'Administrador';

        if (!$isAdmin && (int)$cierreCaja->user_id !== (int)optional($user)->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $cierreCaja = $this->attachComputedFields($cierreCaja)->load('user');

        return response()->json([
            'cierre' => $cierreCaja
        ]);
    }

    // =========================
    // GET: último cierre del usuario logueado
    // =========================
    public function ultimoMio(Request $request)
    {
        $user = $request->user();
        $userId = (int) optional($user)->id;

        $cierre = CierreCaja::with('user')
            ->where('user_id', $userId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->first();

        if (!$cierre) {
            return response()->json(['message' => 'No hay cierres de caja registrados'], 404);
        }

        $cierre = $this->attachComputedFields($cierre);

        return response()->json(['cierre' => $cierre]);
    }
}
