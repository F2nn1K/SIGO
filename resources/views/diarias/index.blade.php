<!-- resources/views/diarias/index.blade.php -->
@extends('adminlte::page')

@section('title', 'Listagem de Diárias')

@section('content_header')
    <h1>Listagem de Diárias</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between">
                <h3 class="card-title">Todas as diárias registradas</h3>
                <a href="{{ route('diarias.cadastro') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nova Diária
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Departamento</th>
                            <th>Função</th>
                            <th>Valor da Diária</th>
                            <th>Referência</th>
                            <th>Data de Inclusão</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diarias as $diaria)
                            <tr>
                                <td>{{ $diaria->id }}</td>
                                <td>{{ $diaria->nome }}</td>
                                <td>{{ $diaria->departamento }}</td>
                                <td>{{ $diaria->funcao }}</td>
                                <td>R$ {{ number_format($diaria->diaria, 2, ',', '.') }}</td>
                                <td>{{ $diaria->referencia }}</td>
                                <td>{{ \Carbon\Carbon::parse($diaria->data_inclusao)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalDetalhes{{ $diaria->id }}">
                                        <i class="fas fa-eye"></i> Detalhes
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal de Detalhes -->
                            <div class="modal fade" id="modalDetalhes{{ $diaria->id }}" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesLabel{{ $diaria->id }}" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="modalDetalhesLabel{{ $diaria->id }}">Detalhes da Diária #{{ $diaria->id }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <p><strong>Nome:</strong> {{ $diaria->nome }}</p>
                                                    <p><strong>Departamento:</strong> {{ $diaria->departamento }}</p>
                                                    <p><strong>Função:</strong> {{ $diaria->funcao }}</p>
                                                    <p><strong>Valor da Diária:</strong> R$ {{ number_format($diaria->diaria, 2, ',', '.') }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p><strong>Referência:</strong> {{ $diaria->referencia }}</p>
                                                    <p><strong>Data de Inclusão:</strong> {{ \Carbon\Carbon::parse($diaria->data_inclusao)->format('d/m/Y H:i') }}</p>
                                                    <p><strong>Criado em:</strong> {{ $diaria->created_at ? $diaria->created_at->format('d/m/Y H:i') : 'Data não disponível' }}</p>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-12">
                                                    <p><strong>Observação:</strong></p>
                                                    <div class="p-2 bg-light rounded">
                                                        {{ $diaria->observacao ?: 'Nenhuma observação registrada.' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Nenhuma diária registrada.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $diarias->links() }}
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .pagination {
            justify-content: center;
        }
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Adicione qualquer script JavaScript necessário aqui
        });
    </script>
@stop