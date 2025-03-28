@extends('layouts.master')

@section('title', 'Crear Venta')

@section('header')
    <h1 class="text-center">Registrar Nueva Venta</h1>
@endsection

@section('content')
    {{-- 🔥 Mensajes de error con SweetAlert --}}
    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let errorMessage = "";
                @foreach($errors->all() as $error)
                    errorMessage += "{{ $error }}\n";
                @endforeach
                Swal.fire('Error', errorMessage, 'error');
            });
        </script>
    @endif

    {{-- 📌 Formulario de venta --}}
    <form action="{{ route('sales.store') }}" method="POST" id="sale-form">
        @csrf

        {{-- Cliente --}}
        <div class="form-group">
            <label for="customer_name"><strong>Nombre del Cliente</strong></label>
            <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Ingrese el nombre del cliente" required>
        </div>

        {{-- Número de Factura --}}
        <div class="form-group">
            <label for="invoice_number"><strong>Número de Factura</strong></label>
            <input type="text" name="invoice_number" id="invoice_number" class="form-control" value="{{ $invoiceNumber }}" readonly>
        </div>

        {{-- Código de Barras --}}
        <div class="form-group">
            <label for="barcode"><strong>Escanear o Ingresar Código de Barras</strong></label>
            <input type="text" id="barcode" class="form-control" placeholder="Escanea el código de barras aquí" autofocus>
        </div>

        {{-- 📌 Tabla de Detalles de la Venta --}}
        <h4 class="mt-4"><i class="fas fa-list"></i> Detalles de la Venta</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="sale-details-table">
                <thead class="thead-dark">
                    <tr>
                        <th>Artículo</th>
                        <th class="text-center">Cantidad</th>
                        <th>Seleccionar Precio</th>
                        <th class="text-right">Precio Unitario</th>
                        <th class="text-right">Subtotal</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Se llenarán dinámicamente los detalles --}}
                </tbody>
            </table>
        </div>

        {{-- Total --}}
        <div class="form-group mt-4">
            <label for="total"><strong>Total</strong></label>
            <input type="text" id="total" name="total" class="form-control text-right font-weight-bold" readonly value="0.00">
        </div>

        {{-- Botones --}}
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Registrar Venta
            </button>
            <a href="{{ route('sales.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </form>
@endsection

@section('custom_js')
<script>
    let articles = @json($articles);
    let details = [];

    document.getElementById('barcode').addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let barcode = e.target.value.trim();
            if (barcode) {
                addOrIncrementArticle(barcode);
                e.target.value = '';
            }
        }
    });

    function addOrIncrementArticle(barcode) {
        let article = articles.find(a => 
            a.model === barcode || 
            (a.barcodes && a.barcodes.some(b => b.barcode === barcode))
        );

        if (!article) {
            Swal.fire('❌ Error', '¡Artículo no encontrado!', 'error');
            return;
        }

        if (article.status === 0) {
            Swal.fire('⛔ Artículo inactivo', 'Este artículo está inactivo y no se puede vender.', 'warning');
            return;
        }

        if (article.stock === 0) {
            Swal.fire('⚠️ Sin stock', 'Este artículo no tiene stock disponible.', 'warning');
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
                            <option value="${parseFloat(detail.prices.storePrice)}" ${detail.price == detail.prices.storePrice ? 'selected' : ''}>
                                Tienda: Bs ${parseFloat(detail.prices.storePrice).toFixed(2)}
                            </option>
                            <option value="${parseFloat(detail.prices.wholesalePrice)}" ${detail.price == detail.prices.wholesalePrice ? 'selected' : ''}>
                                Mayorista: Bs ${parseFloat(detail.prices.wholesalePrice).toFixed(2)}
                            </option>
                            <option value="${parseFloat(detail.prices.invoicePrice)}" ${detail.price == detail.prices.invoicePrice ? 'selected' : ''}>
                                Factura: Bs ${parseFloat(detail.prices.invoicePrice).toFixed(2)}
                            </option>
                        </select>
                    </td>
                    <td>
                        <input type="number" class="form-control text-right" value="${detail.price}" min="0" step="0.01"
                            onchange="updatePrice(${index}, this.value)">
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
    

    function updateQuantity(index, value) {
        let newQuantity = parseInt(value) || 1;
        details[index].quantity = newQuantity;
        updateTable();
    }

    function changePrice(index, value) {
        details[index].price = parseFloat(value) || 0;
        updateTable();
    }

    function updatePrice(index, value) {
        details[index].price = parseFloat(value) || 0;
        updateTable();
    }

    function removeDetail(index) {
        details.splice(index, 1);
        updateTable();
    }

    document.getElementById('sale-form').addEventListener('submit', function (e) {
        if (details.length === 0) {
            e.preventDefault();
            Swal.fire('❌ Error', 'Debe agregar al menos un artículo a la venta.', 'error');
            return;
        }
        // 🔹 Guardamos el mensaje en localStorage
        localStorage.setItem("saleSuccess", "¡Venta creada con éxito!");

        let detailsInput = document.createElement('input');
        detailsInput.type = 'hidden';
        detailsInput.name = 'details';
        detailsInput.value = JSON.stringify(details);
        this.appendChild(detailsInput);
    });

    document.addEventListener('DOMContentLoaded', function () {
        @if(session('error'))
            Swal.fire('❌ Error', "{{ session('error') }}", 'error');
        @endif

        @if(session('success'))
            Swal.fire('✅ Éxito', "{{ session('success') }}", 'success');
        @endif
    });
</script>

@endsection
