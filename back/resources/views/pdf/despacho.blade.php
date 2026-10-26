<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Despacho #{{ $despacho->id }}</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: DejaVu Sans, sans-serif;
        }
        body {
            font-size: 10px;
            margin: 10px;
            color: #333;
        }
        .header {
            padding: 8px 12px;
            border-radius: 6px;
            background: #c2431c;
            color: #fff;
            margin-bottom: 10px;
        }
        .header-title {
            font-size: 16px;
            font-weight: bold;
        }
        .subtitle {
            font-size: 11px;
        }
        .info-box {
            width: 100%;
            margin-bottom: 8px;
        }
        .info-box td {
            padding: 2px 6px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #a33314;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        .table th {
            background: #eeeeee;
            padding: 5px 4px;
            border-bottom: 1px solid #bdbdbd;
            text-align: left;
            font-size: 9px;
        }
        .table td {
            padding: 4px;
            border-bottom: 1px solid #eeeeee;
        }
        .table tfoot td {
            font-weight: bold;
            border-top: 1px solid #bdbdbd;
            background: #fafafa;
        }
        .right {
            text-align: right;
        }
        .falta {
            color: #c62828;
            font-weight: bold;
        }
        .ok {
            color: #2e7d32;
        }
        .coment {
            font-size: 8px;
            color: #666;
            font-style: italic;
        }
        .tag {
            display: inline-block;
            padding: 1px 8px;
            border-radius: 999px;
            font-size: 9px;
            color: #fff;
        }
        .tag-PENDIENTE { background-color: #f9a825; }
        .tag-COMPLETADO { background-color: #43a047; }
        .tag-ANULADO { background-color: #e53935; }
        .comentarios {
            margin-top: 10px;
            width: 100%;
        }
        .comentarios td {
            border: 1px solid #e0e0e0;
            padding: 6px;
            vertical-align: top;
            width: 50%;
        }
        .firmas {
            width: 100%;
            margin-top: 45px;
        }
        .firmas td {
            text-align: center;
            width: 50%;
        }
        .linea {
            border-top: 1px dashed #555;
            width: 70%;
            margin: 0 auto;
            padding-top: 3px;
        }
        .footer {
            margin-top: 15px;
            font-size: 8px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
@php
    $diferenciaTotal = $despacho->detalles->sum('diferencia');
@endphp
<div class="header">
    <div class="header-title">Nota de despacho de pollo #{{ $despacho->id }}</div>
    <div class="subtitle">Control de recepción: peso según nota vs. peso recibido</div>
</div>

<table class="info-box">
    <tr>
        <td width="35%">
            <div><span class="info-label">Fecha: </span>{{ \Carbon\Carbon::parse($despacho->fecha)->format('d/m/Y') }}</div>
            <div><span class="info-label">N° nota proveedor: </span>{{ $despacho->numero_nota ?: '—' }}</div>
            <div><span class="info-label">Proveedor: </span>{{ $despacho->proveedor ?: '—' }}</div>
        </td>
        <td width="35%">
            <div><span class="info-label">Cobrador: </span>{{ $despacho->cobrador ?: '—' }}</div>
            <div><span class="info-label">Recibido por: </span>{{ $despacho->user?->name ?: '—' }}</div>
            <div><span class="info-label">Completado por: </span>{{ $despacho->admin?->name ?: '—' }}</div>
        </td>
        <td width="30%">
            <div>
                <span class="info-label">Estado: </span>
                <span class="tag tag-{{ $despacho->estado }}">{{ $despacho->estado }}</span>
            </div>
            <div><span class="info-label">Registrado: </span>{{ $despacho->created_at?->format('d/m/Y H:i') }}</div>
        </td>
    </tr>
</table>

<table class="table">
    <thead>
    <tr>
        <th>#</th>
        <th>Env.</th>
        <th class="right">Peso</th>
        <th class="right">Tara</th>
        <th>Descripción</th>
        <th>Unidad</th>
        <th class="right">P-Bruto</th>
        <th class="right">P-Neto</th>
        <th class="right">Cantidad</th>
        <th class="right">P. recibido</th>
        <th class="right">Diferencia</th>
        <th class="right">Precio</th>
        <th class="right">Importe</th>
    </tr>
    </thead>
    <tbody>
    @foreach($despacho->detalles as $i => $det)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $det->envase ?: '—' }}</td>
            <td class="right">{{ number_format($det->peso_envase, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($det->tara, 2, ',', '.') }}</td>
            <td>
                {{ $det->producto?->nombre }}
                @if($det->comentario)
                    <div class="coment">{{ $det->comentario }}</div>
                @endif
            </td>
            <td>{{ $det->producto?->unidad }}</td>
            <td class="right">{{ number_format($det->peso_bruto, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($det->peso_neto, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($det->cantidad, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($det->peso_recibido, 2, ',', '.') }}</td>
            <td class="right {{ $det->diferencia > 0 ? 'falta' : 'ok' }}">
                {{ number_format($det->diferencia, 2, ',', '.') }}
            </td>
            <td class="right">{{ number_format($det->precio, 2, ',', '.') }}</td>
            <td class="right">{{ number_format($det->importe, 2, ',', '.') }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <td colspan="3">{{ $despacho->detalles->count() }} ítems</td>
        <td class="right">{{ number_format($despacho->detalles->sum('tara'), 2, ',', '.') }}</td>
        <td colspan="2"></td>
        <td class="right">{{ number_format($despacho->total_peso_bruto, 2, ',', '.') }}</td>
        <td class="right">{{ number_format($despacho->total_peso_neto, 2, ',', '.') }}</td>
        <td class="right">{{ number_format($despacho->detalles->sum('cantidad'), 2, ',', '.') }}</td>
        <td class="right">{{ number_format($despacho->total_peso_recibido, 2, ',', '.') }}</td>
        <td class="right {{ $diferenciaTotal > 0 ? 'falta' : 'ok' }}">{{ number_format($diferenciaTotal, 2, ',', '.') }}</td>
        <td class="right">TOTAL</td>
        <td class="right">{{ number_format($despacho->total, 2, ',', '.') }} Bs</td>
    </tr>
    </tfoot>
</table>

<table class="comentarios">
    <tr>
        <td>
            <span class="info-label">Comentario de recepción:</span><br>
            {{ $despacho->comentario ?: '—' }}
        </td>
        <td>
            <span class="info-label">Comentario del administrador:</span><br>
            {{ $despacho->comentario_admin ?: '—' }}
        </td>
    </tr>
</table>

<table class="firmas">
    <tr>
        <td><div class="linea">Entregué conforme</div></td>
        <td><div class="linea">Recibí conforme ({{ $despacho->user?->name }})</div></td>
    </tr>
</table>

<div class="footer">
    Diferencia = P-Neto de la nota − peso recibido (positivo = faltante) · Generado el {{ now()->format('d/m/Y H:i') }}
</div>
</body>
</html>
