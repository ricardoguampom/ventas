@extends('layouts.master')

@section('title', 'Editar Venta')

@section('header')
    <h1 class="text-center">Editar Venta</h1>
@endsection

@section('content')
    {{-- Mostrar mensajes de éxito y error --}}
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales.update', $sale) }}" method="POST" id="sale-form">
        @csrf
        @method('PUT')

        {{-- Cliente --}}
        <div class="form-group">
            <label for="customer_name"><strong>Nombre del Cliente</strong></label>
            <input type="text" name="customer_name" id="customer_name" class="form-control" value="{{ $sale->customer_name }}" required>
        </div>

        {{-- Número de Factura --}}
        <div class="form-group">
            <label for="invoice_number"><strong>Número de Factura</strong></label>
            <input type="text" name="invoice_number" id="invoice_number" class="form-control" value="{{ $sale->invoice_number }}" required>
        </div>

        {{-- Código de Barras --}}
        <div class="form-group">
            <label for="barcode"><strong>Escanear o Ingresar Código de Barras</strong></label>
            <input type="text" id="barcode" class="form-control" placeholder="Escanea el código de barras aquí" autofocus>
        </div>

        {{-- Tabla de detalles --}}
        <h4 class="mt-4"><strong>Detalles de la Venta</strong></h4>
        <table class="table table-bordered" id="sale-details-table">
            <thead class="thead-dark">
                <tr>
                    <th>Artículo</th>
                    <th>Cantidad</th>
                    <th>Seleccionar Precio</th>
                    <th>Precio</th>
                    <th>Subtotal</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                {{-- Se llenarán dinámicamente los detalles --}}
            </tbody>
        </table>

        {{-- Total --}}
        <div class="form-group">
            <label for="total"><strong>Total</strong></label>
            <input type="text" id="total" name="total" class="form-control" readonly value="{{ $sale->total }}">
        </div>

        {{-- Botones --}}
        <div class="d-flex justify-content-between mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Actualizar Venta
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </form>
@endsection

@section('custom_js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Mensajes de éxito/desaparecen después de 3s
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 3000);
            });
        });

        let articles = @json($articles); // Lista de artículos
        let details = @json($saleDetails); // Detalles de la venta
        let isQuotation = {{ $sale->is_quotation ? 'true' : 'false' }}; // Si es cotización

        // Captura de código de barras
        document.getElementById('barcode').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                let barcode = e.target.value.trim();
                addOrIncrementArticle(barcode);
                e.target.value = '';
            }
        });

        function addOrIncrementArticle(barcode) {
            let article = articles.find(a => 
                a.model === barcode || 
                (a.barcodes && a.barcodes.some(b => b.barcode === barcode))
            );

            if (!article) {
                alert('¡Artículo no encontrado!');
                return;
            }

            // Validar stock si no es cotización
            if (!isQuotation && article.stock <= 0) {
                alert(`El artículo "${article.name}" no tiene suficiente stock.`);
                return;
            }

            let existing = details.find(d => d.article_id === article.id);

            if (existing) {
                existing.quantity++;
            } else {
                details.push({
                    article_id: article.id,
                    name: article.name,
                    quantity: 1,
                    price: parseFloat(article.store_price) || 0,
                    subtotal: parseFloat(article.store_price) || 0,
                    prices: {
                        storePrice: parseFloat(article.store_price) || 0,
                        wholesalePrice: parseFloat(article.wholesale_price) || 0,
                        invoicePrice: parseFloat(article.invoice_price) || 0
                    }
                });
            }
            updateTable();
        }

        function updateTable() {
            let tbody = document.querySelector('#sale-details-table tbody');
            tbody.innerHTML = '';
        
            let total = 0;
        
            details.forEach((detail, index) => {
                detail.price = parseFloat(detail.price) || 0;
                detail.subtotal = detail.quantity * detail.price;
                total += detail.subtotal;
        
                let row = `
                    <tr>
                        <td>${detail.name}</td>
                        <td>
                            <input type="number" class="form-control" value="${detail.quantity}" min="1"
                                onchange="updateQuantity(${index}, this.value)">
                        </td>
                        <td>
                            <select class="form-control" onchange="changePrice(${index}, this.value)">
                                <option value="${detail.prices.storePrice}" ${detail.price == detail.prices.storePrice ? 'selected' : ''}>Tienda: Bs ${(detail.prices.storePrice).toFixed(2)}</option>
                                <option value="${detail.prices.wholesalePrice}" ${detail.price == detail.prices.wholesalePrice ? 'selected' : ''}>Mayorista: Bs ${(detail.prices.wholesalePrice).toFixed(2)}</option>
                                <option value="${detail.prices.invoicePrice}" ${detail.price == detail.prices.invoicePrice ? 'selected' : ''}>Factura: Bs ${(detail.prices.invoicePrice).toFixed(2)}</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" class="form-control text-right" value="${detail.price.toFixed(2)}" min="0" step="0.01"
                                onchange="updatePriceManual(${index}, this.value)">
                        </td>
                        <td class="text-right">Bs ${detail.subtotal.toFixed(2)}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm" onclick="removeDetail(${index})">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </td>
                    </tr>
                `;
        
                tbody.insertAdjacentHTML('beforeend', row);
            });
        
            document.getElementById('total').value = total.toFixed(2);
        }
        
        function updatePriceManual(index, value) {
            details[index].price = parseFloat(value) || 0;
            updateTable();
        }
        

        function updateQuantity(index, value) {
            details[index].quantity = Number(value);
            updateTable();
        }

        function changePrice(index, value) {
            details[index].price = parseFloat(value);
            updateTable();
        }

        function removeDetail(index) {
            details.splice(index, 1);
            updateTable();
        }

        document.getElementById('sale-form').addEventListener('submit', function () {
            let detailsInput = document.createElement('input');
            detailsInput.type = 'hidden';
            detailsInput.name = 'details';
            detailsInput.value = JSON.stringify(details);
            this.appendChild(detailsInput);
        });

        updateTable();
    </script>
@endsection
