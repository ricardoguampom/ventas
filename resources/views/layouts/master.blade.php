@extends('adminlte::page')

@section('title', $title ?? 'Panel de Administración')

@section('content_header')
    <h1 class="text-center">@yield('header', 'Dashboard')</h1>
@endsection

@section('content')
    @yield('content')
@endsection

@section('css') 
    {{-- Flatpickr --}}
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">

    {{-- Select2 --}}
    <link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">

    @yield('custom_css')
@endsection

@section('js') 
    {{-- SweetAlert --}}
    @include('sweetalert::alert')
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script> 

    {{-- Flatpickr --}}
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>

    {{-- Select2 --}}
    <script src="{{ asset('vendor/select2/js/select2.min.js') }}"></script>

    {{-- Custom --}}
    @yield('custom_js')
@endsection
