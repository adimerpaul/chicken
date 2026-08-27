<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\InventarioInicial;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControlConsumoController extends Controller
{
    /**
     * Resumen semanal de consumo de los insumos marcados con "control_consumo".
     *
     * POST /insumos/control-consumo
     * body: { fechaInicio, fechaFin }  (por defecto la semana actual, lunes a domingo)
     */
    public function semana(Request $request)
    {
        return response()->json($this->construir($request));
    }

    /**
     * Inventario inicial de una fecha: lista los insumos controlados con la
     * cantidad registrada ese día (0 si no hay registro).
     *
     * GET /inventario-inicial?fecha=YYYY-MM-DD
     */
    public function inventarioIndex(Request $request)
    {
        $fecha = $request->input('fecha')
            ? Carbon::parse($request->input('fecha'))->toDateString()
            : Carbon::now()->startOfWeek()->toDateString();

        $registros = InventarioInicial::where('fecha', $fecha)->get()->keyBy('insumo_id');

        $insumos = Insumo::where('control_consumo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad', 'stock']);

        $items = $insumos->map(function ($i) use ($registros) {
            $reg = $registros->get($i->id);
            return [
                'insumo_id' => $i->id,
                'nombre'    => $i->nombre,
                'unidad'    => $i->unidad,
                'stock'     => (float)$i->stock,
                'cantidad'  => $reg ? (float)$reg->cantidad : 0,
                'registrado' => (bool)$reg,
            ];
        });

        return response()->json([
            'fecha' => $fecha,
            'items' => $items,
        ]);
    }

    /**
     * Guarda (o actualiza) el inventario inicial de una fecha.
     * Si el insumo ya tenía inicial ese día, se actualiza.
     *
     * POST /inventario-inicial
     * body: { fecha, items: [{ insumo_id, cantidad }] }
     */
    public function inventarioStore(Request $request)
    {
        $data = $request->validate([
            'fecha'             => ['required', 'date'],
            'items'             => ['required', 'array'],
            'items.*.insumo_id' => ['required', 'integer'],
            'items.*.cantidad'  => ['nullable', 'numeric'],
        ]);

        $fecha  = Carbon::parse($data['fecha'])->toDateString();
        $userId = $request->user()->id ?? null;

        DB::transaction(function () use ($data, $fecha, $userId) {
            foreach ($data['items'] as $item) {
                InventarioInicial::updateOrCreate(
                    ['insumo_id' => $item['insumo_id'], 'fecha' => $fecha],
                    ['cantidad' => (float)($item['cantidad'] ?? 0), 'user_id' => $userId]
                );
            }
        });

        return response()->json([
            'message' => 'Inventario inicial guardado',
            'fecha'   => $fecha,
        ]);
    }

    /**
     * Exporta el control de consumo a Excel.
     * POST /insumos/control-consumo/excel
     */
    public function excel(Request $request)
    {
        $d = $this->construir($request);

        $spreadsheet = new Spreadsheet();
        $hoja = $spreadsheet->getActiveSheet();
        $hoja->setTitle('Control de consumo');

        $dias    = $d['dias'];
        $nDias   = count($dias);
        // A insumo | B unidad | C inicial | D..(D+n-1) compras | .. ventas | resumen
        $colTot  = 3 + ($nDias * 2) + 3; // total de columnas
        $ultima  = $this->col($colTot);

        // ---- Título
        $hoja->setCellValue('A1', "CHICKEN'S GARDEN — CONTROL DE CONSUMO DE INSUMOS");
        $hoja->mergeCells("A1:{$ultima}1");
        $hoja->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setRGB('FFFFFF');
        $hoja->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('A33314');
        $hoja->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $hoja->getRowDimension(1)->setRowHeight(24);

        $hoja->setCellValue('A2', 'Periodo: ' . $d['desde'] . ' al ' . $d['hasta']);
        $hoja->mergeCells("A2:{$ultima}2");
        $hoja->getStyle('A2')->getFont()->setItalic(true)->getColor()->setRGB('555555');

        // ---- Cabecera de grupos (fila 3)
        $hoja->setCellValue('A3', 'Insumo');
        $hoja->mergeCells('A3:A4');
        $hoja->setCellValue('B3', 'Unidad');
        $hoja->mergeCells('B3:B4');
        $hoja->setCellValue('C3', 'Inicial');
        $hoja->mergeCells('C3:C4');

        $iniCompras = 4;
        $finCompras = 3 + $nDias;
        $iniVentas  = $finCompras + 1;
        $finVentas  = $finCompras + $nDias;

        $hoja->setCellValue($this->col($iniCompras) . '3', 'COMPRAS (entradas)');
        $hoja->mergeCells($this->col($iniCompras) . '3:' . $this->col($finCompras) . '3');
        $hoja->setCellValue($this->col($iniVentas) . '3', 'VENTAS (consumo)');
        $hoja->mergeCells($this->col($iniVentas) . '3:' . $this->col($finVentas) . '3');

        $resumen = ['Total compras', 'Total ventas', 'Esperado'];
        foreach ($resumen as $k => $titulo) {
            $c = $this->col($finVentas + 1 + $k);
            $hoja->setCellValue($c . '3', $titulo);
            $hoja->mergeCells($c . '3:' . $c . '4');
        }

        // ---- Cabecera de días (fila 4)
        foreach ($dias as $k => $dia) {
            $hoja->setCellValue($this->col($iniCompras + $k) . '4', $dia['label']);
            $hoja->setCellValue($this->col($iniVentas + $k) . '4', $dia['label']);
        }

        // Estilos de cabecera
        $hoja->getStyle("A3:{$ultima}4")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $hoja->getStyle("A3:{$ultima}4")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $hoja->getStyle("A3:{$this->col(3)}4")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('37474F');
        $hoja->getStyle($this->col($iniCompras) . '3:' . $this->col($finCompras) . '4')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('0D9488'); // compras: verde azulado
        $hoja->getStyle($this->col($iniVentas) . '3:' . $this->col($finVentas) . '4')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2542F'); // ventas: naranja
        $hoja->getStyle($this->col($finVentas + 1) . '3:' . $ultima . '4')->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('37474F');

        // ---- Filas
        $fila = 5;
        foreach ($d['insumos'] as $i) {
            $hoja->setCellValue('A' . $fila, $i['nombre']);
            $hoja->setCellValue('B' . $fila, $i['unidad']);
            $hoja->setCellValue('C' . $fila, $i['inicial']);

            foreach ($dias as $k => $dia) {
                $hoja->setCellValue($this->col($iniCompras + $k) . $fila, $i['compras_dia'][$dia['fecha']]);
                $hoja->setCellValue($this->col($iniVentas + $k) . $fila, $i['por_dia'][$dia['fecha']]);
            }

            $vals = [$i['compras'], $i['cantidad'], $i['esperado']];
            foreach ($vals as $k => $v) {
                $hoja->setCellValue($this->col($finVentas + 1 + $k) . $fila, $v);
            }

            // color suave por grupo
            $hoja->getStyle($this->col($iniCompras) . $fila . ':' . $this->col($finCompras) . $fila)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E6F4F1');
            $hoja->getStyle($this->col($iniVentas) . $fila . ':' . $this->col($finVentas) . $fila)
                ->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FCE9E3');

            // esperado negativo = se vendió más de lo que había registrado
            $colEsp = $this->col($finVentas + 3);
            $hoja->getStyle($colEsp . $fila)->getFont()->setBold(true);
            if ($i['esperado'] < 0) {
                $hoja->getStyle($colEsp . $fila)->getFont()->getColor()->setRGB('C62828');
            }

            $fila++;
        }

        // ---- Totales
        if (count($d['insumos'])) {
            $hoja->setCellValue('A' . $fila, 'TOTALES');
            $hoja->mergeCells('A' . $fila . ':B' . $fila);
            for ($c = 3; $c <= $colTot; $c++) {
                $letra = $this->col($c);
                $hoja->setCellValue($letra . $fila, "=SUM({$letra}5:{$letra}" . ($fila - 1) . ')');
            }
            $hoja->getStyle('A' . $fila . ':' . $ultima . $fila)->getFont()->setBold(true);
            $hoja->getStyle('A' . $fila . ':' . $ultima . $fila)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('ECEFF1');
        }

        // ---- Formato general
        $ultimaFila = max($fila, 5);
        $hoja->getStyle("A3:{$ultima}{$ultimaFila}")->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('BDBDBD');
        $hoja->getStyle("C5:{$ultima}{$ultimaFila}")->getNumberFormat()->setFormatCode('#,##0.00');
        $hoja->getColumnDimension('A')->setWidth(28);
        $hoja->getColumnDimension('B')->setWidth(10);
        for ($c = 3; $c <= $colTot; $c++) {
            $hoja->getColumnDimension($this->col($c))->setWidth(13);
        }
        $hoja->freezePane('C5');
        $hoja->setAutoFilter("A4:{$ultima}" . ($ultimaFila - 1));

        // ---- Hoja 2: ventas por día
        $hoja2 = $spreadsheet->createSheet();
        $hoja2->setTitle('Ventas por día');
        $hoja2->fromArray(['Fecha', 'Día', 'Ventas', 'Total Bs'], null, 'A1');
        $hoja2->getStyle('A1:D1')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $hoja2->getStyle('A1:D1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('37474F');
        $f = 2;
        foreach ($d['dias'] as $dia) {
            $hoja2->fromArray([$dia['fecha'], $dia['label'], $dia['ventas'], $dia['total']], null, 'A' . $f);
            $f++;
        }
        $hoja2->setCellValue('A' . $f, 'TOTAL');
        $hoja2->setCellValue('C' . $f, "=SUM(C2:C" . ($f - 1) . ')');
        $hoja2->setCellValue('D' . $f, "=SUM(D2:D" . ($f - 1) . ')');
        $hoja2->getStyle('A' . $f . ':D' . $f)->getFont()->setBold(true);
        $hoja2->getStyle('D2:D' . $f)->getNumberFormat()->setFormatCode('#,##0.00');
        foreach (['A' => 14, 'B' => 14, 'C' => 12, 'D' => 14] as $col => $w) {
            $hoja2->getColumnDimension($col)->setWidth($w);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $nombre = "control_consumo_{$d['desde']}_{$d['hasta']}.xlsx";

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // ------------------------------------------------------------------
    // Núcleo del reporte
    // ------------------------------------------------------------------
    private function construir(Request $request): array
    {
        [$ini, $fin] = $this->rango($request);

        $dias = $this->dias($ini, $fin);

        // Ventas por día (solo ventas activas de ingreso)
        $ventasPorDia = DB::table('ventas')
            ->selectRaw('date, COUNT(*) as ventas, COALESCE(SUM(total), 0) as total')
            ->whereNull('deleted_at')
            ->where('status', 'ACTIVO')
            ->where('type', 'INGRESO')
            ->whereBetween('date', [$ini->toDateString(), $fin->toDateString()])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        $resumenDias = [];
        foreach ($dias as $d) {
            $row = $ventasPorDia->get($d['fecha']);
            $resumenDias[] = [
                'fecha'  => $d['fecha'],
                'label'  => $d['label'],
                'ventas' => $row ? (int)$row->ventas : 0,
                'total'  => $row ? (float)$row->total : 0,
            ];
        }

        // Consumo teórico por insumo y día: cantidad de la receta x cantidad vendida
        $consumos = DB::table('venta_detalles as vd')
            ->join('ventas as v', 'v.id', '=', 'vd.venta_id')
            ->join('insumo_productos as ip', 'ip.producto_id', '=', 'vd.product_id')
            ->join('insumos as i', 'i.id', '=', 'ip.insumo_id')
            ->whereNull('v.deleted_at')
            ->whereNull('ip.deleted_at')
            ->whereNull('i.deleted_at')
            ->where('v.status', 'ACTIVO')
            ->where('v.type', 'INGRESO')
            ->where('i.control_consumo', 1)
            ->whereBetween('v.date', [$ini->toDateString(), $fin->toDateString()])
            // mismo criterio que el descuento de stock: MESA / LLEVAR o insumo sin restricción
            ->where(function ($w) {
                $w->where(function ($w1) {
                    $w1->where('v.mesa', 'MESA')
                        ->where(function ($w2) {
                            $w2->where('i.es_mesa', 1)
                                ->orWhere(function ($w3) {
                                    $w3->where('i.es_mesa', 0)->where('i.es_llevar', 0);
                                });
                        });
                })->orWhere(function ($w1) {
                    $w1->where('v.mesa', '!=', 'MESA')
                        ->where(function ($w2) {
                            $w2->where('i.es_llevar', 1)
                                ->orWhere(function ($w3) {
                                    $w3->where('i.es_mesa', 0)->where('i.es_llevar', 0);
                                });
                        });
                });
            })
            ->selectRaw('i.id as insumo_id, v.date as fecha,
                         SUM(ip.cantidad * vd.qty) as cantidad,
                         SUM(vd.subtotal) as importe,
                         COUNT(DISTINCT v.id) as ventas')
            ->groupBy('i.id', 'v.date')
            ->get();

        // Compras (entradas) por insumo y día
        $compras = DB::table('compra_detalles as cd')
            ->join('compras as c', 'c.id', '=', 'cd.compra_id')
            ->join('insumos as i', 'i.id', '=', 'cd.insumo_id')
            ->whereNull('cd.deleted_at')
            ->whereNull('c.deleted_at')
            ->whereNull('i.deleted_at')
            ->where('c.estado', '!=', 'ANULADO')
            ->where('i.control_consumo', 1)
            ->whereBetween('c.fecha', [$ini->toDateString(), $fin->toDateString()])
            ->selectRaw('i.id as insumo_id, c.fecha as fecha,
                         SUM(cd.cantidad) as cantidad,
                         SUM(cd.subtotal) as importe,
                         COUNT(DISTINCT c.id) as compras')
            ->groupBy('i.id', 'c.fecha')
            ->get();

        // Inventario inicial: el registro del rango (el más antiguo) o el último anterior
        $inicialesRango = InventarioInicial::whereBetween('fecha', [$ini->toDateString(), $fin->toDateString()])
            ->orderBy('fecha')
            ->get()
            ->groupBy('insumo_id');

        $inicialesPrevias = InventarioInicial::where('fecha', '<', $ini->toDateString())
            ->orderByDesc('fecha')
            ->get()
            ->groupBy('insumo_id');

        $insumos = Insumo::where('control_consumo', 1)
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'unidad', 'stock', 'costo', 'min_stock', 'tipo_insumo']);

        $data = [];
        foreach ($insumos as $insumo) {
            $filasV = $consumos->where('insumo_id', $insumo->id);
            $filasC = $compras->where('insumo_id', $insumo->id);

            $porDia = [];
            $comprasDia = [];
            foreach ($dias as $d) {
                $fv = $filasV->firstWhere('fecha', $d['fecha']);
                $fc = $filasC->first(function ($r) use ($d) {
                    return substr((string)$r->fecha, 0, 10) === $d['fecha'];
                });
                $porDia[$d['fecha']]     = $fv ? round((float)$fv->cantidad, 2) : 0;
                $comprasDia[$d['fecha']] = $fc ? round((float)$fc->cantidad, 2) : 0;
            }

            $reg = ($inicialesRango[$insumo->id] ?? collect())->first()
                ?? ($inicialesPrevias[$insumo->id] ?? collect())->first();

            $inicial = $reg ? (float)$reg->cantidad : 0;
            $totalVentas  = round(array_sum($porDia), 2);
            $totalCompras = round(array_sum($comprasDia), 2);
            $esperado     = round($inicial + $totalCompras - $totalVentas, 2);

            // Nota: el control NO usa el stock del sistema (no es preciso).
            // Solo inventario inicial declarado + compras − ventas.
            $data[] = [
                'id'             => $insumo->id,
                'nombre'         => $insumo->nombre,
                'unidad'         => $insumo->unidad,
                'costo'          => (float)$insumo->costo,
                'tipo_insumo'    => $insumo->tipo_insumo,
                'inicial'        => $inicial,
                'inicial_fecha'  => $reg ? substr((string)$reg->fecha, 0, 10) : null,
                'por_dia'        => $porDia,
                'compras_dia'    => $comprasDia,
                'cantidad'       => $totalVentas,
                'compras'        => $totalCompras,
                'esperado'       => $esperado,
                'ventas'         => (int)$filasV->sum('ventas'),
                'importe'        => round((float)$filasV->sum('importe'), 2),
                'compras_importe' => round((float)$filasC->sum('importe'), 2),
            ];
        }

        return [
            'desde'   => $ini->toDateString(),
            'hasta'   => $fin->toDateString(),
            'dias'    => $resumenDias,
            'insumos' => $data,
            'totales' => [
                'ventas'          => array_sum(array_column($resumenDias, 'ventas')),
                'vendido'         => round(array_sum(array_column($resumenDias, 'total')), 2),
                'cantidad'        => round(array_sum(array_column($data, 'cantidad')), 2),
                'importe'         => round(array_sum(array_column($data, 'importe')), 2),
                'compras'         => round(array_sum(array_column($data, 'compras')), 2),
                'compras_importe' => round(array_sum(array_column($data, 'compras_importe')), 2),
                'inicial'         => round(array_sum(array_column($data, 'inicial')), 2),
            ],
        ];
    }

    private function col(int $index): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
    }

    private function rango(Request $request): array
    {
        $ini = $request->input('fechaInicio');
        $fin = $request->input('fechaFin');

        if ($ini && $fin) {
            $ini = Carbon::parse($ini)->startOfDay();
            $fin = Carbon::parse($fin)->startOfDay();
        } else {
            $ini = Carbon::now()->startOfWeek();
            $fin = Carbon::now()->endOfWeek()->startOfDay();
        }

        if ($fin->lt($ini)) {
            [$ini, $fin] = [$fin, $ini];
        }

        // máximo 31 días para no reventar la tabla
        if ($ini->diffInDays($fin) > 31) {
            $fin = $ini->copy()->addDays(31);
        }

        return [$ini, $fin];
    }

    private function dias(Carbon $ini, Carbon $fin): array
    {
        $nombres = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $dias = [];
        $cursor = $ini->copy();

        while ($cursor->lte($fin)) {
            $dias[] = [
                'fecha' => $cursor->toDateString(),
                'label' => $nombres[$cursor->dayOfWeek] . ' ' . $cursor->format('d/m'),
            ];
            $cursor->addDay();
        }

        return $dias;
    }
}
