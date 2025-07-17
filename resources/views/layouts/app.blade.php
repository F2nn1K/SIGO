@extends('adminlte::page')

@section('title', config('app.name'))

@section('plugins.Sweetalert2', true)

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
@stop

@section('content_header')
    <h1>@yield('page_title', 'Dashboard')</h1>
@stop

@section('content')
    @yield('content')
@stop

@section('css')
    <link rel="stylesheet" href="/css/admin_custom.css">
    @yield('css')
    @stack('styles')
@stop

@section('js')
    <script>
        // Configurar o token CSRF para todas as requisições AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
    </script>
    
    @stack('scripts')
    @yield('js')
@stop
