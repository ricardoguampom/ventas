<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cotizaciones</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; }
        .total { font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <h2>Reporte de Cotizaciones</h2>
    <p><strong>Desde:</strong> {{ $startDate ?? 'No definido' }} - <strong>Hasta:</strong> {{ $endDate ?? 'No definido' }}</p>

    @foreach($quotations as $index => $q)
        <table>
            <thead>
                <tr>
                    <th colspan="5">Cotización #{{ $index + 1 }}</th>
                </tr>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Cliente</th>
                    <th>Total Cotización (Bs)</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $q->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>{{ $q->user->name ?? 'Desconocido' }}</td>
                    <td>{{ $q->customer_name }}</td>
                    <td>{{ number_format($q->total, 2) }}</td>
                    <td>{{ ucfirst($q->status) }}</td>
                </tr>
            </tbody>
        </table>

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
                @foreach($q->details as $detail)
                    <tr>
                        <td>{{ $detail->article->name ?? 'Artículo eliminado' }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>{{ number_format($detail->price, 2) }}</td>
                        <td>{{ number_format($detail->quantity * $detail->price, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="3" class="total">Total Cotización:</td>
                    <td class="total">{{ number_format($q->total, 2) }} Bs</td>
                </tr>
            </tbody>
        </table>

        <hr>
    @endforeach
</body>
</html>
