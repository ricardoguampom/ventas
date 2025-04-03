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
                        <select name="category_id" id="category_id" class="form-control select2" required>
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
                        <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $article->name) }}" required>
                    </div>

                    {{-- Modelo --}}
                    <div class="col-md-6 form-group">
                        <label for="model"><strong>Modelo del Artículo</strong></label>
                        <input type="text" name="model" id="model" class="form-control" value="{{ old('model', $article->model) }}">
                    </div>

                    {{-- Descripción --}}
                    <div class="col-md-6 form-group">
                        <label for="description"><strong>Descripción del Artículo</strong></label>
                        <textarea name="description" id="description" class="form-control">{{ old('description', $article->description) }}</textarea>
                    </div>

                    {{-- Fecha de expiración --}}
                    <div class="col-md-6 form-group">
                        <label for="expiration_date"><strong>Fecha de Expiración</strong></label>
                        <input type="text" name="expiration_date" id="expiration_date" class="form-control flatpickr"
                               value="{{ old('expiration_date', $article->expiration_date) }}" placeholder="Seleccione la fecha">
                    </div>

                    {{-- Stock --}}
                    <div class="col-md-6 form-group">
                        <label for="stock"><strong>Stock</strong></label>
                        <input type="number" name="stock" id="stock" class="form-control" min="0"
                               value="{{ old('stock', $article->stock) }}" required>
                    </div>

                    {{-- Códigos de Barras --}}
                    <div class="col-md-6 form-group">
                        <label><strong>Códigos de Barras</strong></label>
                        <div id="barcode-container">
                            @foreach(old('barcodes', $article->barcodes->pluck('barcode')->toArray()) as $barcode)
                                <div class="d-flex mt-2 barcode-group">
                                    <input type="text" name="barcodes[]" class="form-control barcode-input" value="{{ $barcode }}">
                                    <button type="button" class="btn btn-danger ml-2 remove-barcode"><i class="fas fa-minus"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-success mt-2" id="add-barcode">
                            <i class="fas fa-plus"></i> Agregar Código
                        </button>
                    </div>

                    {{-- Imagen --}}
                    <div class="col-md-6 form-group">
                        <label for="image"><strong>Imagen</strong></label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                        <small>Opcional: Puede cargar una nueva imagen.</small><br>
                        <img id="imagePreview" src="{{ $article->image ? asset('storage/' . $article->image) : '' }}" 
                             class="img-thumbnail mt-2" style="max-width: 200px; {{ $article->image ? '' : 'display: none;' }}">
                    </div>

                    {{-- Estado --}}
                    <div class="col-md-6 form-group">
                        <label for="status"><strong>Estado</strong></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="1" {{ old('status', $article->status) == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status', $article->status) == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                {{-- Botones --}}
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar Artículo</button>
                    <a href="{{ route('articles.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom_js')
<script>
    $('.select2').select2({ placeholder: "Seleccione una categoría", width: '100%' });

    flatpickr(".flatpickr", { enableTime: false, dateFormat: "Y-m-d", locale: "es" });

    const barcodeContainer = document.getElementById('barcode-container');
    const addBarcodeButton = document.getElementById('add-barcode');

    addBarcodeButton.addEventListener('click', function (e) {
        e.preventDefault();
        const newInput = document.createElement('div');
        newInput.classList.add('d-flex', 'mt-2', 'barcode-group');
        newInput.innerHTML = `
            <input type="text" name="barcodes[]" class="form-control barcode-input" placeholder="Ingrese un código de barras">
            <button type="button" class="btn btn-danger ml-2 remove-barcode"><i class="fas fa-minus"></i></button>`;
        barcodeContainer.appendChild(newInput);
    });

    barcodeContainer.addEventListener('click', function (e) {
        if (e.target.closest('.remove-barcode')) {
            e.target.closest('.barcode-group').remove();
        }
    });

    function previewImage(event) {
        const input = event.target;
        const reader = new FileReader();
        reader.onload = () => {
            const imagePreview = document.getElementById('imagePreview');
            imagePreview.src = reader.result;
            imagePreview.style.display = 'block';
        };
        if (input.files && input.files[0]) reader.readAsDataURL(input.files[0]);
    }
</script>
@endsection
