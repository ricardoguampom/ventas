@extends('layouts.master')

@section('title', 'Detalle del Rol')

@section('header')
    <h1 class="text-center">Detalles del Rol</h1>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">Rol: <strong>{{ $role->name }}</strong></h3>
        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>

    <div class="card-body">
        <table class="table table-bordered mb-4">
            <tr>
                <th>Nombre del Rol</th>
                <td>{{ ucfirst($role->name) }}</td>
            </tr>
            <tr>
                <th>Creado el</th>
                <td>{{ $role->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <th>Actualizado el</th>
                <td>{{ $role->updated_at->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        {{-- Permisos agrupados por módulo --}}
        <h5 class="mb-3"><i class="fas fa-key"></i> Permisos Asignados</h5>

        @if($role->permissions->isEmpty())
            <p class="text-muted">Este rol no tiene permisos asignados.</p>
        @else
            @php
                $grouped = $role->permissions->groupBy(function($perm) {
                    return explode('.', $perm->name)[0];
                });
            @endphp

            @foreach($grouped as $module => $perms)
                <div class="mb-3">
                    <h6 class="text-uppercase text-primary fw-bold">
                        <i class="fas fa-folder-open me-1"></i> {{ ucfirst($module) }}
                    </h6>
                    @foreach($perms as $perm)
                        <span class="badge bg-info text-dark me-1 mb-1">
                            {{ ucwords(str_replace('_', ' ', str_replace($module . '.', '', $perm->name))) }}
                        </span>
                    @endforeach
                </div>
            @endforeach
        @endif

        {{-- Usuarios asignados (si se tiene permiso) --}}
        @can('roles.verUsuarios')
            <hr>
            <h5 class="mb-3"><i class="fas fa-users"></i> Usuarios con este Rol</h5>

            @php $users = $role->users()->get(); @endphp

            @if($users->isEmpty())
                <p class="text-muted">No hay usuarios asignados a este rol.</p>
            @else
                <ul class="list-group list-group-flush">
                    @foreach($users as $user)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-circle me-2"></i>{{ $user->name }}</span>
                            <span class="text-muted small">{{ $user->email }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endcan

        <div class="d-flex justify-content-end mt-4">
            @can('roles.editar')
                <a href="{{ route('roles.edit', $role) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Editar Rol
                </a>
            @endcan
        </div>
    </div>
</div>
@endsection
