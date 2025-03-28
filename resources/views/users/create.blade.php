@extends('layouts.master')

@section('title', 'Crear Usuario')

@section('header')
    <h1 class="text-center">Crear Usuario</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Nuevo Usuario</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name"><strong>Nombre</strong></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" required>
                    @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email"><strong>Correo Electrónico</strong></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" required>
                    @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password"><strong>Contraseña</strong></label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                    @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="password_confirmation"><strong>Confirmar Contraseña</strong></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="role_id"><strong>Rol</strong></label>
                    <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                        <option value="">-- Seleccionar Rol --</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ ucfirst($role->name) }}</option>
                        @endforeach
                    </select>
                    @error('role_id') <span class="text-danger small">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Crear Usuario
                </button>
                <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
