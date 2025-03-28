@extends('layouts.master')

@section('title', 'Reporte de Ingresos')

@section('header')
    <h1 class="text-center">Reporte de Ingresos</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Generar Reporte de Ingresos</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('entries.export') }}" method="GET" class="row g-3 p-3 border rounded bg-light">
                {{-- 📅 Fecha de Inicio --}}
                <div class="col-md-4">
                    <label for="start_date"><strong>Fecha Inicio</strong></label>
                    <input type="text" name="start_date" id="start_date" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                {{-- 📅 Fecha de Fin --}}
                <div class="col-md-4">
                    <label for="end_date"><strong>Fecha Fin</strong></label>
                    <input type="text" name="end_date" id="end_date" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                {{-- 📌 Selección de Formato --}}
                <div class="col-md-3">
                    <label for="format"><strong>Formato</strong></label>
                    <select name="format" class="form-control">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>

                {{-- 📤 Botón de Exportar --}}
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom_js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr('.datepicker', {
            dateFormat: 'Y-m-d',
            enableTime: false,
            allowInput: true,
        });
    });
</script>
@endsection
