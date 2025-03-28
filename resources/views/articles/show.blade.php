@extends('layouts.master')

@section('title', 'Detalles del Artículo')

@section('header')
    <h1 class="text-center">Detalles del Artículo: {{ $article->name }}</h1>
@endsection

@section('content')
@perm('articulos.ver')
<div class="card">
    <div class="card-body">
        {{-- 🏷 Información del Artículo --}}
        <div class="row">
            <div class="col-md-4 text-center">
                <img 
                    src="{{ $article->image ? asset('storage/' . $article->image) : 'https://via.placeholder.com/150' }}" 
                    alt="{{ $article->name }}" 
                    class="img-thumbnail shadow-sm rounded"
                    style="max-height: 250px;">
            </div>
            <div class="col-md-8">
                <table class="table table-bordered">
                    <tbody>
                        <tr><th width="30%">Categoría</th><td>{{ $article->category->name }}</td></tr>
                        <tr><th>Códigos de Barra</th>
                            <td>
                                @if($barcodes->isNotEmpty())
                                    {{ $barcodes->pluck('barcode')->implode(', ') }}
                                @else
                                    <span class="text-muted">Sin Código de Barra</span>
                                @endif
                            </td>
                        </tr>
                        <tr><th>Modelo</th><td>{{ $article->model ?? 'N/A' }}</td></tr>
                        <tr><th>Descripción</th><td>{{ $article->description ?? 'N/A' }}</td></tr>
                        <tr><th>Stock</th>
                            <td>
                                <span class="badge {{ $article->stock == 0 ? 'badge-danger' : 'badge-primary' }}">{{ $article->stock }}</span>
                                @if($article->stock == 0)
                                    <i class="fas fa-exclamation-triangle text-danger"></i>
                                @endif
                            </td>
                        </tr>
                        <tr><th>Fecha de Expiración</th><td>{{ $article->expiration_date ?? 'N/A' }}</td></tr>
                        <tr><th>Estado</th>
                            <td>
                                <span class="badge {{ $article->status ? 'badge-success' : 'badge-danger' }}">
                                    {{ $article->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 💰 Precios --}}
        <h4 class="mt-4">Precios del Artículo</h4>
        <table class="table table-bordered text-center">
            <thead class="thead-dark">
                <tr>
                    <th>Costo</th>
                    <th>Precio Mayorista</th>
                    <th>Precio de Tienda</th>
                    <th>Precio de Factura</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Bs {{ number_format($article->cost, 2) }}</td>
                    <td>Bs {{ number_format($article->wholesale_price, 2) }}</td>
                    <td>Bs {{ number_format($article->store_price, 2) }}</td>
                    <td>Bs {{ number_format($article->invoice_price, 2) }}</td>
                </tr>
            </tbody>
        </table>

        {{-- 📄 Exportar --}}
        @perm('articulos.reporte_exportar')
        <div class="text-center mb-4">
            <a href="{{ route('articles.export.csv', $article) }}" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Exportar CSV
            </a>
            <a href="{{ route('articles.export.pdf', $article) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
        </div>
        @endperm

        {{-- 📊 Historial --}}
        <h4>Historial de Precios</h4>
        <form method="GET" action="{{ route('articles.show', $article) }}" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="start_date">Fecha Inicio</label>
                    <input type="text" name="start_date" id="start_date" class="form-control datepicker" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date">Fecha Fin</label>
                    <input type="text" name="end_date" id="end_date" class="form-control datepicker" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="min_price">Precio Mínimo</label>
                    <input type="number" name="min_price" id="min_price" class="form-control" value="{{ request('min_price') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filtrar</button>
                </div>
            </div>
        </form>

        @if ($priceHistory->isNotEmpty())
            <table class="table table-bordered text-center">
                <thead class="thead-light">
                    <tr>
                        <th>Precio de Compra</th>
                        <th>Precio Mayorista</th>
                        <th>Precio de Tienda</th>
                        <th>Precio de Factura</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($priceHistory as $history)
                        <tr>
                            <td>Bs {{ number_format($history->new_cost, 2) }}</td>
                            <td>Bs {{ number_format($history->new_wholesale_price, 2) }}</td>
                            <td>Bs {{ number_format($history->new_store_price, 2) }}</td>
                            <td>Bs {{ number_format($history->new_invoice_price, 2) }}</td>
                            <td>{{ $history->created_at->format('Y-m-d H:i:s') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $priceHistory->appends(request()->query())->links() }}
            </div>
        @else
            <p class="text-center text-muted">No hay historial de precios disponible.</p>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('articles.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
</div>
@endperm
@endsection

@section('custom_js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            enableTime: false,
            allowInput: true
        });
    });
</script>
@endsection
