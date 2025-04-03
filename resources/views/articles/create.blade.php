@extends('layouts.master')

@section('title', 'Registrar Nuevo Artículo')

@section('header')
    <h1 class="text-center">Registrar Nuevo Artículo</h1>
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

            <form action="{{ route('articles.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    {{-- Categoría --}}
                    <div class="col-md-6 form-group">
                        <label for="category_id"><strong>Categoría del Artículo</strong></label>
                        <select name="category_id" id="category_id" class="form-control select2" required>
                            <option value="" disabled {{ old('category_id') ? '' : 'selected' }}>Seleccione una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Resto del formulario igual --}}
                    <div class="col-md-6 form-group">
                        <label for="name"><strong>Nombre del Artículo</strong></label>
                        <input type="text" name="name" id="name" class="form-control" placeholder="Ej. Router AC1200" value="{{ old('name') }}" required>
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="model"><strong>Modelo del Artículo</strong></label>
                        <input type="text" name="model" id="model" class="form-control" placeholder="Ej. AC1200" value="{{ old('model') }}">
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="description"><strong>Descripción del Artículo</strong></label>
                        <textarea name="description" id="description" class="form-control" placeholder="Ingrese una breve descripción del artículo...">{{ old('description') }}</textarea>
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="expiration_date"><strong>Fecha de Expiración</strong></label>
                        <input type="text" name="expiration_date" id="expiration_date" class="form-control flatpickr" value="{{ old('expiration_date') }}" placeholder="Seleccione la fecha">
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="barcodes"><strong>Códigos de Barras</strong></label>
                        <div id="barcode-container">
                            @foreach(old('barcodes', ['']) as $barcode)
                                <div class="d-flex mt-2 barcode-group">
                                    <input type="text" name="barcodes[]" class="form-control barcode-input" placeholder="Ingrese un código de barras" value="{{ $barcode }}">
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

                    <div class="col-md-6 form-group">
                        <label for="image"><strong>Imagen del Artículo</strong></label>
                        <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(event)">
                        <small class="form-text text-muted">Opcional: Puede cargar una imagen del artículo.</small>
                        <br>
                        <img id="imagePreview" src="" alt="Previsualización de Imagen" class="img-thumbnail mt-2" style="max-width: 200px; display: none;">
                    </div>

                    <div class="col-md-6 form-group">
                        <label for="status"><strong>Estado del Artículo</strong></label>
                        <select name="status" id="status" class="form-control" required>
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Artículo</button>
                    <button type="reset" class="btn btn-warning"><i class="fas fa-undo"></i> Limpiar Formulario</button>
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

    addBarcodeButton.addEventListener('click', function (event) {
        event.preventDefault();
        const newInput = document.createElement('div');
        newInput.classList.add('d-flex', 'mt-2', 'barcode-group');
        newInput.innerHTML = `
            <input type="text" name="barcodes[]" class="form-control barcode-input" placeholder="Ingrese un código de barras">
            <button type="button" class="btn btn-danger ml-2 remove-barcode"><i class="fas fa-minus"></i></button>`;
        barcodeContainer.appendChild(newInput);
    });

    barcodeContainer.addEventListener('click', function (event) {
        if (event.target.closest('.remove-barcode')) {
            event.target.closest('.barcode-group').remove();
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
