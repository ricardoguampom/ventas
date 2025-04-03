<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas Pagadas</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; margin: 15px; color: #222; }
        h2, h3 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; font-size: 12px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #555; padding: 6px; }
        th { background-color: #eee; font-weight: bold; }
        .total-section { margin-top: 15px; text-align: right; font-weight: bold; }
        .footer { text-align: center; font-size: 11px; margin-top: 20px; border-top: 1px solid #aaa; padding-top: 5px; color: #555; }
    </style>
</head>

<body>

    <h2>Reporte de Ventas Pagadas</h2>
    <p><strong>Desde:</strong> {{ $startDate ?? '---' }} &nbsp; | &nbsp; <strong>Hasta:</strong> {{ $endDate ?? '---' }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Cliente</th>
                <th>Factura</th>
                <th>Usuario</th>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th>Precio Unitario (Bs)</th>
                <th>Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @php $counter = 1; @endphp
            @foreach($sales as $sale)
                @foreach($sale->details as $detail)
                    <tr>
                        <td>{{ $counter++ }}</td>
                        <td>{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($sale->client)->name ?? $sale->customer_name ?? 'Sin Cliente' }}</td>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ optional($sale->user)->name ?? 'Usuario no disponible' }}</td>
                        <td>{{ $detail->article->name ?? 'Artículo eliminado' }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td class="right">Bs {{ number_format($detail->price, 2) }}</td>
                        <td class="right">Bs {{ number_format($detail->quantity * $detail->price, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Total Ventas Pagadas (Bs):</strong> {{ number_format($totalSalesAmount, 2) }}</p>
        <p><strong>Número de Ventas Realizadas:</strong> {{ $totalSalesCount }}</p>
    </div>

    <div class="footer">
        <p>Generado automáticamente | Mavatec &copy; {{ date('Y') }}</p>
    </div>

</body>

</html>
