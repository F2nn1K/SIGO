@php
    use Illuminate\Support\Str;
@endphp
@extends('adminlte::page')

@section('title', 'BRS - Administrador')

@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)

@section('styles')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    
    <style>
        .card-problema {
            margin-bottom: 15px;
        }
        .badge {
            font-size: 0.9em;
        }
    </style>
@endsection

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Administrador RH</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Administrador RH</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <!-- Meta tag para evitar cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between">
                            <h3 class="card-title">Listagem de Problemas</h3>
                            <a href="{{ route('rh.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Novo Problema
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="tabela-rh">
                                <thead>
                                    <tr>
                                        <th>Problema</th>
                                        <th>Estado</th>
                                        <th>Prioridade</th>
                                        <th>Horário</th>
                                        <th>Finalizado</th>
                                        <th>Prazo de entrega</th>
                                        <th>Usuário</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($problemas as $problema)
                                        @php
                                            // Debugar problemas
                                            $statusDebug = $problema->status ?? 'null';
                                            echo "<!-- Problema ID: {$problema->id}, Status: {$statusDebug} -->";
                                        @endphp
                                        <tr>
                                            <td>{{ $problema->descricao }}</td>
                                            <td>
                                                @php
                                                    $status = $problema->status ?? 'Pendente';
                                                    $statusClass = '';
                                                    $statusText = $status; // Preservar o texto original
                                                    
                                                    // Verificar todos os possíveis status de "concluído"
                                                    if (Str::contains(strtolower($status), ['conclu', 'finaliz', 'complet'])) {
                                                        $statusClass = 'bg-success';
                                                        $statusText = 'Concluído';
                                                    } 
                                                    // Verificar todos os possíveis status de "em andamento"
                                                    else if (Str::contains(strtolower($status), ['andamento', 'progress'])) {
                                                        $statusClass = 'bg-primary';
                                                        $statusText = 'Em andamento';
                                                    } 
                                                    // Padrão é pendente
                                                    else {
                                                        $statusClass = 'bg-warning';
                                                        $statusText = 'Pendente';
                                                    }
                                                @endphp
                                                <span class="badge {{ $statusClass }}">{{ $statusText }}</span>
                                                <!-- Status original: {{ $status }} -->
                                            </td>
                                            <td>
                                                @php
                                                    $prioridade = $problema->prioridade ?? 'media';
                                                    $prioridadeClass = '';
                                                    $prioridadeLabel = '';
                                                    
                                                    switch($prioridade) {
                                                        case 'baixa':
                                                            $prioridadeClass = 'bg-success';
                                                            $prioridadeLabel = 'Baixa Prioridade';
                                                            break;
                                                        case 'media':
                                                            $prioridadeClass = 'bg-warning';
                                                            $prioridadeLabel = 'Média Prioridade';
                                                            break;
                                                        case 'alta':
                                                            $prioridadeClass = 'bg-danger';
                                                            $prioridadeLabel = 'Alta Prioridade';
                                                            break;
                                                        default:
                                                            $prioridadeClass = 'bg-secondary';
                                                            $prioridadeLabel = 'Não definida';
                                                    }
                                                @endphp
                                                <span class="badge {{ $prioridadeClass }}">{{ $prioridadeLabel }}</span>
                                            </td>
                                            <td>
                                                @if($problema->status == 'Em andamento' && $problema->inicio_contagem)
                                                    <span id="contador-{{ $problema->id }}" class="contador badge badge-info" data-inicio="{{ is_object($problema->inicio_contagem) ? $problema->inicio_contagem->format('Y-m-d H:i:s') : $problema->inicio_contagem }}">
                                                        Carregando...
                                                    </span>
                                                @elseif($problema->status == 'Concluído' && $problema->inicio_contagem && $problema->data_resposta)
                                                    @php
                                                        try {
                                                            $inicio = is_object($problema->inicio_contagem) ? $problema->inicio_contagem : \Carbon\Carbon::parse($problema->inicio_contagem);
                                                            $fim = is_object($problema->data_resposta) ? $problema->data_resposta : \Carbon\Carbon::parse($problema->data_resposta);
                                                            $diff = $inicio->diffInSeconds($fim);
                                                            
                                                            $dias = floor($diff / (24 * 60 * 60));
                                                            $horas = floor(($diff % (24 * 60 * 60)) / (60 * 60));
                                                            $minutos = floor(($diff % (60 * 60)) / 60);
                                                            $segundos = $diff % 60;
                                                            
                                                            $texto = '';
                                                            if ($dias > 0) $texto .= $dias . 'd ';
                                                            if ($horas > 0 || $dias > 0) $texto .= $horas . 'h ';
                                                            if ($minutos > 0 || $horas > 0 || $dias > 0) $texto .= $minutos . 'm ';
                                                            $texto .= $segundos . 's';
                                                        } catch (\Exception $e) {
                                                            $texto = 'Erro no cálculo';
                                                        }
                                                    @endphp
                                                    <span class="text-success">{{ $texto }}</span>
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($problema->finalizado_em)
                                                    <span class="text-nowrap" data-toggle="tooltip" data-placement="top" 
                                                          title="{{ is_object($problema->finalizado_em) ? $problema->finalizado_em->format('d/m/Y H:i:s') : $problema->finalizado_em }}">
                                                        {{ is_object($problema->finalizado_em) ? $problema->finalizado_em->format('d/m/Y') : $problema->finalizado_em }}
                                                    </span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if($problema->prazo_entrega)
                                                    {{ is_object($problema->prazo_entrega) ? $problema->prazo_entrega->format('d/m/Y H:i') : $problema->prazo_entrega }}
                                                @else
                                                    <span class="text-muted">--</span>
                                                @endif
                                            </td>
                                            <td>{{ $problema->usuario_nome }}</td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info btn-detalhe" 
                                                    data-id="{{ $problema->id }}"
                                                    data-descricao="{{ $problema->descricao }}"
                                                    data-status="{{ $problema->status }}"
                                                    data-prioridade="{{ $problema->prioridade }}"
                                                    data-usuario="{{ $problema->usuario_nome }}"
                                                    data-criado="{{ is_object($problema->created_at) ? $problema->created_at->format('d/m/Y H:i') : $problema->created_at }}"
                                                    data-atualizado="{{ is_object($problema->updated_at) ? $problema->updated_at->format('d/m/Y H:i') : $problema->updated_at }}"
                                                    data-detalhes="{{ $problema->detalhes }}"
                                                    data-resposta="{{ $problema->resposta }}"
                                                    data-data-resposta="{{ $problema->data_resposta && is_object($problema->data_resposta) ? $problema->data_resposta->format('d/m/Y H:i') : '' }}"
                                                    title="Visualizar">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <a href="{{ route('rh.edit', $problema->id) }}" class="btn btn-sm btn-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger btn-excluir" 
                                                    data-id="{{ $problema->id }}"
                                                    data-descricao="{{ $problema->descricao }}"
                                                    title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">Nenhum problema registrado.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Formulário de exclusão -->
    <form id="form-excluir" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@stop

