<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte: Todos los Artículos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            margin: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            font-size: 16px;
            color: #007bff;
            margin-bottom: 20px;
        }

        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 9px;
            word-wrap: break-word;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:nth-child(odd) {
            background-color: #fff;
        }

        .small-column {
            width: 50px;
        }

        .right-align {
            text-align: right;
        }

        .status {
            font-weight: bold;
        }

        .status.activo {
            color: green;
        }

        .status.inactivo {
            color: red;
        }
    </style>
</head>
<body>

    <h1>Reporte: Todos los Artículos</h1>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="small-column">ID</th>
                    <th>Categoría</th>
                    <th>Modelo</th>
                    <th>Nombre</th>
                    <th class="right-align">Stock</th>
                    <th class="right-align">Costo</th>
                    <th class="right-align">Precio Mayorista</th>
                    <th class="right-align">Precio Tienda</th>
                    <th class="right-align">Precio Factura</th>
                    <th>Fecha de Expiración</th>
                    <th>Estado</th>
                    <th>Creado En</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($articles as $article)
                    <tr>
                        <td>{{ $article->id }}</td>
                        <td>{{ $article->category->name ?? 'N/A' }}</td>
                        <td>{{ $article->model ?? 'N/A' }}</td>
                        <td>{{ $article->name }}</td>
                        <td class="right-align">{{ $article->stock }}</td>
                        <td class="right-align">Bs {{ number_format($article->cost, 2) }}</td>
                        <td class="right-align">Bs {{ number_format($article->wholesale_price, 2) }}</td>
                        <td class="right-align">Bs {{ number_format($article->store_price, 2) }}</td>
                        <td class="right-align">Bs {{ number_format($article->invoice_price, 2) }}</td>
                        <td>{{ $article->expiration_date ?? 'N/A' }}</td>
                        <td class="status {{ $article->status ? 'activo' : 'inactivo' }}">
                            {{ $article->status ? 'Activo' : 'Inactivo' }}
                        </td>
                        <td>{{ $article->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</body>
</html>
