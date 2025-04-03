<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General de Artículos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
            margin: 20px;
        }

        h1 {
            text-align: center;
            font-size: 18px;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .subheader {
            text-align: center;
            font-size: 11px;
            color: #555;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 5px;
            text-align: center;
            font-size: 9px;
        }

        th {
            background-color: #34495e;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) {
            background-color: #ffffff;
        }

        .right {
            text-align: right;
        }

        .status.activo {
            color: green;
            font-weight: bold;
        }

        .status.inactivo {
            color: red;
            font-weight: bold;
        }

        .footer {
            text-align: center;
            font-size: 9px;
            margin-top: 30px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }
    </style>
</head>

<body>

    <h1>Reporte General de Artículos</h1>
    <div class="subheader">Generado el {{ date('d/m/Y H:i:s') }}</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Categoría</th>
                <th>Modelo</th>
                <th>Nombre</th>
                <th>Stock</th>
                <th>Costo</th>
                <th>Precio Mayorista</th>
                <th>Precio Tienda</th>
                <th>Precio Factura</th>
                <th>Expiración</th>
                <th>Estado</th>
                <th>Creado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($articles as $article)
                <tr>
                    <td>{{ $article->id }}</td>
                    <td>{{ $article->category->name ?? 'Sin Categoría' }}</td>
                    <td>{{ $article->model ?? '-' }}</td>
                    <td>{{ $article->name }}</td>
                    <td class="right">{{ $article->stock }}</td>
                    <td class="right">Bs {{ number_format($article->cost, 2) }}</td>
                    <td class="right">Bs {{ number_format($article->wholesale_price, 2) }}</td>
                    <td class="right">Bs {{ number_format($article->store_price, 2) }}</td>
                    <td class="right">Bs {{ number_format($article->invoice_price, 2) }}</td>
                    <td>{{ $article->expiration_date ?? 'Sin fecha' }}</td>
                    <td class="status {{ $article->status ? 'activo' : 'inactivo' }}">
                        {{ $article->status ? 'Activo' : 'Inactivo' }}
                    </td>
                    <td>{{ $article->created_at->format('d/m/Y H:i:s') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Sistema generado automáticamente | © {{ date('Y') }}
    </div>

</body>

</html>
