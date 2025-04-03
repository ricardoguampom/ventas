@extends('layouts.master')

@section('title', 'Detalle del Proveedor')

@section('header')
    <h1 class="text-center">Detalles del Proveedor</h1>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Información del Proveedor</h4>
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Nombre o Razón Social:</strong>
                <p>{{ $provider->name }}</p>
            </div>
            <div class="col-md-4">
                <strong>Tipo de Documento:</strong>
                <p>{{ $provider->document_type ?? 'No registrado' }}</p>
            </div>
            <div class="col-md-4">
                <strong>Nro. Documento:</strong>
                <p>{{ $provider->document_number ?? 'No registrado' }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Teléfono:</strong>
                <p>{{ $provider->phone ?? 'No registrado' }}</p>
            </div>
            <div class="col-md-6">
                <strong>Correo Electrónico:</strong>
                <p>{{ $provider->email ?? 'No registrado' }}</p>
            </div>
        </div>

        <div class="mb-3">
            <strong>Dirección:</strong>
            <p>{{ $provider->address ?? 'No registrada' }}</p>
        </div>

        <div class="mb-3">
            <strong>Fecha de Registro:</strong>
            <p>{{ optional($provider->created_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
        </div>

        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('providers.index') }}" class="btn btn-secondary px-3">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            @can('proveedores.editar')
                <a href="{{ route('providers.edit', $provider) }}" class="btn btn-warning px-3 ms-auto">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endcan
        </div>
        
    </div>
</div>
@endsection
