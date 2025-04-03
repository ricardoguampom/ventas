<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas Pendientes</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
        }
        .title {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .subtitle {
            text-align: center;
            font-size: 12px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 10px;
        }
        th {
            background-color: #f0f0f0;
        }
        .total {
            font-weight: bold;
            background-color: #f8d7da;
        }
        .right { text-align: right; }
        .center { text-align: center; }
    </style>
</head>
<body>

    <div class="title">Reporte de Ventas Pendientes</div>
    <div class="subtitle">
        <strong>Rango de Fechas:</strong> {{ $startDate ?? 'Sin filtro' }} - {{ $endDate ?? 'Sin filtro' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Cliente</th>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th>Precio Unitario (Bs)</th>
                <th>Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @php $count = 1; $totalGeneral = 0; @endphp
            @foreach($pendingSales as $sale)
                @php $saleTotal = 0; @endphp
                @foreach($sale->details as $detail)
                    @php
                        $subtotal = $detail->quantity * $detail->price;
                        $saleTotal += $subtotal;
                        $totalGeneral += $subtotal;
                    @endphp
                    <tr>
                        <td class="center">{{ $count++ }}</td>
                        <td>{{ $sale->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ optional($sale->user)->name ?? 'Desconocido' }}</td>
                        <td>{{ optional($sale->client)->name ?? $sale->customer_name ?? 'No registrado' }}</td>
                        <td>{{ $detail->article->name ?? 'Artículo eliminado' }}</td>
                        <td class="center">{{ $detail->quantity }}</td>
                        <td class="right">{{ number_format($detail->price, 2, '.', ',') }}</td>
                        <td class="right">{{ number_format($subtotal, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="7" class="total right">Total Venta:</td>
                    <td class="total right">{{ number_format($saleTotal, 2, '.', ',') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="7" class="total right"><strong>TOTAL GENERAL:</strong></td>
                <td class="total right"><strong>{{ number_format($totalGeneral, 2, '.', ',') }} Bs</strong></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
