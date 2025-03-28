@extends('layouts.master')

@section('title', 'Detalle del Usuario')

@section('header')
    <h1 class="text-center">Detalles del Usuario</h1>
@endsection

@section('content')
@can('usuarios.ver')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title">Información del Usuario</h3>
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver a la Lista
        </a>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Nombre:</strong>
                <p>{{ $user->name }}</p>
            </div>

            <div class="col-md-4">
                <strong>Email:</strong>
                <p>{{ $user->email }}</p>
            </div>

            <div class="col-md-4">
                <strong>Rol:</strong>
                <p>
                    @if($user->role)
                    <span class="badge text-capitalize {{ 
                        $user->role->name === 'admin' ? 'bg-danger' :
                        ($user->role->name === 'seller' ? 'bg-warning text-dark' : 'bg-info') }}">
                        {{ $user->role->name }}
                    </span>
                    @else
                        <span class="badge bg-secondary">Sin rol</span>
                    @endif
                </p>
            </div>

            <div class="col-md-6">
                <strong>Creado el:</strong>
                <p>{{ $user->created_at->format('d/m/Y H:i') }}</p>
            </div>

            <div class="col-md-6">
                <strong>Actualizado el:</strong>
                <p>{{ $user->updated_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Permisos del Rol (agrupados y legibles) --}}
        @if($user->role && $user->role->permissions->count())
            <div class="mt-4">
                <h5><i class="fas fa-key"></i> Permisos Asignados al Rol</h5>

                @php
                    $grouped = $user->role->permissions->groupBy(function ($perm) {
                        return explode('.', $perm->name)[0]; // agrupar por módulo
                    });
                @endphp

                <div class="row mt-3">
                    @foreach($grouped as $module => $permissions)
                        <div class="col-md-4 mb-3">
                            <h6 class="text-uppercase text-primary border-bottom pb-1">
                                <i class="fas fa-folder-open"></i> {{ ucfirst($module) }}
                            </h6>
                            <ul class="list-group list-group-flush">
                                @foreach($permissions as $perm)
                                    <li class="list-group-item px-2 py-1 small text-muted">
                                        {{ str_replace('_', ' ', ucwords(str_replace('.', ' ', $perm->name))) }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif


        <div class="d-flex justify-content-end mt-4">
            @can('usuarios.editar')
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Usuario
                </a>
            @endcan
        </div>
    </div>
</div>
@endcan
@endsection
