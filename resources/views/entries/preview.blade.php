@extends('layouts.master')

@section('title', 'Previsualización de Importación')
@section('header', 'Previsualizar Ingreso')

@section('content')

@php
    $columnNames = [
        'nombre_proveedor'   => 'Proveedor',
        'nombre_categoria'   => 'Categoría',
        'nombre_articulo'    => 'Artículo',
        'modelo'             => 'Modelo',
        'descripcion'        => 'Descripción',
        'cantidad'           => 'Cantidad',
        'costo'              => 'Precio Costo',
        'precio_mayor'       => 'Precio Mayorista',
        'precio_tienda'      => 'Precio Tienda',
        'precio_factura'     => 'Precio Factura',
        'fecha_vencimiento'  => 'Fecha Vencimiento',
        'codigo_barra'       => 'Códigos de Barras',
    ];

    $perPage = 100;
    $totalRows = count($rows);
    $totalPages = max(1, ceil($totalRows / $perPage));
    $currentPage = max(1, min(request()->get('page', 1), $totalPages));
    $offset = ($currentPage - 1) * $perPage;
    $rowsPaginated = array_slice($rows, $offset, $perPage);
@endphp

{{-- ⚠️ Máximo de filas --}}
@if($totalRows > 1000)
    <div class="alert alert-danger">
        <h5><i class="fas fa-exclamation-triangle"></i> No se puede importar más de 1000 registros</h5>
        <a href="{{ route('entries.importForm') }}" class="btn btn-secondary mt-3">Volver al formulario de importación</a>
    </div>
    @php return; @endphp
@endif

{{-- 🔴 Errores críticos --}}
@if(isset($errors) && count($errors) > 0)
    <div class="alert alert-danger">
        <h5><i class="fas fa-times-circle"></i> Errores que impiden la importación:</h5>
        <ul class="mb-0">
            @foreach($errors as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- 🟣 Advertencias --}}
@if(isset($warnings) && count($warnings) > 0)
    <div class="alert alert-warning">
        <h5><i class="fas fa-exclamation-circle"></i> Advertencias:</h5>
        <ul class="mb-0">
            @foreach($warnings as $warning)
                <li>{{ $warning }}</li>
            @endforeach
        </ul>
        <small class="text-muted">Las advertencias no impiden la importación, pero deberías revisarlas.</small>
    </div>
@endif

{{-- ✅ Información --}}
@if($totalRows > 0)
    <div class="alert alert-info mb-3">
        <i class="fas fa-eye"></i> Previsualización de registros cargados ({{ $totalRows }} filas)
    </div>

    {{-- ✅ Formulario solo si no hay errores --}}
    @if(count($errors) === 0)
        <form action="{{ route('entries.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="rows" value='@json($rows)'>
            <button type="submit" class="btn btn-success mb-3">
                <i class="fas fa-check"></i> Confirmar Importación
            </button>
            <a href="{{ route('entries.importForm') }}" class="btn btn-outline-secondary mb-3 ml-2">
                <i class="fas fa-arrow-left"></i> Volver al formulario
            </a>
        </form>
    @endif

    {{-- ✅ Tabla --}}
    <div class="table-responsive">
        <p>Mostrando <b>{{ count($rowsPaginated) }}</b> de <b>{{ $totalRows }}</b> registros (Página {{ $currentPage }} de {{ $totalPages }})</p>
        <table class="table table-bordered table-sm table-striped">
            <thead class="thead-dark">
                <tr>
                    @foreach($columnNames as $key => $label)
                        <th>{{ $label }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rowsPaginated as $row)
                    <tr>
                        @foreach(array_keys($columnNames) as $key)
                            <td>{{ $row[$key] ?? '' }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columnNames) }}" class="text-center">Sin datos para mostrar en esta página</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Paginación --}}
        <div class="d-flex justify-content-between mt-2">
            @if($currentPage > 1)
                <a href="{{ url()->current() }}?page={{ $currentPage - 1 }}" class="btn btn-outline-primary">← Anterior</a>
            @else
                <div></div>
            @endif

            @if($currentPage < $totalPages)
                <a href="{{ url()->current() }}?page={{ $currentPage + 1 }}" class="btn btn-outline-primary">Siguiente →</a>
            @endif
        </div>
    </div>
@endif

@endsection
