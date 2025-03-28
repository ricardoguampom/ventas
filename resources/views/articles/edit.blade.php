@extends('layouts.master')

@section('title', 'Editar Artículo')

@section('header')
    <h1 class="text-center">Editar Artículo: {{ $article->name }}</h1>
@endsection

@section('content')
    <div class="card shadow-lg">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('articles.update', $article) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">
                    {{-- Categoría --}}
                    <div class="col-md-6 form-group">
                        <label for="category_id"><strong>Categoría del Artículo</strong></label>
                        <select name="category_id" id="category_id" class="form-control" required>
                            <option value="" disabled>Seleccione una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $article->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nombre --}}
                    <div class="col-md-6 form-group">
                        <label for="name"><strong>Nombre del Artículo</strong></label>
                        <input type="text" name="name" id="name" class="form-control"
                               placeholder="Ej. Router AC1200" value="{{ old('name', $article->name) }}" required>
                    </div>

                    {{-- Modelo --}}
                    <div class="col-md-6 form-group">
                        <label for="model"><strong>Modelo del Artículo</strong></label>
                        <input type="text" name="model" id="model" class="form-control"
                               placeholder="Ej. AC1200" value="{{ old('model', $article->model) }}">
                    </div>

                    {{-- Descripción --}}
                    <div class="col-md-6 form-group">
                        <label for="description"><strong>Descripción del Artículo</strong></label>
                        <textarea name="description" id="description" class="form-control"
                                  placeholder="Ingrese una breve descripción...">{{ old('description', $article->description) }}</textarea>
                    </div>

                    {{-- Fecha de expiración con Flatpickr --}}
                    <div class="col-md-6 form-group">
                        <label for="expiration_date"><strong>Fecha de Expiración</strong></label>
                        <input type="text" name="expiration_date" id="expiration_date" class="form-control flatpickr"
                               value="{{ old('expiration_date', $article->expiration_date) }}" placeholder="Seleccione la fecha">
                    </div>

                    {{-- Stock --}}
                    <div class="col-md-6 form-group">
                        <label for="stock"><strong>Stock del Artículo</strong></label>
                        <input type="number" name="stock" id="stock" class="form-control" min="0"
                               placeholder="Ingrese la cantidad en stock" value="{{ old('stock', $article->stock) }}" required>
                    </div>

                    {{-- Códigos de Barras Dinámicos --}}
                    <div class="col-md-6 form-group">
                        <label for="barcodes"><strong>Códigos de Barras</strong></label>
                        <div id="barcode-container">
                            @foreach(old('barcodes', $article->barcodes->pluck('barcode')->toArray()) as $barcode)
                                <div class="d-flex mt-2 barcode-group">
                                    <input type="text" name="barcodes[]" class="form-control barcode-input"
                                           placeholder="Ingrese un código de barras" value="{{ $barcode }}">
                                    <button type="button" class="btn btn-danger ml-2 remove-barcode">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-success mt-2" id="add-barcode">
                            <i class="fas fa-plus"></i> Agregar Código
                        </button>
                    </div>

                    {{-- Imagen con Previsualización --}}
                    <div class="col-md-6 form-group">
                        <label for="image"><strong>Imagen del Artículo</strong></label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*"
                               onchange="previewImage(event)">
                        <small class="form-text text-muted">Opcional: Puede cargar una nueva imagen.</small>
                        <br>
                        <img id="imagePreview" src="{{ $article->image ? asset('storage/' . $article->image) : '' }}"
                             alt="Previsualización de Imagen" class="img-thumbnail mt-2"
                             style="max-width: 200px; {{ $article->image ? '' : 'display: none;' }}">
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-6 form-group">
                        <label for="status"><strong>Estado del Artículo</strong></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="1" {{ old('status', $article->status) == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status', $article->status) == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Actualizar Artículo
                    </button>
                    <a href="{{ route('articles.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom_js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // ✅ Initialize Flatpickr
        flatpickr(".flatpickr", {
            enableTime: false,
            dateFormat: "Y-m-d",
            locale: "es"
        });

        const barcodeContainer = document.getElementById('barcode-container');
        const addBarcodeButton = document.getElementById('add-barcode');

        // ✅ Fix: Remove previous event listeners before adding a new one
        addBarcodeButton.removeEventListener('click', addBarcode);
        addBarcodeButton.addEventListener('click', addBarcode);

        function addBarcode(event) {
            event.preventDefault();

            const newInput = document.createElement('div');
            newInput.classList.add('d-flex', 'mt-2', 'barcode-group');

            newInput.innerHTML = `
                <input type="text" name="barcodes[]" class="form-control barcode-input" placeholder="Ingrese un código de barras">
                <button type="button" class="btn btn-danger ml-2 remove-barcode">
                    <i class="fas fa-minus"></i>
                </button>
            `;

            barcodeContainer.appendChild(newInput);
        }

        // ✅ Fix: Ensure barcode removal works properly
        barcodeContainer.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-barcode') || event.target.closest('.remove-barcode')) {
                event.target.closest('.barcode-group').remove();
            }
        });
    });

    // ✅ Function to Preview Uploaded Image
    function previewImage(event) {
        const input = event.target;
        const reader = new FileReader();

        reader.onload = function () {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.src = reader.result;
            imagePreview.style.display = 'block';
        };

        if (input.files && input.files[0]) {
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endsection
