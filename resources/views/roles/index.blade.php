@extends('layouts.master')

@section('title', 'Gestión de Roles')

@section('header')
    <h1 class="text-center">Gestión de Roles</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title mb-2">Lista de Roles</h3>

        <form method="GET" action="{{ route('roles.index') }}" class="d-flex gap-2 mb-2 mb-md-0">
            <input type="text" name="filter" class="form-control form-control-sm" placeholder="Buscar por nombre de rol" value="{{ request('filter') }}">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
        </form>

        @can('roles.crear')
            <a href="{{ route('roles.create') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus"></i> Nuevo Rol
            </a>
        @endcan
    </div>

    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover align-middle">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre del Rol</th>
                    <th>Permisos Asignados</th>
                    <th>Usuarios</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td class="text-capitalize fw-bold">{{ $role->name }}</td>
                        <td>
                            @forelse($role->permissions as $perm)
                                <span class="badge bg-info mb-1">{{ ucwords(str_replace('_', ' ', $perm->name)) }}</span>
                            @empty
                                <span class="badge bg-secondary">Sin permisos</span>
                            @endforelse
                        </td>
                        <td class="text-center">
                            <span class="badge bg-primary">{{ $role->users_count ?? 0 }}</span>
                            @can('roles.verUsuarios')
                                <a href="{{ route('roles.users', $role) }}" class="btn btn-outline-primary btn-sm mt-1">
                                    <i class="fas fa-users"></i> Ver Usuarios
                                </a>
                            @endcan
                        </td>
                        <td class="text-center">
                            @can('roles.ver')
                                <a href="{{ route('roles.show', $role) }}" class="btn btn-info btn-sm me-1" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                            @endcan
                            @can('roles.editar')
                                <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning btn-sm me-1" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            @endcan
                            @can('roles.eliminar')
                                <form action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline delete-form">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" type="submit" title="Eliminar">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No hay roles registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="d-flex justify-content-center mt-3">
            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Eliminar rol?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
