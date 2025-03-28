@extends('layouts.master')

@section('title', 'Crear Ingreso')

@section('header')
    <h1 class="text-center">Crear Ingreso</h1>
@endsection

@section('content')
    <form action="{{ route('entries.store') }}" method="POST" id="entry-form">
        @csrf

        {{-- 📅 Fecha del Ingreso --}}
        <div class="form-group">
            <label for="date"><strong>Fecha</strong></label>
            <input type="text" name="date" id="date" class="form-control flatpickr" required>
        </div>

        {{-- 🏢 Nombre del Proveedor --}}
        <div class="form-group">
            <label for="supplier_name"><strong>Nombre del Proveedor</strong></label>
            <input type="text" name="supplier_name" id="supplier_name" class="form-control" placeholder="Ingrese el nombre del proveedor" required>
        </div>

        {{-- 🏷 Código de Barras --}}
        <div class="form-group">
            <label for="barcode"><strong>Escanear o Ingresar Código de Barras</strong></label>
            <input type="text" id="barcode" class="form-control" placeholder="Escanea o ingresa el código de barras aquí" autofocus>
        </div>

        {{-- 📋 Tabla de Detalles --}}
        <h4 class="mt-4">Detalles del Ingreso</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="entry-details-table">
                <thead class="thead-dark">
                    <tr>
                        <th>Artículo</th>
                        <th>Cantidad</th>
                        <th>Precio Compra</th>
                        <th>Precio Mayorista</th>
                        <th>Precio Tienda</th>
                        <th>Precio Factura</th>
                        <th>Subtotal</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Se llenará dinámicamente --}}
                </tbody>
            </table>
        </div>

        {{-- 🔢 Total del Ingreso --}}
        <div class="form-group">
            <label for="total"><strong>Total</strong></label>
            <input type="text" id="total" name="total" class="form-control text-right" readonly value="0.00">
        </div>

        {{-- 🛠 Botones --}}
        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Crear Ingreso
            </button>
            <button type="reset" class="btn btn-warning">
                <i class="fas fa-undo"></i> Cancelar
            </button>
            <a href="{{ route('entries.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </form>
@endsection

@section('custom_js')

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ✅ Flatpickr para manejar fecha de ingreso
        flatpickr("#date", {
            dateFormat: "Y-m-d",
            allowInput: true,
            defaultDate: new Date()
        });
    });

    let articles = @json($articles); // Lista de artículos disponibles
    let details = []; // Detalles dinámicos

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
            (a.model === barcode || (a.barcodes && a.barcodes.some(b => b.barcode === barcode))) 
            && a.status === 1 // Solo agregar artículos activos
        );

        if (!article) {
            Swal.fire({
                icon: 'error',
                title: 'Artículo no encontrado',
                text: 'Solo se pueden agregar artículos activos con código de barras o modelo válido.',
            });
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
                price: article.cost || 0,
                wholesale_price: article.wholesale_price || 0,
                store_price: article.store_price || 0,
                invoice_price: article.invoice_price || 0,
                subtotal: article.cost || 0,
            });
        }
        updateTable();
    }

    function updateTable() {
        let tbody = document.querySelector('#entry-details-table tbody');
        tbody.innerHTML = '';

        let total = 0;

        details.forEach((detail, index) => {
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
                        <input type="number" class="form-control" value="${detail.price}" min="0"
                            onchange="updatePrice(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${detail.wholesale_price}" min="0"
                            onchange="updateWholesalePrice(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${detail.store_price}" min="0"
                            onchange="updateStorePrice(${index}, this.value)">
                    </td>
                    <td>
                        <input type="number" class="form-control" value="${detail.invoice_price}" min="0"
                            onchange="updateInvoicePrice(${index}, this.value)">
                    </td>
                    <td class="text-right">${detail.subtotal.toFixed(2)}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm" onclick="removeDetail(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;

            tbody.insertAdjacentHTML('beforeend', row);
        });

        document.getElementById('total').value = total.toFixed(2);
    }

    function updateQuantity(index, value) {
        details[index].quantity = Math.max(parseInt(value), 1);
        updateTable();
    }

    function updatePrice(index, value) {
        details[index].price = Math.max(parseFloat(value), 0);
        updateTable();
    }

    function updateWholesalePrice(index, value) {
        details[index].wholesale_price = Math.max(parseFloat(value), 0);
        updateTable();
    }

    function updateStorePrice(index, value) {
        details[index].store_price = Math.max(parseFloat(value), 0);
        updateTable();
    }

    function updateInvoicePrice(index, value) {
        details[index].invoice_price = Math.max(parseFloat(value), 0);
        updateTable();
    }

    function removeDetail(index) {
        details.splice(index, 1);
        updateTable();
    }

    document.getElementById('entry-form').addEventListener('submit', function (e) {
        if (details.length === 0) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Debe agregar al menos un artículo para guardar el ingreso.',
            });
        } else {
            let detailsInput = document.createElement('input');
            detailsInput.type = 'hidden';
            detailsInput.name = 'details';
            detailsInput.value = JSON.stringify(details);
            this.appendChild(detailsInput);
        }
    });
</script>
@endsection
