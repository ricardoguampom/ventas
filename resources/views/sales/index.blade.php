@extends('layouts.master')

@section('title', 'Gestión de Ventas')

@section('header')
    <h1 class="text-center">Gestión de Ventas</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h3 class="card-title mb-2">Listado de Ventas y Cotizaciones</h3>
            @can('ventas.crear')
                <a href="{{ route('sales.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Registrar Nueva Venta
                </a>
            @endcan
        </div>

        <div class="card-body">
            @include('sweetalert::alert')

            {{-- Filtros --}}
            <form method="GET" action="{{ route('sales.index') }}" class="mb-3">
            <div class="d-flex flex-wrap align-items-end gap-2">
                    <div class="flex-grow-1">
                        <label for="start_date"><strong>Fecha Inicio</strong></label>
                        <input type="text" name="start_date" id="start_date" class="form-control datepicker" value="{{ request('start_date') }}" placeholder="Seleccionar fecha">
                    </div>
                
                    <div class="flex-grow-1">
                        <label for="end_date"><strong>Fecha Fin</strong></label>
                        <input type="text" name="end_date" id="end_date" class="form-control datepicker" value="{{ request('end_date') }}" placeholder="Seleccionar fecha">
                    </div>
                
                    <div class="flex-grow-1">
                        <label for="customer_name"><strong>Nombre del Cliente</strong></label>
                        <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Buscar por cliente..." value="{{ request('customer_name') }}">
                    </div>
                
                    <div class="flex-grow-1">
                        <label for="invoice_number"><strong>Número de Factura</strong></label>
                        <input type="text" name="invoice_number" id="invoice_number" class="form-control" placeholder="Buscar por factura..." value="{{ request('invoice_number') }}">
                    </div>
                
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar Resultados
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Fecha y Hora</th>
                            <th>Cliente</th>
                            <th>Factura</th>
                            <th>Total (Bs)</th>
                            <th>Transacción</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                            <tr id="sale-row-{{ $sale->id }}">
                                <td>{{ $sale->id }}</td>
                                <td>{{ $sale->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>{{ $sale->client->name ?? 'Sin cliente' }}</td>
                                <td>{{ $sale->invoice_number }}</td>
                                <td>Bs {{ number_format($sale->total, 2) }}</td>
                                <td>
                                    <span class="badge {{ $sale->is_quotation ? 'badge-info' : 'badge-primary' }}">
                                        {{ $sale->is_quotation ? 'Cotización' : 'Venta' }}
                                    </span>
                                </td>
                                <td>
                                    @if(!$sale->is_quotation)
                                        <div class="btn-group status-form" data-sale-id="{{ $sale->id }}">
                                            <label class="btn btn-sm {{ $sale->status == 'pending' ? 'btn-warning active' : 'btn-outline-warning' }}">
                                                <input type="radio" name="status-{{ $sale->id }}" value="pending" {{ $sale->status == 'pending' ? 'checked' : '' }}> Pendiente
                                            </label>
                                            <label class="btn btn-sm {{ $sale->status == 'paid' ? 'btn-success active' : 'btn-outline-success' }}">
                                                <input type="radio" name="status-{{ $sale->id }}" value="paid" {{ $sale->status == 'paid' ? 'checked' : '' }}> Pagado
                                            </label>
                                        </div>
                                    @else
                                        @can('ventas.crear')
                                            <button class="btn btn-success convert-to-sale-btn" data-sale-id="{{ $sale->id }}">
                                                <i class="fas fa-exchange-alt"></i> Convertir a Venta
                                            </button>
                                        @endcan
                                    @endif
                                </td>
                                <td>
                                    @can('ventas.ver')
                                        <a href="{{ route('sales.show', $sale) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endcan

                                    @can('ventas.editar')
                                        <a href="{{ route('sales.edit', $sale) }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endcan

                                    @can('ventas.eliminar')
                                        <button class="btn btn-danger btn-sm delete-sale-btn" data-sale-id="{{ $sale->id }}">
                                            <i class="fas fa-trash"></i> Eliminar
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No se encontraron resultados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $sales->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
         // Inicializar Flatpickr en los campos de fecha
         flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            enableTime: false, // Deshabilita la selección de hora
            allowInput: true,  // Permite que el usuario escriba la fecha manualmente
            locale: "es",      // Establece el idioma en español (si lo necesitas)
        });
        if (localStorage.getItem("saleSuccess")) {
            Swal.fire('✅ Éxito', localStorage.getItem("saleSuccess"), 'success');
            localStorage.removeItem("saleSuccess"); // 🔥 Borrar el mensaje después de mostrarlo
        }
    
        // ✅ Manejo de conversión de cotización a venta
        let convertButtons = document.querySelectorAll('.convert-to-sale-btn');
    
        if (convertButtons.length === 0) {
            console.warn("⚠️ No se encontraron botones para convertir cotización a venta.");
        } else {
            convertButtons.forEach(button => {
                button.addEventListener('click', function () {
                    let saleId = this.dataset.saleId;
    
                    Swal.fire({
                        title: "¿Confirmar conversión?",
                        text: "¿Deseas convertir esta cotización en una venta?",
                        icon: "question",
                        showCancelButton: true,
                        confirmButtonText: "Sí, convertir",
                        cancelButtonText: "Cancelar",
                        confirmButtonColor: "#28a745",
                        cancelButtonColor: "#d33"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetch(`/sales/${saleId}/convert-to-sale`, {
                                method: "POST",
                                headers: {
                                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content"),
                                    "Content-Type": "application/json"
                                }
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire("✅ Éxito", data.message, "success").then(() => location.reload());
                                } else {
                                    Swal.fire("❌ Error", data.error || "Ocurrió un error inesperado.", "error");
                                }
                            })
                            .catch(error => {
                                Swal.fire("❌ Error", "Error inesperado en la conexión.", "error");
                                console.error("Error en la conversión:", error);
                            });
                        }
                    });
                });
            });
        }
    
        // ✅ Manejo del cambio de estado con confirmación SweetAlert
        document.querySelectorAll('.status-form input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function () {
                let saleId = this.closest('.status-form').dataset.saleId;
                let newStatus = this.value;
    
                Swal.fire({
                    title: '¿Cambiar estado de la venta?',
                    text: `Se cambiará a "${newStatus === 'paid' ? 'Pagado' : 'Pendiente'}".`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/sales/${saleId}/update-status`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ status: newStatus })
                        }).then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire("✅ Éxito", data.message, "success").then(() => location.reload());
                            } else {
                                Swal.fire("❌ Error", data.error || "Ocurrió un error inesperado.", "error");
                            }
                        }).catch(error => {
                            Swal.fire("❌ Error", "Error en la conexión.", "error");
                            console.error("Error:", error);
                        });
                    }
                });
            });
        });
    
        // ✅ Manejo de eliminación de ventas
        document.querySelectorAll('.delete-sale-btn').forEach(button => {
            button.addEventListener('click', function () {
                let saleId = this.dataset.saleId;
                Swal.fire({
                    title: '¿Eliminar esta venta?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/sales/${saleId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        }).then(() => location.reload());
                    }
                });
            });
        });
    });
    
</script>
@endsection
