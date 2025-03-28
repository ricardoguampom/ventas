<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ingresos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        h2, h3 { text-align: center; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .total-section { font-size: 14px; font-weight: bold; text-align: right; margin-top: 15px; }
        .footer { text-align: center; font-size: 12px; margin-top: 20px; border-top: 1px solid #ddd; padding-top: 10px; }
    </style>
</head>
<body>
    <h2>Reporte de Ingresos</h2>
    <p><strong>Rango de Fechas:</strong> {{ $startDate }} - {{ $endDate }}</p>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Proveedor</th>
                <th>Artículo</th>
                <th>Cantidad</th>
                <th>Precio Unitario (Bs)</th>
                <th>Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries as $entry)
                @foreach($entry->details as $detail)
                    <tr>
                        <td>{{ $entry->date }}</td>
                        <td>{{ $entry->supplier_name ?? 'N/A' }}</td>
                        <td>{{ $detail->article->name ?? 'Artículo desconocido' }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>Bs {{ number_format($detail->price, 2) }}</td>
                        <td>Bs {{ number_format($detail->quantity * $detail->price, 2) }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <p><strong>Total de Ingresos (Bs):</strong> {{ number_format($totalInvestment, 2) }}</p>
    </div>

    <div class="footer">
        <p>Reporte generado automáticamente.</p>
    </div>
</body>
</html>
