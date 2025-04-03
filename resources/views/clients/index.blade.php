@extends('layouts.master')

@section('title', 'Gestión de Clientes')

@section('header')
    <h1 class="text-center">Gestión de Clientes</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-2">Lista de Clientes</h3>
        @can('clientes.crear')
            <a href="{{ route('clients.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nuevo Cliente
            </a>
        @endcan
    </div>

    {{-- Buscador --}}
    <div class="card-body">
        <form method="GET" action="{{ route('clients.index') }}" class="row g-2 mb-3">
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
                        <th>Documento</th>
                        <th>Email</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($clients as $client)
                        <tr>
                            <td>{{ $client->name }}</td>
                            <td>{{ $client->document_number ?? 'N/A' }}</td>
                            <td>{{ $client->email ?? 'N/A' }}</td>
                            <td>{{ $client->phone ?? 'N/A' }}</td>
                            <td>{{ $client->address ?? 'N/A' }}</td>
                            <td class="text-center">
                                @can('clientes.ver')
                                    <a href="{{ route('clients.show', $client) }}" class="btn btn-info btn-sm me-1" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan

                                @can('clientes.editar')
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning btn-sm me-1" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan

                                @can('clientes.eliminar')
                                    <form action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas eliminar este cliente?')">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No se encontraron clientes.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $clients->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
