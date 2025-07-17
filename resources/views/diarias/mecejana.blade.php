<!-- resources/views/admin/funcionarios.blade.php -->
@extends('adminlte::page')

@section('title', 'Formulário de Funcionários')

@section('content_header')
    <h1>Gestão de Funcionários</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @livewire('funcionario-form')
        </div>
    </div>
@stop