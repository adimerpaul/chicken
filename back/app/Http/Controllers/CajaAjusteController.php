<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Excel (opcional)
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class CajaAjusteController extends Controller
{
    private function weekRange(?string $anyDate = null): array
    {
        $d = $anyDate ? Carbon::parse($anyDate) : now();
        $start = $d->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $end   = $d->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        return [$start, $end];
    }

    public function index(Request $req)
    {
        $dateFrom = $req->input('date_from');
        $dateTo   = $req->input('date_to');

        // NUEVO: filtros
        $pago   = $req->input('pago');     // 'QR' o 'EFECTIVO'
        $userId = $req->input('user_id');  // id de user

        if ($req->boolean('week') || (!$dateFrom && !$dateTo)) {
            [$dateFrom, $dateTo] = $this->weekRange($req->input('anchor_date'));
        } else {
            $dateFrom = $dateFrom ?: now()->toDateString();
            $dateTo   = $dateTo ?: now()->toDateString();
        }

        // Lista completa de días (para mostrar 0 aunque no haya registros)
        $days = [];
        $cursor = Carbon::parse($dateFrom);
        $end = Carbon::parse($dateTo);
        while ($cursor->lte($end)) {
            $days[] = [
                'fecha' => $cursor->toDateString(),
                'dia'   => (int)$cursor->format('j'),
                'label' => $cursor->format('d/m/y'),
                'ymd'   => $cursor->toDateString(),
            ];
            $cursor->addDay();
        }

        // Lista de usuarios (para el select y panel)
        $usersAll = DB::table('users')
            ->select('id', 'name')
            ->orderBy('name', 'asc')
            ->get()
            ->map(fn($u) => ['id' => (int)$u->id, 'name' => $u->name])
            ->values();

        // Helper para aplicar filtros a un query de ventas
        $applyFilters = function ($q) use ($dateFrom, $dateTo, $pago, $userId) {
            $q->whereBetween(DB::raw('DATE(v.date)'), [$dateFrom, $dateTo]);

            if ($pago) {
                $q->where('v.pago', $pago);
            }
            if ($userId) {
                $q->where('v.user_id', $userId);
            }

            return $q;
        };

        // ============
        // 1) INGRESOS: total por día
        // ============
        $ingTotalByDay = $applyFilters(
            DB::table('ventas as v')
                ->selectRaw("DATE(v.date) as fecha")
                ->selectRaw("SUM(CASE WHEN v.status='ACTIVO' THEN v.total ELSE 0 END) as total")
                ->where('v.type', 'INGRESO')
                ->groupBy(DB::raw('DATE(v.date)'))
        )->get()->keyBy('fecha');

        // ============
        // 2) EGRESOS: total por día
        // ============
        $egrTotalByDay = $applyFilters(
            DB::table('ventas as v')
                ->selectRaw("DATE(v.date) as fecha")
                ->selectRaw("SUM(CASE WHEN v.status='ACTIVO' THEN v.total ELSE 0 END) as total")
                ->where('v.type', 'EGRESO')
                ->groupBy(DB::raw('DATE(v.date)'))
        )->get()->keyBy('fecha');

        // ============
        // 3) DETALLE INGRESOS POR DÍA Y USUARIO
        // ============
        $ingDetalleQuery = DB::table('ventas as v')
            ->join('users as u', 'u.id', '=', 'v.user_id')
            ->selectRaw("DATE(v.date) as fecha")
            ->addSelect('u.id as user_id', 'u.name as user_name')
            ->selectRaw("SUM(CASE WHEN v.status='ACTIVO' THEN v.total ELSE 0 END) as total")
            ->where('v.type', 'INGRESO')
            ->groupBy(DB::raw('DATE(v.date)'), 'u.id', 'u.name')
            ->orderBy(DB::raw('DATE(v.date)'), 'asc');

        $applyFilters($ingDetalleQuery);
        $ingDetalle = $ingDetalleQuery->get();

        // index: fecha -> user_id -> total
        $ingDetalleByDay = [];
        foreach ($ingDetalle as $r) {
            $f = $r->fecha;
            if (!isset($ingDetalleByDay[$f])) $ingDetalleByDay[$f] = [];
            $ingDetalleByDay[$f][(string)$r->user_id] = (float)$r->total;
        }

        // ============
        // 4) DETALLE EGRESOS POR DÍA (lista)
        // ============
        $egrDetalleQuery = DB::table('ventas as v')
            ->selectRaw("DATE(v.date) as fecha")
            ->addSelect('v.name as detalle', 'v.total', 'v.id')
            ->where('v.type', 'EGRESO')
            ->where('v.status', 'ACTIVO')
            ->orderBy(DB::raw('DATE(v.date)'), 'asc')
            ->orderBy('v.id', 'asc');

        $applyFilters($egrDetalleQuery);
        $egrDetalle = $egrDetalleQuery->get();

        $egrDetalleByDay = [];
        foreach ($egrDetalle as $r) {
            $f = $r->fecha;
            if (!isset($egrDetalleByDay[$f])) $egrDetalleByDay[$f] = [];
            $egrDetalleByDay[$f][] = [
                'id' => (int)$r->id,
                'detalle' => $r->detalle ?? '',
                'total' => (float)$r->total,
            ];
        }

        // ============
        // 5) Construcción final rows
        // ============
        $rows = collect($days)->map(function ($d) use ($ingTotalByDay, $egrTotalByDay) {
            $f = $d['fecha'];
            $ing = (float) ($ingTotalByDay[$f]->total ?? 0);
            $egr = (float) ($egrTotalByDay[$f]->total ?? 0);
            return [
                'fecha' => $f,
                'label' => $d['label'],
                'dia' => $d['dia'],
                'ingresos_total' => $ing,
                'egresos_total' => $egr,
                'en_caja' => $ing - $egr,
            ];
        })->values();

        $totIngresos = (float)$rows->sum('ingresos_total');
        $totEgresos  = (float)$rows->sum('egresos_total');
        $totCaja     = $totIngresos - $totEgresos;

        return response()->json([
            'title' => 'AJUSTE EN CAJA GARDEN ORURO',
            'date_from' => $dateFrom,
            'date_to' => $dateTo,

            // NUEVO: devolver filtros
            'pago' => $pago,
            'user_id' => $userId,

            // usuarios para select / panel
            'users' => $usersAll,

            'rows' => $rows,

            'detalle' => [
                'ingresos_by_day' => $ingDetalleByDay,
                'egresos_by_day' => $egrDetalleByDay,
            ],

            'totales' => [
                'ingresos' => $totIngresos,
                'egresos' => $totEgresos,
                'en_caja' => $totCaja,
            ],
        ]);
    }

    // EXCEL (opcional)
    public function excel(Request $req)
    {
        $data = $this->index($req)->getData(true);

        $spreadsheet = new Spreadsheet();

        // Hoja 1: Resumen diario
        $s1 = $spreadsheet->getActiveSheet();
        $s1->setTitle('Resumen');

        $s1->setCellValue('A1', $data['title']);
        $s1->setCellValue('A2', 'Desde: '.$data['date_from']);
        $s1->setCellValue('C2', 'Hasta: '.$data['date_to']);
        $s1->setCellValue('A3', 'Pago: '.($data['pago'] ?: 'Todos'));
        $s1->setCellValue('C3', 'Usuario: '.($data['user_id'] ?: 'Todos'));

        $s1->fromArray(['Fecha', 'Día', 'Ingresos', 'Egresos', 'En Caja'], null, 'A5');
        $r = 6;
        foreach ($data['rows'] as $row) {
            $s1->fromArray([
                $row['label'],
                $row['dia'],
                $row['ingresos_total'],
                $row['egresos_total'],
                $row['en_caja'],
            ], null, "A{$r}");
            $r++;
        }

        $r += 1;
        $s1->setCellValue("A{$r}", 'TOTALES');
        $s1->setCellValue("B{$r}", 'INGRESOS'); $s1->setCellValue("C{$r}", $data['totales']['ingresos']);
        $s1->setCellValue("B".($r+1), 'EGRESOS'); $s1->setCellValue("C".($r+1), $data['totales']['egresos']);
        $s1->setCellValue("B".($r+2), 'EN CAJA'); $s1->setCellValue("C".($r+2), $data['totales']['en_caja']);

        // Hoja 2: Ingresos detalle (por día y usuario)
        $s2 = $spreadsheet->createSheet();
        $s2->setTitle('Ingresos Detalle');

        $header = ['Fecha'];
        foreach ($data['users'] as $u) $header[] = $u['name'];
        $header[] = 'TOTAL';
        $s2->fromArray($header, null, 'A1');

        $r = 2;
        foreach ($data['rows'] as $row) {
            $f = $row['fecha'];
            $ingMap = $data['detalle']['ingresos_by_day'][$f] ?? [];

            $line = [$row['label']];
            $sum = 0;

            foreach ($data['users'] as $u) {
                $v = (float)($ingMap[(string)$u['id']] ?? 0);
                $sum += $v;
                $line[] = $v;
            }
            $line[] = $sum;

            $s2->fromArray($line, null, "A{$r}");
            $r++;
        }

        // Hoja 3: Egresos detalle
        $s3 = $spreadsheet->createSheet();
        $s3->setTitle('Egresos Detalle');
        $s3->fromArray(['Fecha', 'Detalle', 'Total'], null, 'A1');

        $r = 2;
        foreach ($data['rows'] as $row) {
            $f = $row['fecha'];
            $list = $data['detalle']['egresos_by_day'][$f] ?? [];
            foreach ($list as $e) {
                $s3->fromArray([$row['label'], $e['detalle'], $e['total']], null, "A{$r}");
                $r++;
            }
        }

        $fileName = "ajuste_caja_{$data['date_from']}_{$data['date_to']}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
