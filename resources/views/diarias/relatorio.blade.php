<!-- resources/views/diarias/relatorio.blade.php -->
@extends('adminlte::page')

@section('title', 'Relatório de Diárias')

@section('content_header')
    <h1>Relatório de Diárias</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('diarias.relatorio') }}" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="data_inicio">Data Inicial:</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="{{ $dataInicio }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="data_fim">Data Final:</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim" value="{{ $dataFim }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="departamento">Departamento:</label>
                            <select class="form-control" id="departamento" name="departamento">
                                <option value="">Todos</option>
                                @foreach($departamentos as $depto)
                                    <option value="{{ $depto }}" {{ request('departamento') == $depto ? 'selected' : '' }}>{{ $depto }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="referencia">Referência:</label>
                            <select class="form-control" id="referencia" name="referencia">
                                <option value="">Todas</option>
                                @foreach($referencias as $ref)
                                    <option value="{{ $ref }}" {{ request('referencia') == $ref ? 'selected' : '' }}>{{ $ref }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                        <a href="{{ route('diarias.relatorio') }}" class="btn btn-secondary">
                            <i class="fas fa-broom"></i> Limpar Filtros
                        </a>
                        <button type="button" class="btn btn-success float-right" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resultados</h3>
            <div class="card-tools">
                <div class="badge bg-primary">
                    Total: R$ {{ number_format($totalDiarias, 2, ',', '.') }}
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Departamento</th>
                            <th>Função</th>
                            <th>Valor Diária</th>
                            <th>Referência</th>
                            <th>Data de Inclusão</th>
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
                                <td>{{ \Carbon\Carbon::parse($diaria->data_inclusao)->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Nenhum resultado encontrado para os filtros selecionados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-right"><strong>Total:</strong></td>
                            <td><strong>R$ {{ number_format($totalDiarias, 2, ',', '.') }}</strong></td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumo por Departamento</h3>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Departamento</th>
                            <th>Total de Diárias</th>
                            <th>Valor Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $resumoPorDepartamento = $diarias->groupBy('departamento');
                        @endphp
                        
                        @forelse($resumoPorDepartamento as $departamento => $items)
                            <tr>
                                <td>{{ $departamento }}</td>
                                <td>{{ $items->count() }}</td>
                                <td>R$ {{ number_format($items->sum('diaria'), 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">Nenhum resultado encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        @media print {
            .no-print, .main-sidebar, .main-header, .content-header {
                display: none !important;
            }
            
            .content-wrapper {
                margin-left: 0 !important;
                padding-top: 0 !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd;
                margin-bottom: 20px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        // Script para melhorar a experiência de impressão
        $(document).ready(function() {
            $('.btn-print').on('click', function() {
                window.print();
            });
        });
    </script>
@stop