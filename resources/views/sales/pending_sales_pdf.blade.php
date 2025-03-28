<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas Pendientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
        }
        .subtitle {
            text-align: center;
            font-size: 14px;
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: center;
            font-size: 12px;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            background-color: #f8d7da;
        }
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
                        <td>{{ $count++ }}</td>
                        <td>{{ $sale->created_at->format('Y-m-d H:i:s') }}</td>
                        <td>{{ $sale->user->name ?? 'Desconocido' }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td>{{ $detail->article->name ?? 'Artículo eliminado' }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ number_format($detail->price, 2, '.', ',') }}</td>
                        <td>{{ number_format($subtotal, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
                {{-- 🏷️ Total por Venta --}}
                <tr>
                    <td colspan="7" class="total">Total Venta:</td>
                    <td class="total">{{ number_format($saleTotal, 2, '.', ',') }}</td>
                </tr>
            @endforeach
        </tbody>
        {{-- 🏷️ Gran Total General --}}
        <tfoot>
            <tr>
                <td colspan="7" class="total"><strong>TOTAL GENERAL:</strong></td>
                <td class="total"><strong>{{ number_format($totalGeneral, 2, '.', ',') }} Bs</strong></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
