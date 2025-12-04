<?php

namespace App\Http\Controllers;

use App\Models\CierreCaja;
use App\Models\Venta;
use Illuminate\Http\Request;

class CierreCajaController extends Controller
{
    /**
     * Registrar cierre de caja.
     *  - user_id: usuario al que se le cierra caja (vendedor)
     *  - date: fecha del cierre
     *  - monto_efectivo: efectivo contado en caja
     */
    public function store(Request $request)
    {
        $auth = $request->user();

        $data = $request->validate([
            'date'           => ['required', 'date'],
            'user_id'        => ['nullable', 'integer', 'exists:users,id'],
            'monto_efectivo' => ['required', 'numeric', 'min:0'],
            'observacion'    => ['nullable', 'string'],
        ]);

        $date   = $data['date'];
        $userId = $data['user_id'] ?? optional($auth)->id;

        // Base: ventas activas de ese usuario en esa fecha
        $base = Venta::where('status', 'ACTIVO')
            ->whereDate('date', $date)
            ->when($userId, fn ($q) => $q->where('user_id', $userId));

        // === SEPARAR POR MÉTODO DE PAGO ===
        $ingresosEfectivo = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'EFECTIVO')
            ->sum('total');

        $ingresosQr = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'QR')
            ->sum('total');

        $ingresosTarjeta = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'TARJETA')
            ->sum('total');

        $ingresosOnline = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'ONLINE')
            ->sum('total');

        // EGRESOS / CAJA (normal, suelen ser efectivo)
        $totalEgresos  = (clone $base)->where('type', 'EGRESO')->sum('total');
        $totalCajaIni  = (clone $base)->where('type', 'CAJA')->sum('total');
        $tickets       = (clone $base)
            ->where('type', 'INGRESO')
            ->count();

        // Para CUADRE de caja de la vendedora SOLO usamos efectivo:
        // caja_inicial + ingresos_efectivo - egresos
        $totalIngresos = $ingresosEfectivo;
        $montoSistema  = $totalIngresos + $totalCajaIni - $totalEgresos;

        $montoEfectivo = (float) $data['monto_efectivo'];
        $diferencia    = $montoEfectivo - $montoSistema;

        $cierre = CierreCaja::create([
            'user_id'            => $userId,
            'date'               => $date,
            'total_ingresos'     => $totalIngresos,     // SOLO EFECTIVO
            'total_egresos'      => $totalEgresos,
            'total_caja_inicial' => $totalCajaIni,
            'tickets'            => $tickets,
            'monto_efectivo'     => $montoEfectivo,
            'monto_sistema'      => $montoSistema,
            'diferencia'         => $diferencia,
            'observacion'        => $data['observacion'] ?? null,
        ]);

        // Campos "virtuales" para el JSON (no están en la tabla, pero aparecen en la respuesta)
        $cierre->ingresos_efectivo = $ingresosEfectivo;
        $cierre->ingresos_qr       = $ingresosQr;
        $cierre->ingresos_tarjeta  = $ingresosTarjeta;
        $cierre->ingresos_online   = $ingresosOnline;

        return $cierre->load('user');
    }

    // Ver un cierre (para reimprimir manualmente si quieres)
    public function show(CierreCaja $cierreCaja)
    {
        // Opcional: recalcular métodos de pago para ese cierre
        $this->attachPaymentBreakdown($cierreCaja);
        return $cierreCaja->load('user');
    }

    /**
     * Último cierre.
     * Si se envía ?user_id=XX filtra por ese usuario.
     * Si no, por defecto usa el usuario autenticado (modo viejo).
     */
    public function ultimo(Request $request)
    {
        $auth   = $request->user();
        $userId = $request->query('user_id');

        if (!$userId && $auth) {
            $userId = $auth->id;
        }

        $cierre = CierreCaja::with('user')
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->firstOrFail();

        // Adjunta desglose EFECTIVO / QR / otros para la impresión
        $this->attachPaymentBreakdown($cierre);

        return $cierre;
    }

    /**
     * Adjunta al modelo (para JSON) los totales por método de pago
     * usando date + user_id del cierre.
     */
    protected function attachPaymentBreakdown(CierreCaja $cierre)
    {
        if (!$cierre->user_id || !$cierre->date) {
            return;
        }

        $base = Venta::where('status', 'ACTIVO')
            ->whereDate('date', $cierre->date)
            ->where('user_id', $cierre->user_id);

        $cierre->ingresos_efectivo = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'EFECTIVO')
            ->sum('total');

        $cierre->ingresos_qr = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'QR')
            ->sum('total');

        $cierre->ingresos_tarjeta = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'TARJETA')
            ->sum('total');

        $cierre->ingresos_online = (clone $base)
            ->where('type', 'INGRESO')
            ->where('pago', 'ONLINE')
            ->sum('total');
    }
}
