<!-- resources/views/sales/sale_pdf.blade.php -->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $sale->is_quotation ? 'Cotización' : 'Factura' }} N° {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        h1, h3 {
            text-align: center;
            margin: 0;
        }
        .info, .details {
            margin-top: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }
        th {
            background: #444;
            color: #fff;
        }
        .right { text-align: right; }
        .center { text-align: center; }
        .footer {
            margin-top: 30px;
            font-size: 10px;
            text-align: center;
            color: #777;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            color: #fff;
            font-size: 9px;
        }
        .badge-info { background: #17a2b8; }
        .badge-primary { background: #007bff; }
    </style>
</head>
<body>

    <h1>{{ $sale->is_quotation ? 'Cotización' : 'Factura' }} N° {{ $sale->invoice_number }}</h1>

    <div class="info">
        <p><strong>Cliente:</strong> {{ optional($sale->client)->name ?? $sale->customer_name }}</p>
        <p><strong>Documento:</strong> {{ optional($sale->client)->document_number ?? '-' }}</p>
        <p><strong>Fecha:</strong> {{ $sale->created_at->format('d/m/Y H:i:s') }}</p>
        <p><strong>Estado:</strong> 
            <span class="badge {{ $sale->is_quotation ? 'badge-info' : 'badge-primary' }}">
                {{ $sale->is_quotation ? 'Cotización' : 'Venta' }}
            </span>
        </p>
    </div>

    <h3>Detalle</h3>
    <table>
        <thead>
            <tr>
                <th>Artículo</th>
                <th>Descripción</th>
                <th class="center">Cantidad</th>
                <th class="right">Precio Unitario (Bs)</th>
                <th class="right">Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $detail)
            <tr>
                <td>{{ $detail->article->name }}</td>
                <td>{{ $detail->article->description }}</td>
                <td class="center">{{ $detail->quantity }}</td>
                <td class="right">{{ number_format($detail->price, 2) }}</td>
                <td class="right">{{ number_format($detail->quantity * $detail->price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="right">Total</td>
                <td class="right"><strong>Bs {{ number_format($sale->total, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Documento generado automáticamente | Mavatec &copy; {{ date('Y') }}
    </div>

</body>
</html>
