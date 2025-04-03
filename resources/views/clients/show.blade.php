@extends('layouts.master')

@section('title', 'Detalle del Cliente')

@section('header')
    <h1 class="text-center">Detalles del Cliente</h1>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h4 class="mb-0">Información del Cliente</h4>
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Nombre:</strong>
                <p>{{ $client->name }}</p>
            </div>
            <div class="col-md-4">
                <strong>Tipo de Documento:</strong>
                <p>{{ $client->document_type ?? 'No registrado' }}</p>
            </div>
            <div class="col-md-4">
                <strong>Nro. Documento:</strong>
                <p>{{ $client->document_number ?? 'No registrado' }}</p>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <strong>Teléfono:</strong>
                <p>{{ $client->phone ?? 'No registrado' }}</p>
            </div>
            <div class="col-md-6">
                <strong>Dirección:</strong>
                <p>{{ $client->address ?? 'No registrada' }}</p>
            </div>
        </div>

        <div class="mb-3">
            <strong>Fecha de Registro:</strong>
            <p>{{ optional($client->created_at)->format('d/m/Y H:i') ?? 'Sin fecha' }}</p>
        </div>
        
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('clients.index') }}" class="btn btn-secondary px-3">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
            @can('clientes.editar')
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-warning px-3 ms-auto">
                    <i class="fas fa-edit"></i> Editar
                </a>
            @endcan
        </div>
        
    </div>
</div>
@endsection
