@extends('layouts.master')

@section('title', 'Gestión de Artículos')

@section('header')
    <h1 class="text-center">Gestión de Artículos</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Lista de Artículos</h3>
            <div>
                @perm('articulos.crear')
                    <a href="{{ route('articles.create') }}" class="btn btn-success">
                        <i class="fas fa-plus"></i> Nuevo Artículo
                    </a>
                @endperm
                @perm('articulos.reporte_exportar')
                    <a href="{{ route('articles.export.all.csv') }}" class="btn btn-primary">
                        <i class="fas fa-file-csv"></i> Exportar CSV
                    </a>
                    <a href="{{ route('articles.export.all.pdf') }}" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                @endperm
            </div>
        </div>

        @perm('articulos.ver_inversion')
        <div class="form-group">
            <label for="total_investment"><strong>Inversión Total:</strong></label>
            <input type="text" id="total_investment" class="form-control bg-white text-dark font-weight-bold" readonly>
        </div>
        @endperm

        <div class="card-body">
            {{-- 📌 Filtros de búsqueda --}}
            <form method="GET" action="{{ route('articles.index') }}" class="row g-3 p-3 border rounded bg-light">
                <div class="col-md-4">
                    <label for="query"><strong>Buscar Artículo</strong></label>
                    <input type="text" name="query" class="form-control" placeholder="Nombre, modelo, código de barras..." value="{{ request('query') }}">
                </div>
                <div class="col-md-2">
                    <label for="start_date"><strong>Fecha Inicio</strong></label>
                    <input type="text" name="start_date" id="start_date" class="form-control datepicker" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="end_date"><strong>Fecha Fin</strong></label>
                    <input type="text" name="end_date" id="end_date" class="form-control datepicker" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2">
                    <label for="status"><strong>Estado</strong></label>
                    <select name="status" class="form-control">
                        <option value="">Todos</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>

            {{-- 📊 Tabla de Artículos --}}
            <div class="table-responsive mt-4">
                <table class="table table-hover">
                    <thead class="thead-dark">
                        <tr>
                            <th>Imagen</th>
                            <th>Categoría</th>
                            <th>Modelo</th>
                            <th>Nombre</th>
                            <th>Códigos de Barras</th>
                            <th>Fecha de Caducidad</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($articles as $article)
                            <tr id="article-{{ $article->id }}" class="{{ $article->stock == 0 ? 'table-danger' : ($article->status == 0 ? 'table-light' : '') }}">
                                <td>
                                    @if($article->image)
                                        <img src="{{ asset('storage/' . $article->image) }}" width="50" height="50" alt="{{ $article->name }}">
                                    @else
                                        <span class="badge badge-secondary">Sin Imagen</span>
                                    @endif
                                </td>
                                <td>{{ $article->category->name ?? 'Sin Categoría' }}</td>
                                <td>{{ $article->model ?? 'N/A' }}</td>
                                <td>{{ $article->name }}</td>
                                <td>
                                    @if($article->barcodes->count() > 0)
                                        {{ $article->barcodes->first()->barcode }}
                                    @else
                                        <span class="badge badge-secondary">Sin Código</span>
                                    @endif
                                </td>
                                <td>{{ $article->expiration_date ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if($article->stock == 0)
                                        <span class="badge badge-danger">0 <i class="fas fa-exclamation-circle"></i></span>
                                    @else
                                        <span class="badge badge-primary">{{ $article->stock }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $article->status ? 'badge-success' : 'badge-dark' }}">
                                        {{ $article->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    @perm('articulos.ver')
                                        <a href="{{ route('articles.show', $article) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    @endperm

                                    @perm('articulos.editar')
                                        <a href="{{ route('articles.edit', $article) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i> Editar
                                        </a>
                                    @endperm

                                    @perm('articulos.eliminar')
                                        <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="{{ $article->id }}" data-url="{{ route('articles.destroy', $article) }}">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    @endperm
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">No se encontraron artículos.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 📌 Paginación --}}
            <div class="d-flex justify-content-center mt-3">
                {{ $articles->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        @perm('articulos.ver_inversion')
        fetch("{{ route('articles.investment') }}")
            .then(response => response.json())
            .then(data => {
                document.getElementById("total_investment").value = data.total_investment ? `Bs ${data.total_investment}` : "No disponible";
            })
            .catch(error => console.error("Error al obtener la inversión total:", error));
        @endperm

        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            enableTime: false,
            allowInput: true,
            locale: 'es'
        });

        document.querySelectorAll('.delete-btn').forEach(button => {
            button.addEventListener('click', function () {
                let articleId = this.getAttribute('data-id');
                let deleteUrl = this.getAttribute('data-url');

                Swal.fire({
                    title: "¿Estás seguro?",
                    text: "Esta acción no se puede deshacer.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar",
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(deleteUrl, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById(`article-${articleId}`).remove();
                                Swal.fire("✅ Eliminado", "El artículo ha sido eliminado.", "success");
                            } else {
                                Swal.fire("❌ Error", data.error || "Ocurrió un problema al eliminar.", "error");
                            }
                        })
                        .catch(error => {
                            Swal.fire("❌ Error", "Error inesperado en la conexión.", "error");
                        });
                    }
                });
            });
        });
    });
</script>
@endsection
