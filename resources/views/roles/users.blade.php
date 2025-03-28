@extends('layouts.master')

@section('title', 'Usuarios del Rol: ' . ucfirst($role->name))

@section('header')
    <h1 class="text-center">Usuarios con el Rol: {{ ucfirst($role->name) }}</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr><th>#</th><th>Nombre</th><th>Email</th><th>Fecha de Registro</th></tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">No hay usuarios para este rol.</td></tr>
                    @endforelse
                </tbody>
            </table>

            <div class="mt-3 d-flex justify-content-center">
                {{ $users->links() }}
            </div>

            <div class="mt-3">
                <a href="{{ route('roles.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
            </div>
        </div>
    </div>
@endsection
