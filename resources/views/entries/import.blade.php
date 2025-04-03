@extends('layouts.master')

@section('title', 'Importar Ingreso desde CSV o Excel')
@section('header', 'Importar Ingreso')

@section('content')
<div class="card shadow">
    <div class="card-body">

        {{-- Mostrar errores de validación --}}
        @if($errors->any())
            <div class="alert alert-danger">
                <h5><i class="fas fa-exclamation-circle"></i> Se encontraron errores:</h5>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Formulario de importación --}}
        <form action="{{ route('entries.preview') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="form-group">
                <label for="file">Seleccionar archivo CSV o Excel <span class="text-danger">*</span></label>
                <input type="file" name="file" id="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                <small class="form-text text-muted">
                    Formatos permitidos: <strong>CSV</strong>, <strong>Excel (.xlsx, .xls)</strong>
                </small>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('entries.template') }}" class="btn btn-outline-primary">
                    <i class="fas fa-download"></i> Descargar plantilla de ejemplo
                </a>

                <button type="submit" class="btn btn-success">
                    <i class="fas fa-search"></i> Previsualizar archivo
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('custom_js')
<script>
    @if(session('success'))
        Swal.fire('Éxito', '{{ session('success') }}', 'success');
    @elseif(session('error'))
        Swal.fire('Error', '{{ session('error') }}', 'error');
    @endif
</script>
@endsection
