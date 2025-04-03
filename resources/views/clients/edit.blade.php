@extends('layouts.master')

@section('title', isset($client) ? 'Editar Cliente' : 'Registrar Cliente')

@section('header')
    <h1 class="text-center">{{ isset($client) ? 'Editar Cliente' : 'Registrar Cliente' }}</h1>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ isset($client) ? route('clients.update', $client) : route('clients.store') }}" method="POST">
            @csrf
            @if(isset($client))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control form-control-sm" value="{{ old('name', $client->name ?? '') }}" required placeholder="Ej: Juan Pérez">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="document_type" class="form-label">Tipo de Documento</label>
                    <select name="document_type" id="document_type" class="form-select form-select-sm select2">
                        <option value="" disabled {{ old('document_type', $client->document_type ?? '') ? '' : 'selected' }}>Seleccione tipo</option>
                        <option value="CI" {{ old('document_type', $client->document_type ?? '') == 'CI' ? 'selected' : '' }}>🪪 Carnet de Identidad</option>
                        <option value="NIT" {{ old('document_type', $client->document_type ?? '') == 'NIT' ? 'selected' : '' }}>💼 NIT</option>
                        <option value="PASAPORTE" {{ old('document_type', $client->document_type ?? '') == 'PASAPORTE' ? 'selected' : '' }}>🛂 Pasaporte</option>
                        <option value="OTRO" {{ old('document_type', $client->document_type ?? '') == 'OTRO' ? 'selected' : '' }}>📄 Otro</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="document_number" class="form-label">Número de Documento</label>
                    <input type="text" name="document_number" id="document_number" class="form-control form-control-sm" value="{{ old('document_number', $client->document_number ?? '') }}" placeholder="Ej: 12345678">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Teléfono/Celular</label>
                    <input type="text" name="phone" id="phone" class="form-control form-control-sm" value="{{ old('phone', $client->phone ?? '') }}" placeholder="Ej: 78965412">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control form-control-sm" value="{{ old('email', $client->email ?? '') }}" placeholder="cliente@ejemplo.com">
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Dirección</label>
                <textarea name="address" id="address" class="form-control form-control-sm" rows="2" placeholder="Ej: Av. Siempre Viva #742">{{ old('address', $client->address ?? '') }}</textarea>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> {{ isset($client) ? 'Actualizar' : 'Registrar' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    $(function(){
        $('#document_type').select2({
            placeholder: 'Seleccione tipo de documento',
            allowClear: true,
            width: '100%'
        });
    });
</script>
@endsection
