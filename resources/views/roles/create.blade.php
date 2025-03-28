@extends('layouts.master')

@section('title', isset($role) ? 'Editar Rol' : 'Crear Rol')

@section('header')
    <h1 class="text-center">{{ isset($role) ? 'Editar Rol' : 'Crear Nuevo Rol' }}</h1>
@endsection

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h3 class="card-title mb-0">{{ isset($role) ? 'Editar Rol' : 'Nuevo Rol' }}</h3>
        <a href="{{ route('roles.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card-body">
        <form action="{{ isset($role) ? route('roles.update', $role) : route('roles.store') }}" method="POST">
            @csrf
            @if(isset($role)) @method('PUT') @endif

            {{-- Nombre del Rol --}}
            <div class="mb-4">
                <label for="name" class="form-label fw-bold">Nombre del Rol</label>
                <input type="text" name="name" id="name" class="form-control"
                       value="{{ old('name', $role->name ?? '') }}" required>
                @error('name')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            {{-- Permisos agrupados --}}
            <div class="mb-4">
                <label class="form-label fw-bold">Permisos del Rol</label>

                @php
                    $grouped = $permissions->groupBy(fn($perm) => explode('.', $perm->name)[0]);
                @endphp

                @forelse($grouped as $group => $perms)
                    <div class="border rounded p-3 mb-4 bg-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="text-uppercase text-primary mb-0">{{ ucfirst($group) }}</h6>
                            <div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input group-toggle-radio" type="radio" name="group_toggle_{{ $group }}" value="all" data-group="{{ $group }}">
                                    <label class="form-check-label text-success">Todos</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input group-toggle-radio" type="radio" name="group_toggle_{{ $group }}" value="none" data-group="{{ $group }}">
                                    <label class="form-check-label text-danger">Ninguno</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            @foreach($perms as $perm)
                                <div class="col-md-4 col-lg-3 mb-2">
                                    <div class="form-check">
                                        <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                               class="form-check-input perm-checkbox perm-{{ $group }}"
                                               id="perm-{{ $perm->id }}"
                                               {{ isset($role) && $role->permissions->contains($perm->id) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="perm-{{ $perm->id }}">
                                            {{ $perm->label }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <p class="text-muted">No hay permisos disponibles.</p>
                @endforelse
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ isset($role) ? 'Actualizar Rol' : 'Crear Rol' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    document.addEventListener("DOMContentLoaded", () => {
        document.querySelectorAll('.group-toggle-radio').forEach(radio => {
            radio.addEventListener('change', function () {
                const group = this.dataset.group;
                const value = this.value;
                const checkboxes = document.querySelectorAll(`.perm-${group}`);

                checkboxes.forEach(cb => {
                    cb.checked = value === 'all';
                });
            });
        });
    });
</script>
@endsection
