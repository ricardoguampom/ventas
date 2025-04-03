@extends('layouts.master')

@section('title', 'Editar Proveedor')

@section('header')
    <h1 class="text-center">Editar Proveedor</h1>
@endsection

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <form action="{{ route('providers.update', $provider) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nombre o Razón Social <span class="text-danger">*</span></label>
                <input type="text" name="name" id="name" class="form-control form-control-sm" value="{{ old('name', $provider->name) }}" required placeholder="Ej: Proveedor S.A.">
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="document_type" class="form-label">Tipo de Documento</label>
                    <select name="document_type" id="document_type" class="form-select form-select-sm select2">
                        <option value="" disabled {{ old('document_type', $provider->document_type) ? '' : 'selected' }}>Seleccione tipo</option>
                        <option value="NIT" {{ old('document_type', $provider->document_type) == 'NIT' ? 'selected' : '' }}>💼 NIT</option>
                        <option value="CI" {{ old('document_type', $provider->document_type) == 'CI' ? 'selected' : '' }}>🪪 Carnet de Identidad</option>
                        <option value="OTRO" {{ old('document_type', $provider->document_type) == 'OTRO' ? 'selected' : '' }}>📄 Otro</option>
                    </select>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="document_number" class="form-label">Número de Documento</label>
                    <input type="text" name="document_number" id="document_number" class="form-control form-control-sm" value="{{ old('document_number', $provider->document_number) }}" placeholder="Ej: 12345678">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">Teléfono/Celular</label>
                    <input type="text" name="phone" id="phone" class="form-control form-control-sm" value="{{ old('phone', $provider->phone) }}" placeholder="Ej: 78965412">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control form-control-sm" value="{{ old('email', $provider->email) }}" placeholder="proveedor@ejemplo.com">
                </div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Dirección</label>
                <textarea name="address" id="address" class="form-control form-control-sm" rows="2" placeholder="Ej: Av. Siempre Viva #742">{{ old('address', $provider->address) }}</textarea>
            </div>

            <div class="d-flex justify-content-between gap-3">
                <a href="{{ route('providers.index') }}" class="btn btn-secondary px-3">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
                <button type="submit" class="btn btn-success px-3">
                    <i class="fas fa-save"></i> Actualizar Proveedor
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
