@extends('adminlte::page')

@section('title', 'Relatório 1000')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Relatório 1000</h1>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <!-- Filtros -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Filtros</h3>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('relatorio.1000') }}">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Data Início</label>
                                <input type="date" name="data_inicio" class="form-control" value="{{ $dataInicio }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Data Fim</label>
                                <input type="date" name="data_fim" class="form-control" value="{{ $dataFim }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Departamento</label>
                                <select name="departamento" class="form-control">
                                    <option value="">Todos</option>
                                    @foreach($departamentos as $departamento)
                                        <option value="{{ $departamento }}" {{ request('departamento') == $departamento ? 'selected' : '' }}>
                                            {{ $departamento }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search mr-2"></i>Filtrar
                    </button>
                </form>
            </div>
        </div>

        <!-- Resultados -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Resultados</h3>
                <div class="card-tools">
                    <span class="badge badge-info">Total: R$ {{ number_format($totalDiarias, 2, ',', '.') }}</span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Departamento</th>
                            <th>Função</th>
                            <th>Diária</th>
                            <th>Referência</th>
                            <th>Data</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diarias as $diaria)
                        <tr>
                            <td>{{ $diaria->nome }}</td>
                            <td>{{ $diaria->departamento }}</td>
                            <td>{{ $diaria->funcao }}</td>
                            <td>R$ {{ number_format($diaria->diaria, 2, ',', '.') }}</td>
                            <td>{{ $diaria->referencia }}</td>
                            <td>{{ \Carbon\Carbon::parse($diaria->data_inclusao)->format('d/m/Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Nenhum registro encontrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@section('css')
<style>
    .card-tools {
        margin-top: -0.5rem;
    }
</style>
@endsection 