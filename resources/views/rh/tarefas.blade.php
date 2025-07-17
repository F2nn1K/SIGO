@extends('adminlte::page')

@section('title', 'Tarefas RH')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Tarefas RH</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tarefas RH</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        @if(isset($erro))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger">
                    <strong>Erro:</strong> {{ $erro }}
                </div>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Gerenciamento de Tarefas</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Tarefa</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Usuário</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($problemas as $problema)
                                    <tr>
                                        <td class="align-middle">{{ $problema->descricao }}</td>
                                        <td class="align-middle">
                                            @if($problema->prioridade == 'alta')
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-arrow-up"></i> Alta Prioridade
                                                </span>
                                            @elseif($problema->prioridade == 'baixa')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-arrow-down"></i> Baixa Prioridade
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-equals"></i> Média Prioridade
                                                </span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            {!! $problema->status == 'Concluído' 
                                                ? '<span class="badge bg-success">Concluído</span>' 
                                                : ($problema->status == 'Em andamento' 
                                                    ? '<span class="badge bg-primary">Em andamento</span>' 
                                                    : '<span class="badge bg-warning">Pendente</span>') !!}
                                        </td>
                                        <td class="align-middle">{{ $problema->usuario_nome ?? '--' }}</td>
                                        <td class="align-middle">
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-info btn-detalhe" 
                                                    data-id="{{ $problema->id }}"
                                                    data-descricao="{{ $problema->descricao }}"
                                                    data-prioridade="{{ $problema->prioridade }}"
                                                    data-status="{{ $problema->status }}"
                                                    data-usuario="{{ $problema->usuario_nome }}"
                                                    data-detalhes="{{ is_string($problema->detalhes) ? $problema->detalhes : json_encode($problema->detalhes) }}"
                                                    data-resposta="{{ $problema->resposta ?? '' }}"
                                                    data-criado="{{ $problema->created_at->format('d/m/Y H:i') }}"
                                                    data-atualizado="{{ $problema->updated_at->format('d/m/Y H:i') }}"
                                                    title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if($problema->status == 'Pendente')
                                                    <button type="button" class="btn btn-sm btn-success" onclick="event.preventDefault(); document.getElementById('iniciar-form-{{ $problema->id }}').submit();" title="Iniciar">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <form id="iniciar-form-{{ $problema->id }}" action="{{ route('rh.iniciar', $problema->id) }}" method="POST" style="display: none;">
                                                        @csrf
                                                        @method('PUT')
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Nenhuma tarefa registrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de conclusão -->
    <form id="form-concluir" method="POST" style="display: none;">
        @csrf
        @method('PUT')
    </form>
@stop

@section('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    
    <style>
        .badge {
            font-size: 0.9em;
            padding: 0.4em 0.6em;
        }
        
        .badge-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }
        
        .badge-success {
            background-color: #28a745;
            color: white;
        }
        
        .descricao-completa {
            display: none;
        }
        
        .btn-ver-mais {
            cursor: pointer;
            color: #007bff;
        }
    </style>
@stop

@section('js')
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Evitar cache
            $('a[href*="rh"]').each(function() {
                let url = $(this).attr('href');
                if (url.indexOf('?') > -1) {
                    url += '&_=' + new Date().getTime();
                } else {
                    url += '?_=' + new Date().getTime();
                }
                $(this).attr('href', url);
            });
            
            console.log('Página de tarefas carregada sem cache');
            
            // Exibir mensagem de sucesso se houver
            @if(session('message'))
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: "{{ session('message') }}",
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            @endif
            
            // Ver mais / ver menos descrição
            $('.btn-ver-mais').click(function() {
                let id = $(this).data('id');
                $('#descricao-curta-' + id).toggle();
                $('#descricao-completa-' + id).toggle();
                
                if ($(this).text() === 'Ver mais') {
                    $(this).text('Ver menos');
                } else {
                    $(this).text('Ver mais');
                }
            });
            
            // Botão de detalhes (visualizar)
            $('.btn-detalhe').click(function() {
                const id = $(this).data('id');
                const descricao = $(this).data('descricao');
                const prioridade = $(this).data('prioridade');
                const status = $(this).data('status');
                const usuario = $(this).data('usuario');
                const detalhes = $(this).data('detalhes') || 'Nenhum detalhe informado.';
                const resposta = $(this).data('resposta');
                const criado = $(this).data('criado');
                const atualizado = $(this).data('atualizado');

                // Exibir modal com os detalhes sem fazer requisição AJAX
                let html = `
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Tarefa:</strong> ${descricao}</p>
                            <p><strong>Prioridade:</strong> `;
                
                            if (prioridade === 'alta') {
                                html += `<span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Alta Prioridade</span>`;
                            } else if (prioridade === 'baixa') {
                                html += `<span class="badge badge-success"><i class="fas fa-arrow-down"></i> Baixa Prioridade</span>`;
                            } else {
                                html += `<span class="badge badge-warning"><i class="fas fa-equals"></i> Média Prioridade</span>`;
                            }
                
                            html += `</p>
                            <p><strong>Status:</strong> ${status}</p>
                            <p><strong>Usuário:</strong> ${usuario}</p>
                            <p><strong>Criado em:</strong> ${criado}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Última Atualização:</strong> ${atualizado}</p>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <p><strong>Detalhes:</strong></p>
                            <div class="p-2 bg-light rounded">
                                ${detalhes}
                            </div>
                        </div>
                    </div>
                `;

                if (resposta) {
                    html += `
                        <div class="row mt-3">
                            <div class="col-12">
                                <p><strong>Resposta:</strong></p>
                                <div class="p-2 bg-light rounded">
                                    ${resposta}
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                Swal.fire({
                    title: 'Detalhes da Tarefa',
                    html: html,
                    width: 800,
                    showCloseButton: true,
                    showConfirmButton: false
                });
            });
            
            // DataTable para a tabela de problemas
            $('#tabela-problemas').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                },
                "order": [[0, "desc"]]
            });
            
            // Função para atualizar status
            window.atualizarStatus = function(id, status) {
                console.log('Atualizando status do problema #' + id + ' para: ' + status);
                
                // Confirmar antes de atualizar
                Swal.fire({
                    title: 'Confirmar alteração',
                    text: 'Deseja realmente alterar o status para "' + status + '"?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, alterar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar loading
                        Swal.fire({
                            title: 'Atualizando...',
                            text: 'Aguarde enquanto atualizamos o status',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Fazer requisição AJAX
                        $.ajax({
                            url: '/rh/problemas/' + id + '/status',
                            method: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                status: status
                            },
                            success: function(response) {
                                console.log('Resposta do servidor:', response);
                                
                                Swal.fire({
                                    title: 'Sucesso!',
                                    text: 'Status atualizado com sucesso',
                                    icon: 'success',
                                    timer: 1500
                                }).then(() => {
                                    // Força recarregamento completo da página sem cache
                                    window.location.reload(true);
                                });
                            },
                            error: function(xhr) {
                                console.error('Erro ao atualizar status:', xhr);
                                
                                Swal.fire({
                                    title: 'Erro!',
                                    text: 'Ocorreu um erro ao atualizar o status. Tente novamente.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            };
        });
    </script>
@stop 