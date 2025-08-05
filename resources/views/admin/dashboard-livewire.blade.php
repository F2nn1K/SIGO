@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="welcome-card">
                <div class="welcome-icon">
                    <i class="fas fa-home"></i>
                </div>
                <div class="welcome-content">
                    <h2 class="welcome-title">Bem-vindo ao Sistema BRS!</h2>
                    <p class="welcome-subtitle">Olá, <strong>{{ Auth::user()->name }}</strong></p>
                    <p class="welcome-message">
                        É um prazer tê-lo conosco. Use o menu lateral para navegar pelas funcionalidades do sistema.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    .welcome-subtitle {
        font-size: 20px;
        color: #007bff;
        margin-bottom: 20px;
        font-weight: 600;
    }
</style>
@stop 