@section('css')
    <style>
        /* Estilos para badges */
        .badge {
            font-size: 0.9em;
            padding: 0.5em 0.75em;
        }
    </style>
@stop

@section('js')
    <!-- Moment.js para formatação de datas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/pt-br.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar tooltips Bootstrap
        $('[data-toggle="tooltip"]').tooltip();
        
        // Inicialização simplificada do DataTable
        if ($('#tabela-rh tbody tr').length > 0 && $('#tabela-rh tbody tr td').length > 1) {
            console.log("Inicializando DataTable sem filtros");
            // IMPORTANTE: Forçar exibição de todas as tarefas
            // Primeiro, vamos mostrar todos os dados sem DataTables
            $('#tabela-rh tbody tr').show();
            
            // Agora inicializar o DataTable
            var table = $('#tabela-rh').DataTable({
                "stateSave": false, // Não salvar estado
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "pageLength": 10, // Mostrar 10 por padrão
                "language": {
                    "sEmptyTable": "Nenhum registro encontrado",
                    "sInfo": "Mostrando de _START_ até _END_ de _TOTAL_ registros",
                    "sInfoEmpty": "Mostrando 0 até 0 de 0 registros",
                    "sInfoFiltered": "(Filtrados de _MAX_ registros)",
                    "sInfoPostFix": "",
                    "sInfoThousands": ".",
                    "sLengthMenu": "_MENU_ resultados por página",
                    "sLoadingRecords": "Carregando...",
                    "sProcessing": "Processando...",
                    "sZeroRecords": "Nenhum registro encontrado",
                    "sSearch": "Pesquisar",
                    "oPaginate": {
                        "sNext": "Próximo",
                        "sPrevious": "Anterior",
                        "sFirst": "Primeiro",
                        "sLast": "Último"
                    },
                    "oAria": {
                        "sSortAscending": ": Ordenar colunas de forma ascendente",
                        "sSortDescending": ": Ordenar colunas de forma descendente"
                    }
                },
                "responsive": true,
                "ordering": true,
                "searching": true,
                "info": true,
                "paging": true,
                "order": []  // Sem ordenação inicial
            });
            
            // Garantir que a tabela não tenha filtros aplicados
            table.search('').columns().search('').draw();
        }
        
        // Inicialização dos tooltips
        try {
            $('[data-toggle="tooltip"]').tooltip({
                whiteList: { 
                    '*': ['class', 'title', 'style']
                }
            });
        } catch(e) {
            // Erro ao inicializar tooltips
        }

        // Botão de detalhes (visualizar)
        $('#tabela-rh').on('click', '.btn-detalhe', function() {
            const id = $(this).data('id');
            const descricao = $(this).data('descricao');
            const status = $(this).data('status');
            const prioridade = $(this).data('prioridade') || 'media';
            const usuario = $(this).data('usuario');
            const criado = $(this).data('criado');
            const atualizado = $(this).data('atualizado');
            const detalhes = $(this).data('detalhes') || 'Nenhum detalhe informado.';
            const resposta = $(this).data('resposta');
            const dataResposta = $(this).data('data-resposta');
            
            // Formatar a prioridade para exibição
            let prioridadeLabel = '';
            let prioridadeClass = '';
            
            switch(prioridade) {
                case 'baixa':
                    prioridadeLabel = 'Baixa Prioridade';
                    prioridadeClass = 'bg-success';
                    break;
                case 'media':
                    prioridadeLabel = 'Média Prioridade';
                    prioridadeClass = 'bg-warning';
                    break;
                case 'alta':
                    prioridadeLabel = 'Alta Prioridade';
                    prioridadeClass = 'bg-danger';
                    break;
                default:
                    prioridadeLabel = 'Não definida';
                    prioridadeClass = 'bg-secondary';
            }

            // Exibir modal com os detalhes sem fazer requisição AJAX
            let html = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Problema:</strong> ${descricao}</p>
                        <p><strong>Estado:</strong> ${status}</p>
                        <p><strong>Prioridade:</strong> <span class="badge ${prioridadeClass}">${prioridadeLabel}</span></p>
                        <p><strong>Usuário:</strong> ${usuario}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Criado em:</strong> ${criado}</p>
                        <p><strong>Atualizado em:</strong> ${atualizado}</p>
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

            if (dataResposta) {
                html += `
                    <div class="row mt-3">
                        <div class="col-12">
                            <p><strong>Data da Resposta:</strong> ${dataResposta}</p>
                        </div>
                    </div>
                `;
            }

            Swal.fire({
                title: 'Detalhes do Problema',
                html: html,
                width: 800,
                showCloseButton: true,
                showConfirmButton: false
            });
        });

        // Botão de exclusão
        $('#tabela-rh').on('click', '.btn-excluir', function() {
            const id = $(this).data('id');
            const descricao = $(this).data('descricao');
            
            Swal.fire({
                title: 'Confirmar exclusão?',
                text: `Deseja realmente excluir o problema "${descricao}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sim, excluir!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Excluindo...',
                        text: 'Aguarde enquanto excluímos o problema',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    // Fazer requisição AJAX
                    $.ajax({
                        url: '/rh/destroy/' + id,
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            _method: 'DELETE'
                        },
                        success: function(response) {
                            console.log('Resposta do servidor:', response);
                            
                            Swal.fire({
                                title: 'Sucesso!',
                                text: 'Problema excluído com sucesso',
                                icon: 'success',
                                timer: 1500
                            }).then(() => {
                                // Força recarregamento completo da página sem cache
                                window.location.reload(true);
                            });
                        },
                        error: function(xhr) {
                            console.error('Erro ao excluir problema:', xhr);
                            
                            Swal.fire({
                                title: 'Erro!',
                                text: 'Ocorreu um erro ao excluir o problema. Tente novamente.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

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

        // NOVA FUNÇÃO PARA ATUALIZAR CONTADORES - usando Moment.js
        function atualizarContadores() {
            $('.contador').each(function() {
                try {
                    const $this = $(this);
                    const dataInicio = $this.attr('data-inicio');
                    
                    if (!dataInicio || dataInicio === 'null' || dataInicio === 'undefined') {
                        $this.text('Sem data');
                        return;
                    }
                    
                    // Criar objeto moment
                    const momentInicio = moment(dataInicio);
                    
                    if (!momentInicio.isValid()) {
                        $this.text('Data inválida');
                        return;
                    }
                    
                    // Calcular diferença
                    let now = moment();
                    let duration = moment.duration(now.diff(momentInicio));
                    
                    // Formatar a duração
                    let dias = Math.floor(duration.asDays());
                    let horas = duration.hours();
                    let minutos = duration.minutes();
                    let segundos = duration.seconds();
                    
                    // Construir o texto formatado
                    let texto = '';
                    if (dias > 0) texto += dias + 'd ';
                    texto += (horas < 10 ? '0' + horas : horas) + ':';
                    texto += (minutos < 10 ? '0' + minutos : minutos) + ':';
                    texto += (segundos < 10 ? '0' + segundos : segundos);
                    
                    // Atualizar o elemento HTML
                    $this.text(texto);
                } catch (e) {
                    // Erro ao atualizar contador
                    $(this).text('Erro');
                }
            });
        }

        // Executa a função imediatamente e configura um intervalo
        atualizarContadores();
        
        // Atualizar contadores a cada segundo
        setInterval(atualizarContadores, 1000);

        // Garantir que a página seja carregada sem cache
        $(document).ready(function() {
            // Adicionar timestamp a todos os links para evitar cache
            $('a[href*="rh"]').each(function() {
                let url = $(this).attr('href');
                if (url.indexOf('?') > -1) {
                    url += '&_=' + new Date().getTime();
                } else {
                    url += '?_=' + new Date().getTime();
                }
                $(this).attr('href', url);
            });
            
            console.log('Página do administrador carregada sem cache');
        });
        
        // Função para atualizar status diretamente
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