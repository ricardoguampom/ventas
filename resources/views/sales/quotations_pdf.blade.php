<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Cotizaciones</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #333; }
        h2 { text-align: center; margin-bottom: 5px; }
        p { text-align: center; margin-bottom: 20px; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        th, td { border: 1px solid #333; padding: 6px; }
        th { background-color: #f0f0f0; }
        .total { font-weight: bold; text-align: right; }
        .center { text-align: center; }
        .right { text-align: right; }
    </style>
</head>

<body>
    <h2>Reporte de Cotizaciones</h2>
    <p><strong>Desde:</strong> {{ $startDate ?? 'No definido' }} &nbsp;&nbsp; <strong>Hasta:</strong> {{ $endDate ?? 'No definido' }}</p>

    @foreach($quotations as $index => $q)
        {{-- Cabecera de la Cotización --}}
        <table>
            <thead>
                <tr>
                    <th colspan="5">Cotización #{{ str_pad($q->id, 6, '0', STR_PAD_LEFT) }}</th>
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
                    <td class="center">{{ $q->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ optional($q->user)->name ?? 'Desconocido' }}</td>
                    <td>{{ optional($q->client)->name ?? $q->customer_name ?? 'Sin Cliente' }}</td>
                    <td class="right">{{ number_format($q->total, 2) }}</td>
                    <td class="center">{{ ucfirst($q->status) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Detalle de Artículos --}}
        <table>
            <thead>
                <tr>
                    <th>Artículo</th>
                    <th class="center">Cantidad</th>
                    <th class="right">Precio Unitario (Bs)</th>
                    <th class="right">Subtotal (Bs)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($q->details as $detail)
                    <tr>
                        <td>{{ $detail->article->name ?? 'Artículo eliminado' }}</td>
                        <td class="center">{{ $detail->quantity }}</td>
                        <td class="right">{{ number_format($detail->price, 2) }}</td>
                        <td class="right">{{ number_format($detail->quantity * $detail->price, 2) }}</td>
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
