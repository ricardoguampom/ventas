@extends('layouts.master')

@section('title', 'Gestión de Usuarios')

@section('header')
    <h1 class="text-center">Gestión de Usuarios</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
        <h3 class="card-title m-0">Lista de Usuarios</h3>

        <form method="GET" action="{{ route('users.index') }}" class="d-flex align-items-center gap-2">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Buscar usuario..." value="{{ request('search') }}">
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-search"></i> Buscar
            </button>
        </form>

        @can('usuarios.crear')
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-success">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </a>
        @endcan
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $user->role?->name == 'admin' ? 'danger' : 
                                    ($user->role?->name == 'seller' ? 'warning text-dark' : 'info') }}">
                                    {{ ucfirst($user->role?->name ?? 'Sin rol') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @can('usuarios.ver')
                                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-info me-1">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endcan
                                @can('usuarios.editar')
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning me-1">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('usuarios.eliminar')
                                    <button type="button" class="btn btn-sm btn-danger delete-user" data-url="{{ route('users.destroy', $user) }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">No hay usuarios registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll('.delete-user').forEach(button => {
            button.addEventListener('click', function () {
                let deleteUrl = this.getAttribute('data-url');

                Swal.fire({
                    title: "¿Eliminar usuario?",
                    text: "Esta acción no se puede deshacer.",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "Sí, eliminar",
                    cancelButtonText: "Cancelar",
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
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
                                Swal.fire("✅ Eliminado", "Usuario eliminado correctamente.", "success")
                                    .then(() => location.reload());
                            } else {
                                Swal.fire("❌ Error", data.error || "Ocurrió un error al eliminar.", "error");
                            }
                        })
                        .catch(() => {
                            Swal.fire("❌ Error", "Error de conexión inesperado.", "error");
                        });
                    }
                });
            });
        });
    });
</script>
@endsection
