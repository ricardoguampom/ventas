@extends('layouts.master')


@section('title', '403 - Acceso Denegado')

@section('header')
@endsection

@section('content')
<div class="container py-5 text-center">
    <h1 class="display-4 text-danger">
        <i class="fas fa-ban"></i> 403
    </h1>
    <h3 class="mt-3">Acceso Denegado</h3>
    <p>No tienes permisos para acceder a esta sección del sistema.</p>
    <a href="{{ url()->previous() }}" class="btn btn-secondary mt-3">
        <i class="fas fa-arrow-left"></i> Volver atrás
    </a>
</div>
@endsection
