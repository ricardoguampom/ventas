{{-- resources/views/layouts/master.blade.php --}}
@extends('adminlte::page')

@section('title', isset($title) ? $title : 'Panel de Administración')

@section('content_header')
    <h1 class="text-center">@yield('header', 'Dashboard')</h1>
@stop

@section('content')
    @yield('content')
@stop

@section('css') 
    {{-- ✅ Include Flatpickr Styles --}}
    <link rel="stylesheet" href="{{ asset('vendor/flatpickr/flatpickr.min.css') }}">
    @yield('custom_css')
@stop

@section('js') 
    {{-- ✅ Include SweetAlert --}}
    @include('sweetalert::alert')
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script> 

    {{-- ✅ Include Flatpickr JS --}}
    <script src="{{ asset('vendor/flatpickr/flatpickr.min.js') }}"></script>

    {{-- ✅ Allow Custom JS in Child Views --}}
    @yield('custom_js')
@stop
