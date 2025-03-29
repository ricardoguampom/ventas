<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Precios</title>
    <style>
        @page {
            margin: 20px;
            size: A4 portrait;
        }

        body {
            font-family: DejaVu Sans, Helvetica, sans-serif;
            margin: 20px;
            line-height: 1.6;
            color: #333;
            font-size: 11px;
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            margin: 0;
            font-size: 18px;
            color: #222;
        }

        header p {
            margin: 3px 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px;
        }

        th {
            background-color: #007bff;
            color: white;
            text-transform: uppercase;
            font-size: 10px;
        }

        td {
            font-size: 10px;
            text-align: right;
        }

        td:first-child, th:first-child {
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f8f8f8;
        }

        .total-row {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #999;
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
                <th>Precio Compra</th>
                <th>Precio Mayorista</th>
                <th>Precio Tienda</th>
                <th>Precio Factura</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($priceHistory as $history)
                <tr>
                    <td>{{ $history->created_at->format('d/m/Y H:i') }}</td>
                    <td>Bs {{ number_format($history->new_cost, 2, '.', ',') }}</td>
                    <td>Bs {{ number_format($history->new_wholesale_price, 2, '.', ',') }}</td>
                    <td>Bs {{ number_format($history->new_store_price, 2, '.', ',') }}</td>
                    <td>Bs {{ number_format($history->new_invoice_price, 2, '.', ',') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No hay datos en el historial de precios.</td>
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
        Documento generado automáticamente. Para consultas contacte a soporte.
    </div>
</body>

</html>
