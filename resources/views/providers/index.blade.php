@extends('layouts.master')

@section('title', 'Gestión de Proveedores')

@section('header')
    <h1 class="text-center">Gestión de Proveedores</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-2">Lista de Proveedores</h3>
        @can('proveedores.crear')
            <a href="{{ route('providers.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nuevo Proveedor
            </a>
        @endcan
    </div>

    {{-- Buscador --}}
    <div class="card-body">
        <form method="GET" action="{{ route('providers.index') }}" class="row g-2 mb-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar por cualquier campo..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </form>

        {{-- Tabla --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Tipo Documento</th>
                        <th>Número Documento</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($providers as $provider)
                        <tr>
                            <td>{{ $provider->name }}</td>
                            <td>{{ $provider->document_type ?? 'N/A' }}</td>
                            <td>{{ $provider->document_number ?? 'N/A' }}</td>
                            <td>{{ $provider->email ?? 'N/A' }}</td>
                            <td>{{ $provider->phone ?? 'N/A' }}</td>
                            <td>{{ $provider->address ?? 'N/A' }}</td>
                            <td class="text-center">
                                @can('proveedores.ver')
                                    <a href="{{ route('providers.show', $provider) }}" class="btn btn-info btn-sm me-1" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                @can('proveedores.editar')
                                    <a href="{{ route('providers.edit', $provider) }}" class="btn btn-warning btn-sm me-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('proveedores.eliminar')
                                    <form action="{{ route('providers.destroy', $provider) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas eliminar este proveedor?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">No se encontraron proveedores.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $providers->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
