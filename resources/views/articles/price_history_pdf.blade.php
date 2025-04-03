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
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            margin: 20px;
            line-height: 1.5;
            color: #333;
            font-size: 10px;
        }

        header {
            text-align: center;
            margin-bottom: 15px;
        }

        header h1 {
            font-size: 18px;
            margin: 0;
            color: #2c3e50;
        }

        header p {
            margin: 2px 0;
            font-size: 11px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px;
            font-size: 9px;
            text-align: right;
        }

        th {
            background-color: #34495e;
            color: white;
            text-transform: uppercase;
            font-size: 9px;
        }

        td:first-child, th:first-child {
            text-align: left;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .total-row {
            background-color: #34495e;
            color: #fff;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            margin-top: 25px;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <header>
        <h1>Historial de Precios</h1>
        <p><strong>Artículo:</strong> {{ $article->name }}</p>
        <p><strong>Categoría:</strong> {{ $article->category->name ?? 'Sin Categoría' }}</p>
        <p><strong>Modelo:</strong> {{ $article->model ?? 'No especificado' }}</p>
        <p><strong>Generado el:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
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
                    <td colspan="5" style="text-align: center;">No hay registros disponibles.</td>
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
        Documento generado automáticamente - {{ config('app.name') }} | {{ date('Y') }}
    </div>
</body>

</html>
