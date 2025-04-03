@extends('layouts.master')

@section('title', 'Gestión de Ingresos')

@section('header')
    <h1 class="text-center">Gestión de Ingresos</h1>
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Lista de Ingresos</h3>

            @perm('ingresos.crear')
                <a href="{{ route('entries.create') }}" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nuevo Ingreso
                </a>
            @endperm
        </div>

        {{-- 📌 Formulario de Búsqueda --}}
        <div class="card-body">
            <form method="GET" action="{{ route('entries.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="supplier_name"><strong>Proveedor</strong></label>
                    <input type="text" name="supplier_name" class="form-control" value="{{ request('supplier_name') }}">
                </div>
                <div class="col-md-3">
                    <label for="start_date"><strong>Desde</strong></label>
                    <input type="text" id="start_date" name="start_date" class="form-control flatpickr" value="{{ request('start_date') }}">
                </div>
                <div class="col-md-3">
                    <label for="end_date"><strong>Hasta</strong></label>
                    <input type="text" id="end_date" name="end_date" class="form-control flatpickr" value="{{ request('end_date') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        {{-- 📌 Tabla de Ingresos --}}
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Fecha</th>
                        <th>Proveedor</th>
                        <th>Total</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entry)
                        <tr id="entry-row-{{ $entry->id }}">
                            <td>{{ $entry->id }}</td>
                            <td>{{ \Carbon\Carbon::parse($entry->date)->format('d-m-Y') }}</td>
                            <td>{{ $entry->provider->name ?? 'Sin proveedor' }}</td>
                            <td>Bs {{ number_format($entry->total, 2) }}</td>
                            <td class="text-center">
                                @perm('ingresos.ver')
                                    <a href="{{ route('entries.show', $entry->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                @endperm

                                @perm('ingresos.editar')
                                    <a href="{{ route('entries.edit', $entry) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                @endperm

                                @perm('ingresos.eliminar')
                                    <button type="button" class="btn btn-danger btn-sm delete-btn"
                                        data-id="{{ $entry->id }}" data-toggle="modal" data-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                @endperm
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No se encontraron ingresos.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- 📌 Paginación --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $entries->appends(request()->query())->links() }}
        </div>
    </div>

    {{-- ✅ Modal de Eliminación --}}
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirmar Eliminación</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    ¿Estás seguro de que deseas eliminar este ingreso? Esta acción no se puede deshacer.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <form id="deleteForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Eliminar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('custom_js')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        flatpickr(".flatpickr", { dateFormat: "Y-m-d", allowInput: true });

        let deleteForm = document.getElementById('deleteForm');
        let deleteModal = $('#deleteModal');

        deleteModal.on('show.bs.modal', function (event) {
            let button = $(event.relatedTarget);
            let entryId = button.data('id');
            deleteForm.action = "{{ url('entries') }}/" + entryId;
        });

        deleteForm.addEventListener('submit', function (event) {
            event.preventDefault();
            Swal.fire({
                title: '¿Confirmar eliminación?',
                text: "Esta acción no se puede deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6'
            }).then((result) => {
                if (result.isConfirmed) deleteForm.submit();
            });
        });

        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Éxito', text: "{{ session('success') }}" });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Error', text: "{{ session('error') }}" });
        @endif
    });
</script>
@endsection
