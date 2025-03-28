@extends('layouts.master')

@section('title', 'Detalles del Ingreso')

@section('header')
    <h1 class="text-center">Detalles del Ingreso</h1>
@endsection

@section('content')

    {{-- 📌 Información del Ingreso --}}
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h3 class="card-title"><i class="fas fa-receipt"></i> Información del Ingreso</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong><i class="fas fa-calendar-alt"></i> Fecha:</strong> 
                        {{ \Carbon\Carbon::parse($entry->date)->format('d-m-Y') }}
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong><i class="fas fa-truck"></i> Proveedor:</strong> 
                        {{ $entry->supplier_name ?? 'N/A' }}
                    </p>
                </div>
                <div class="col-md-4">
                    <p><strong><i class="fas fa-dollar-sign"></i> Total:</strong> 
                        <span class="text-success font-weight-bold">Bs {{ number_format($entry->total, 2) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 📌 Detalles del Ingreso --}}
    <div class="card mt-3">
        <div class="card-header bg-dark text-white">
            <h3 class="card-title"><i class="fas fa-boxes"></i> Detalles del Ingreso</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Artículo</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-right">Precio Compra</th>
                            <th class="text-right">Precio Mayorista</th>
                            <th class="text-right">Precio Tienda</th>
                            <th class="text-right">Precio Factura</th>
                            <th class="text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entry->details as $detail)
                            <tr class="{{ $detail->article->stock == 0 ? 'table-warning' : '' }}">
                                <td>
                                    <strong>{{ $detail->article->name }}</strong>
                                    @if($detail->article->stock == 0)
                                        <span class="badge badge-danger"><i class="fas fa-exclamation-circle"></i> Sin Stock</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-right">Bs {{ number_format($detail->price, 2) }}</td>
                                <td class="text-right">Bs {{ number_format($detail->wholesale_price, 2) }}</td>
                                <td class="text-right">Bs {{ number_format($detail->store_price, 2) }}</td>
                                <td class="text-right">Bs {{ number_format($detail->invoice_price, 2) }}</td>
                                <td class="text-right font-weight-bold">Bs {{ number_format($detail->quantity * $detail->price, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-dark text-white">
                        <tr>
                            <th colspan="6" class="text-right">Total General:</th>
                            <th class="text-right font-weight-bold">
                                Bs {{ number_format($entry->details->sum(fn($d) => $d->quantity * $d->price), 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- 📌 Botón Volver --}}
            <div class="text-center mt-4">
                <a href="{{ route('entries.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

@endsection
