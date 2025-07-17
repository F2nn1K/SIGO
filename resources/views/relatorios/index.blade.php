@extends('adminlte::page')

@section('title', 'Relatórios')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Relatórios</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            @can('Ver Relatório 1000')
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Relatório 1000</h3>
                    </div>
                    <div class="card-body">
                        <p>Relatório de dados específicos 1000.</p>
                        <a href="{{ route('relatorio.1000') }}" class="btn btn-primary">
                            <i class="fas fa-file-alt mr-2"></i>Acessar
                        </a>
                    </div>
                </div>
            </div>
            @endcan

            @can('Ver Relatório 1001')
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Relatório 1001</h3>
                    </div>
                    <div class="card-body">
                        <p>Relatório de dados específicos 1001.</p>
                        <a href="{{ route('relatorio.1001') }}" class="btn btn-primary">
                            <i class="fas fa-file-alt mr-2"></i>Acessar
                        </a>
                    </div>
                </div>
            </div>
            @endcan

            @can('Ver Relatório 1002')
            <div class="col-lg-4 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Relatório RH</h3>
                    </div>
                    <div class="card-body">
                        <p>Relatório de Recursos Humanos.</p>
                        <a href="{{ route('relatorio.rh') }}" class="btn btn-primary">
                            <i class="fas fa-users mr-2"></i>Acessar
                        </a>
                    </div>
                </div>
            </div>
            @endcan
        </div>
    </div>
</section>
@endsection 