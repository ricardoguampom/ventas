@extends('layouts.master')

@section('title', 'Gestión de Categorías')

@section('header')
    <h1 class="text-center">Gestión de Categorías</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-2">Lista de Categorías</h3>
        @perm('categorias.crear')
            <a href="{{ route('categories.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nueva Categoría
            </a>
        @endperm
    </div>

    <div class="card-body">
        {{-- 🔎 Filtros --}}
        <form action="{{ route('categories.index') }}" method="GET" class="row g-2 mb-4">
            <div class="col-md-5">
                <input type="text" name="search" class="form-control" placeholder="Buscar nombre o descripción" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">-- Estado --</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>

        {{-- 📋 Tabla --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->description ?? 'Sin descripción' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ $category->status ? 'bg-success' : 'bg-danger' }} px-3 py-2">
                                    <i class="fas {{ $category->status ? 'fa-check-circle' : 'fa-times-circle' }} me-1"></i>
                                    {{ $category->status ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="text-center">
                                @perm('categorias.editar')
                                    <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                @endperm

                                @perm('categorias.eliminar')
                                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                                        data-toggle="modal"
                                        data-target="#deleteModal"
                                        data-url="{{ route('categories.destroy', $category) }}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                @endperm
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No se encontraron categorías.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $categories->appends(request()->query())->links() }}
        </div>
    </div>
</div>

{{-- 🗑 Modal de Confirmación --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="deleteForm" method="POST" class="modal-content">
            @csrf
            @method('DELETE')
            <div class="modal-header">
                <h5 class="modal-title">¿Eliminar Categoría?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                Esta acción es irreversible. ¿Deseas continuar?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-danger">Eliminar</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const deleteForm = document.getElementById('deleteForm');

        document.querySelectorAll('.delete-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                deleteForm.action = btn.getAttribute('data-url');
            });
        });

        const success = "{{ session('success') }}";
        const error = "{{ session('error') }}";

        if (success) {
            Swal.fire('Éxito', success, 'success');
        }

        if (error) {
            Swal.fire('Error', error, 'error');
        }
    });
</script>
@endsection
