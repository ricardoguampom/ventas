@extends('layouts.master')

@section('title', 'Detalles de la Venta')

@section('header')
    <h1 class="text-center">Detalles de la Venta</h1>
@endsection

@section('content')
@can('ventas.ver')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
            <h3 class="card-title"><i class="fas fa-receipt"></i> Factura: {{ $sale->invoice_number }}</h3>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>

        <div class="card-body">
            {{-- Información del Cliente --}}
            <h4 class="mb-3 text-primary"><i class="fas fa-user"></i> Información del Cliente</h4>
            <div class="row">
                <div class="col-md-4"><strong>Cliente:</strong> {{ $sale->customer_name }}</div>
                <div class="col-md-4"><strong>Fecha:</strong> {{ $sale->created_at->format('d/m/Y H:i') }}</div>
                <div class="col-md-4">
                    <strong>Total:</strong> <span class="text-success font-weight-bold">Bs {{ number_format($sale->total, 2) }}</span>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-md-4">
                    <strong>Estado:</strong> 
                    <span class="badge {{ $sale->status == 'paid' ? 'badge-success' : ($sale->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                        {{ $sale->status == 'paid' ? 'Pagado' : ($sale->status == 'pending' ? 'Pendiente' : 'Cancelado') }}
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>Tipo de Transacción:</strong>
                    <span class="badge {{ $sale->is_quotation ? 'badge-info' : 'badge-primary' }}">
                        {{ $sale->is_quotation ? 'Cotización' : 'Venta' }}
                    </span>
                </div>
            </div>

            {{-- Detalles de la Venta --}}
            <h4 class="mt-4 mb-3 text-primary"><i class="fas fa-shopping-cart"></i> Detalles de la Venta</h4>
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Artículo</th>
                            <th>Descripción</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-right">Precio Unitario</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->details as $detail)
                            <tr>
                                <td>{{ $detail->article->name }}</td>
                                <td>{{ $detail->article->description ?? 'Sin descripción' }}</td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-right">Bs {{ number_format($detail->price, 2) }}</td>
                                <td class="text-right font-weight-bold">Bs {{ number_format($detail->quantity * $detail->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-dark text-white">
                        <tr>
                            <th colspan="4" class="text-right">Total General:</th>
                            <th class="text-right">Bs {{ number_format($sale->total, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Botones de Acción --}}
            <div class="mt-4 text-center">
                @can('ventas.reporte')
                    <a href="{{ route('sales.export.pdf', $sale) }}" class="btn btn-danger">
                        <i class="fas fa-file-pdf"></i> {{ $sale->is_quotation ? 'Exportar Cotización' : 'Exportar Venta' }}
                    </a>
                @endcan

                @can('ventas.editar')
                    <a href="{{ route('sales.edit', $sale) }}" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                @endcan

                @can('ventas.eliminar')
                    @if($sale->status !== 'cancelled')
                        <form action="{{ route('sales.destroy', $sale) }}" method="POST" class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de que deseas cancelar esta venta?');">
                                <i class="fas fa-times"></i> {{ $sale->is_quotation ? 'Cancelar Cotización' : 'Cancelar Venta' }}
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>
@else
    @include('errors.403')
@endcan
@endsection
