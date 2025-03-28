<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Precios</title>
    <style>
        /* Global Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        header {
            text-align: center;
            margin-bottom: 20px;
        }
        header h1 {
            margin: 0;
            font-size: 20px;
            color: #333;
        }
        header p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        /* Table Styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            padding: 10px;
        }
        td {
            padding: 8px;
            text-align: right;
            color: #333;
        }
        td:first-child, th:first-child {
            text-align: left;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .total-row {
            font-weight: bold;
            background-color: #007bff;
            color: white;
        }

        /* Footer Styles */
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #aaa;
        }
    </style>
</head>
<body>
    <header>
        <h1>Historial de Precios</h1>
        <p><strong>Artículo:</strong> {{ $article->name }}</p>
        <p><strong>Categoría:</strong> {{ $article->category->name }}</p>
        <p><strong>Modelo:</strong> {{ $article->model ?? 'N/A' }}</p>
        <p><strong>Generado el:</strong> {{ now()->format('Y-m-d H:i:s') }}</p>
    </header>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Precio de Compra</th>
                <th>Precio Mayorista</th>
                <th>Precio de Tienda</th>
                <th>Precio de Factura</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($priceHistory as $history)
                <tr>
                    <td>{{ $history->created_at->format('Y-m-d H:i:s') }}</td>
                    <td>Bs {{ number_format($history->new_cost, 2) }}</td>
                    <td>Bs {{ number_format($history->new_wholesale_price, 2) }}</td>
                    <td>Bs {{ number_format($history->new_store_price, 2) }}</td>
                    <td>Bs {{ number_format($history->new_invoice_price, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay datos disponibles en el historial de precios.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="4" style="text-align: right;">Total de registros:</td>
                <td>{{ $priceHistory->count() }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Documento generado automáticamente. Si tiene preguntas, comuníquese con soporte.</p>
    </div>
</body>
</html>
