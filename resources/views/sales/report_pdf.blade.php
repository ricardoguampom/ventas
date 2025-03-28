<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h2, h3 { text-align: center; margin-bottom: 10px; }
        p { text-align: center; font-size: 14px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-section { font-size: 14px; font-weight: bold; text-align: right; margin-top: 15px; }
        .footer { text-align: center; font-size: 12px; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <h2>Reporte de Ventas Pagadas</h2>
    <p><strong>Rango de Fechas:</strong> {{ $startDate }} - {{ $endDate }}</p>

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
                        <td>{{ $counter++ }}</td> {{-- ✅ ID de conteo --}}
                        <td>{{ $sale->created_at }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->user->name }}</td> {{-- ✅ Usuario que realizó la venta --}}
                        <td>{{ $detail->article->name }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>Bs {{ number_format($detail->price, 2) }}</td>
                        <td>Bs {{ number_format($detail->quantity * $detail->price, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    {{-- 📌 Resumen Total --}}
    <div class="total-section">
        <p><strong>Total de Ventas Pagadas (Bs):</strong> {{ number_format($totalSalesAmount, 2) }}</p>
        <p><strong>Número de Ventas Realizadas:</strong> {{ $totalSalesCount }}</p>
    </div>

    {{-- 📌 Pie de Página --}}
    <div class="footer">
        <p>Reporte generado automáticamente por el sistema.</p>
    </div>
</body>
</html>
