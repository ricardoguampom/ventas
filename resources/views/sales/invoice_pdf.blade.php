<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $sale->is_quotation ? 'Cotización' : 'Factura' }}: {{ $sale->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
            color: #333;
        }
        h1, h3 {
            text-align: center;
            margin-bottom: 10px;
        }
        p {
            margin: 5px 0;
        }
        .info {
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
            padding: 8px;
            text-align: left;
        }
        td {
            padding: 8px;
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total {
            text-align: right;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #777;
        }
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
            font-size: 10px;
        }
        .badge-info {
            background-color: #17a2b8;
        }
        .badge-primary {
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <h1>{{ $sale->is_quotation ? 'Cotización' : 'Factura' }}: {{ $sale->invoice_number }}</h1>
    
    <div class="info">
        <p><strong>Nombre del Cliente:</strong> {{ $sale->customer_name }}</p>
        <p><strong>Fecha:</strong> {{ $sale->created_at->format('Y-m-d H:i:s') }}</p>
        <p><strong>Estado:</strong> 
            <span class="badge {{ $sale->is_quotation ? 'badge-info' : 'badge-primary' }}">
                {{ $sale->is_quotation ? 'Cotización' : 'Venta' }}
            </span>
        </p>
    </div>

    <h3>Detalles de la {{ $sale->is_quotation ? 'Cotización' : 'Venta' }}</h3>
    <table>
        <thead>
            <tr>
                <th>Artículo</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Precio (Bs)</th>
                <th>Subtotal (Bs)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->details as $detail)
                <tr>
                    <td>{{ $detail->article->name }}</td>
                    <td>{{ $detail->article->description }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ number_format($detail->price, 2) }}</td>
                    <td>{{ number_format($detail->quantity * $detail->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="total">Total:</td>
                <td class="total">Bs {{ number_format($sale->total, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Este documento fue generado automáticamente.</p>
        <p>&copy; {{ date('Y') }} Mavatec. Todos los derechos reservados.</p>
    </div>
</body>
</html>
