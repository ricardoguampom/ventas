@extends('adminlte::auth.login')

@section('auth_header', 'Iniciar Sesión')

@section('auth_body')
    <p class="text-center">Ingrese sus credenciales para acceder al sistema</p>

    <form action="{{ route('login') }}" method="POST">
        @csrf

        {{-- 📌 Correo Electrónico --}}
        <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="Correo Electrónico" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-envelope"></span>
                </div>
            </div>
        </div>

        {{-- 📌 Contraseña --}}
        <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Contraseña" required>
            <div class="input-group-append">
                <div class="input-group-text">
                    <span class="fas fa-lock"></span>
                </div>
            </div>
        </div>

        {{-- 📌 Botón de Iniciar Sesión --}}
        <div class="row">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
            </div>
        </div>
    </form>
@endsection

{{-- 📌 Eliminamos el footer para ocultar "Olvidé mi contraseña" y "Registrar nueva cuenta" --}}
@section('auth_footer')
@endsection
