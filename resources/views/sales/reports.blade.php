@extends('layouts.master')

@section('title', 'Reportes de Ventas y Cotizaciones')

@section('header')
    <h1 class="text-center">Reportes de Ventas y Cotizaciones</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Generar Reporte</h3>
        </div>
        <div class="card-body">
            {{-- 📌 Reporte de Ventas Pagadas --}}
            <h4 class="mb-3"><i class="fas fa-file-invoice-dollar"></i> Reporte de Ventas Pagadas</h4>
            <form action="{{ route('sales.exportReport') }}" method="GET" class="row gy-2 gx-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date_sales"><strong>Fecha Inicio</strong></label>
                    <input type="text" name="start_date" id="start_date_sales" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                <div class="col-md-4">
                    <label for="end_date_sales"><strong>Fecha Fin</strong></label>
                    <input type="text" name="end_date" id="end_date_sales" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                <div class="col-md-3">
                    <label for="format_sales"><strong>Formato</strong></label>
                    <select name="format" class="form-select">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>

                <div class="col-md-1 text-end">
                    <button type="submit" class="btn btn-danger w-100">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                </div>
            </form>

            <hr>

            {{-- 📌 Reporte de Ventas Pendientes --}}
            <h4 class="mb-3"><i class="fas fa-clock"></i> Reporte de Ventas Pendientes</h4>
            <form action="{{ route('sales.pending.export') }}" method="GET" class="row gy-2 gx-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date_pending"><strong>Fecha Inicio</strong></label>
                    <input type="text" name="start_date" id="start_date_pending" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                <div class="col-md-4">
                    <label for="end_date_pending"><strong>Fecha Fin</strong></label>
                    <input type="text" name="end_date" id="end_date_pending" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                <div class="col-md-3">
                    <label for="format_pending"><strong>Formato</strong></label>
                    <select name="format" class="form-select">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>

                <div class="col-md-1 text-end">
                    <button type="submit" class="btn btn-warning w-100">
                        <i class="fas fa-file-export"></i> Exportar
                    </button>
                </div>
            </form>

            <hr>

            {{-- 📌 Reporte de Cotizaciones --}}
            <h4 class="mb-3"><i class="fas fa-file-alt"></i> Reporte de Cotizaciones</h4>
            <form action="{{ route('sales.quotations.export') }}" method="GET" class="row gy-2 gx-3 align-items-end">
                <div class="col-md-4">
                    <label for="start_date_quotations"><strong>Fecha Inicio</strong></label>
                    <input type="text" name="start_date" id="start_date_quotations" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                <div class="col-md-4">
                    <label for="end_date_quotations"><strong>Fecha Fin</strong></label>
                    <input type="text" name="end_date" id="end_date_quotations" class="form-control datepicker" required placeholder="Seleccionar fecha">
                </div>

                <div class="col-md-3">
                    <label for="format_quotations"><strong>Formato</strong></label>
                    <select name="format" class="form-select">
                        <option value="pdf">PDF</option>
                        <option value="csv">CSV</option>
                    </select>
                </div>

                <div class="col-md-1 text-end">
                    <button type="submit" class="btn btn-primary w-100">
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
