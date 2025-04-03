<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Reporte de Ingresos</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 10px; margin: 15px; color: #333; }
        h2, h3 { text-align: center; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        th, td { border: 1px solid #999; padding: 5px; font-size: 9px; }
        th { background-color: #3498db; color: white; text-align: center; }
        td { text-align: center; }
        .subtotal { background-color: #f2f2f2; font-weight: bold; }
        .total { font-size: 11px; font-weight: bold; text-align: right; margin-top: 10px; }
        .footer { text-align: center; font-size: 9px; margin-top: 15px; color: #777; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>

<body>

    <h2>Reporte de Ingresos</h2>
    <p><strong>Rango de Fechas:</strong> {{ $startDate }} - {{ $endDate }}</p>

    @foreach($entries as $entry)
        <h3>Ingreso #{{ $entry->id }} | Fecha: {{ \Carbon\Carbon::parse($entry->date)->format('d/m/Y') }}</h3>
        <p><strong>Proveedor:</strong> {{ $entry->provider->name ?? 'Sin proveedor' }}</p>

        <table>
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th>Cantidad</th>
                    <th>Precio Unitario (Bs)</th>
                    <th>Subtotal (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @php $entryTotal = 0; @endphp
                @foreach($entry->details as $detail)
                    @php $subtotal = $detail->quantity * $detail->price; $entryTotal += $subtotal; @endphp
                    <tr>
                        <td>{{ $detail->article->name ?? 'Artículo desconocido' }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>Bs {{ number_format($detail->price, 2) }}</td>
                        <td>Bs {{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr class="subtotal">
                    <td colspan="3" style="text-align: right;">Total Ingreso:</td>
                    <td>Bs {{ number_format($entryTotal, 2) }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <div class="total">
        <p><strong>Total General de Ingresos:</strong> Bs {{ number_format($totalInvestment, 2) }}</p>
    </div>

    <div class="footer">
        Reporte generado automáticamente por {{ config('app.name') }} - {{ date('d/m/Y H:i') }}
    </div>

</body>

</html>
