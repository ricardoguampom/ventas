@extends('layouts.master') {{-- Ahora extiende la vista base personalizada --}}

@section('title', 'Registrar Nueva Categoría')

@section('header')
    Registrar Nueva Categoría
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form action="{{ route('categories.store') }}" method="POST">
                @csrf

                {{-- Nombre de la Categoría --}}
                <div class="form-group">
                    <label for="name"><strong>Nombre de la Categoría</strong></label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Ingrese el nombre de la categoría" required>
                    <small class="form-text text-muted">Ejemplo: Electrónica, Ropa, Hogar...</small>
                </div>

                {{-- Descripción de la Categoría --}}
                <div class="form-group">
                    <label for="description"><strong>Descripción de la Categoría</strong></label>
                    <textarea name="description" id="description" class="form-control" placeholder="Ingrese una breve descripción sobre la categoría"></textarea>
                    <small class="form-text text-muted">Opcional: Detalles adicionales sobre esta categoría.</small>
                </div>

                {{-- Estado de la Categoría --}}
                <div class="form-group">
                    <label for="status"><strong>Estado de la Categoría</strong></label>
                    <select name="status" id="status" class="form-control" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                    <small class="form-text text-muted">Seleccione si la categoría estará activa o inactiva.</small>
                </div>

                {{-- Botones de Acción --}}
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Categoría
                    </button>
                    <button type="reset" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Limpiar Formulario
                    </button>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('custom_css')
    {{-- Estilo adicional para mejorar la apariencia --}}
    <style>
        .form-text.text-muted {
            font-size: 0.85rem;
        }
    </style>
@endsection

@section('custom_js')
    <script>
        console.log('Formulario para registrar nueva categoría cargado correctamente!');
    </script>
@endsection
