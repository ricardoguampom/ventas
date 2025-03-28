@extends('layouts.master')

@section('title', 'Editar Usuario')

@section('header')
    <h1 class="text-center">Editar Usuario</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Editar Usuario</h3>
    </div>

    <div class="card-body">
        <form action="{{ route('users.update', $user) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name"><strong>Nombre</strong></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name', $user->name) }}" required>
                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email"><strong>Correo Electrónico</strong></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email', $user->email) }}" required>
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password">
                        <strong>Contraseña</strong>
                        <small class="text-muted">(Déjalo vacío si no deseas cambiarla)</small>
                    </label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation"><strong>Confirmar Contraseña</strong></label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="role_id"><strong>Rol</strong></label>
                    <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                {{ ucfirst($role->name) }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4 d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar Usuario
                </button>

                <a href="{{ route('users.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
