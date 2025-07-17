@extends('adminlte::page')

@section('title', 'Tarefas por Usuários')

@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Tarefas por Usuários</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Tarefas por Usuários</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Distribuição de Tarefas por Usuários</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Usuário</th>
                                        <th class="text-center">Total de Tarefas</th>
                                        <th class="text-center">Pendentes</th>
                                        <th class="text-center">Em Andamento</th>
                                        <th class="text-center">No Prazo</th>
                                        <th class="text-center">Concluídas</th>
                                        <th class="text-center">Taxa de Conclusão</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($usuariosComTarefas) > 0)
                                        @foreach($usuariosComTarefas as $usuario)
                                            <tr>
                                                <td>{{ $usuario['nome'] }}</td>
                                                <td class="text-center">{{ $usuario['total_tarefas'] }}</td>
                                                <td class="text-center">
                                                    @if($usuario['pendentes'] > 0)
                                                        <span class="badge badge-warning">{{ $usuario['pendentes'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($usuario['em_andamento'] > 0)
                                                        <span class="badge badge-info">{{ $usuario['em_andamento'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($usuario['no_prazo'] > 0)
                                                        <span class="badge badge-primary">{{ $usuario['no_prazo'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($usuario['concluidas'] > 0)
                                                        <span class="badge badge-success">{{ $usuario['concluidas'] }}</span>
                                                    @else
                                                        <span class="text-muted">0</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $taxaConclusao = $usuario['taxa_conclusao'] ?? 0;
                                                    @endphp
                                                    <div class="progress">
                                                        <div class="progress-bar bg-success" role="progressbar" 
                                                            style="width: {{ $taxaConclusao }}%"
                                                            aria-valuenow="{{ $taxaConclusao }}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{ $taxaConclusao }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="7" class="text-center">Nenhum usuário com tarefas atribuídas.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Lista de Tarefas em Andamento -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Tarefas Ativas</h3>
                        <div class="card-tools">
                            <span class="badge badge-info mr-2">Em andamento</span>
                            <span class="badge badge-success">Concluídas (últimos 7 dias)</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="30%">Tarefa</th>
                                        <th width="15%">Responsável</th>
                                        <th width="10%">Iniciada em</th>
                                        <th width="15%">Tempo</th>
                                        <th width="30%">Detalhes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($tarefasAtivas) > 0)
                                        @foreach($tarefasAtivas as $tarefa)
                                            <tr>
                                                <td>
                                                    {{ $tarefa->descricao }}
                                                    @if($tarefa->status == 'Concluído')
                                                        <span class="badge badge-success ml-2">Concluído</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($tarefa->respondente)
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-user mr-1"></i>{{ $tarefa->respondente->name }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted">Não atribuído</span>
                                                    @endif
                                                </td>
                                                <td>{{ $tarefa->data_resposta ? $tarefa->data_resposta->format('d/m/Y H:i') : '-' }}</td>
                                                <td>
                                                    @if($tarefa->status == 'Em andamento' && $tarefa->inicio_contagem)
                                                        <span id="contador-tarefa-{{ $tarefa->id }}" class="contador badge badge-info" data-inicio="{{ is_object($tarefa->inicio_contagem) ? $tarefa->inicio_contagem->format('Y-m-d H:i:s') : $tarefa->inicio_contagem }}">
                                                            Carregando...
                                                        </span>
                                                    @elseif($tarefa->status == 'Concluído' && $tarefa->inicio_contagem && $tarefa->finalizado_em)
                                                        @php
                                                            try {
                                                                $inicio = is_object($tarefa->inicio_contagem) ? $tarefa->inicio_contagem : \Carbon\Carbon::parse($tarefa->inicio_contagem);
                                                                $fim = is_object($tarefa->finalizado_em) ? $tarefa->finalizado_em : \Carbon\Carbon::parse($tarefa->finalizado_em);
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
                                                        <span class="badge badge-success" data-toggle="tooltip" title="Finalizada em {{ is_object($tarefa->finalizado_em) ? $tarefa->finalizado_em->format('d/m/Y H:i') : $tarefa->finalizado_em }}">{{ $texto }}</span>
                                                    @else
                                                        <span class="text-muted">--</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button" class="btn btn-sm btn-info view-anotacoes" 
                                                                data-problema-id="{{ $tarefa->id }}" 
                                                                data-problema-desc="{{ $tarefa->descricao }}">
                                                            <i class="fas fa-history mr-1"></i>Histórico
                                                        </button>
                                                        
                                                        @if(!empty($tarefa->detalhes))
                                                            <button type="button" class="btn btn-sm btn-secondary" 
                                                                    data-toggle="popover" 
                                                                    data-placement="left" 
                                                                    title="Detalhes" 
                                                                    data-content="{{ is_string($tarefa->detalhes) ? $tarefa->detalhes : json_encode($tarefa->detalhes) }}">
                                                                <i class="fas fa-info-circle"></i>
                                                            </button>
                                                        @endif
                                                        
                                                        <a href="{{ route('rh.edit', $tarefa->id) }}?origem=tarefas-por-usuarios" 
                                                        class="btn btn-sm btn-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        
                                                        @if($tarefa->status == 'Em andamento')
                                                            <button type="button" class="btn btn-sm btn-success btn-concluir" 
                                                                data-id="{{ $tarefa->id }}"
                                                                data-descricao="{{ $tarefa->descricao }}">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="5" class="text-center">Nenhuma tarefa ativa no momento.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Gráfico de distribuição de tarefas -->
        @if(count($usuariosComTarefas) > 0)
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Distribuição de Tarefas por Usuário</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="grafico-distribuicao" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Status das Tarefas</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="grafico-status" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Formulários para concluir tarefas -->
    @foreach($tarefasAtivas as $tarefa)
        @if($tarefa->status == 'Em andamento')
            <form id="concluir-form-{{ $tarefa->id }}" action="{{ route('rh.concluir', $tarefa->id) }}" method="POST" style="display: none;">
                @csrf
                @method('PUT')
            </form>
        @endif
    @endforeach
@stop

@section('css')
    <!-- Estilos personalizados se necessário -->
@stop

@section('js')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Moment.js para manipulação de datas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/pt-br.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializar popovers
            $('[data-toggle="popover"]').popover({
                trigger: 'hover',
                html: false,
                sanitize: true
            });
            
            // Inicializar tooltips
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover'
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
            
            // Exibir mensagem de erro se houver
            @if($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: "{{ $errors->first() }}",
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            @endif
            
            // Exibir SweetAlert se houver mensagem de sucesso
            @if(session('swal_success'))
                Swal.fire({
                    icon: '{{ session('swal_success.icon') }}',
                    title: '{{ session('swal_success.title') }}',
                    html: '{!! session('swal_success.html') !!}',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            @endif
            
            // Exibir SweetAlert se houver mensagem de erro
            @if(session('swal_error'))
                Swal.fire({
                    icon: '{{ session('swal_error.icon') }}',
                    title: '{{ session('swal_error.title') }}',
                    text: '{{ session('swal_error.text') }}',
                    timer: 5000,
                    timerProgressBar: true,
                    showConfirmButton: true
                });
            @endif
            
            // Confirmação para concluir tarefa
            $('.btn-concluir').click(function() {
                const id = $(this).data('id');
                const descricao = $(this).data('descricao');
                
                Swal.fire({
                    title: 'Confirmar conclusão?',
                    text: `Deseja realmente concluir a tarefa "${descricao}"?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sim, concluir!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('concluir-form-'+id).submit();
                    }
                });
            });
            
            // Visualização de anotações
            $('.view-anotacoes').on('click', function() {
                const problemaId = $(this).data('problema-id');
                const problemaDesc = $(this).data('problema-desc');
                
                // Abre modal para exibir anotações
                fetchAnotacoes(problemaId, problemaDesc);
            });
            
            // Função para buscar anotações
            function fetchAnotacoes(problemaId, problemaDesc) {
                $.ajax({
                    url: `/rh/problemas/${problemaId}/anotacoes`,
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            showAnotacoesModal(problemaId, problemaDesc, response.data);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Erro',
                                text: 'Não foi possível carregar as anotações.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Não foi possível carregar as anotações.'
                        });
                    }
                });
            }
            
            // Função para exibir modal de anotações
            function showAnotacoesModal(problemaId, problemaDesc, anotacoes) {
                let html = `
                    <div class="modal fade" id="anotacoesModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Histórico: ${problemaDesc}</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="timeline">
                `;
                
                if (anotacoes.length === 0) {
                    html += '<p class="text-center">Nenhuma anotação registrada.</p>';
                } else {
                    anotacoes.forEach(function(anotacao) {
                        // Verifica se é uma anotação de início de tarefa
                        const isInicioTarefa = anotacao.conteudo.startsWith('Tarefa iniciada por');
                        const bgClass = isInicioTarefa ? 'bg-info text-white' : 'bg-light';
                        const iconClass = isInicioTarefa ? 'fas fa-play-circle' : '';
                        
                        html += `
                            <div class="time-label mb-2">
                                <small class="text-muted">${anotacao.created_at}</small>
                            </div>
                            <div class="p-2 ${bgClass} rounded mb-3">
                                ${isInicioTarefa ? `<i class="${iconClass} mr-1"></i>` : ''}
                                <strong>${anotacao.usuario_nome || 'Sistema'}</strong>: ${anotacao.conteudo}
                            </div>
                        `;
                    });
                }
                
                html += `
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Remove qualquer modal existente e adiciona o novo
                $('#anotacoesModal').remove();
                $('body').append(html);
                $('#anotacoesModal').modal('show');
            }
            
            // NOVA FUNÇÃO PARA ATUALIZAR CONTADORES - usando Moment.js
            function atualizarContadores() {
                $('.contador').each(function() {
                    try {
                        // Obter o elemento e os dados
                        let $this = $(this);
                        let dataInicio = $this.attr('data-inicio');
                        
                        if (!dataInicio || dataInicio === 'null' || dataInicio === 'undefined') {
                            $this.text('Sem data');
                            return;
                        }
                        
                        // Criar um objeto Moment a partir da data de início
                        let momentInicio = moment(dataInicio);
                        
                        // Verificar se a data é válida
                        if (!momentInicio.isValid()) {
                            $this.text('Data inválida');
                            return;
                        }
                        
                        // Calcular a diferença até agora
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
                    }
                });
            }

            // Executa a função imediatamente
            atualizarContadores();
            
            // Atualizar contadores a cada segundo
            setInterval(atualizarContadores, 1000);
            
            // Renderizar gráficos se tiver dados
            if (document.getElementById('grafico-distribuicao')) {
                // Extrair nomes e total de tarefas do array de usuários
                const usuarios = @json(collect($usuariosComTarefas)->pluck('nome')->toArray() ?? []);
                const totalTarefas = @json(collect($usuariosComTarefas)->pluck('total_tarefas')->toArray() ?? []);
                
                // Gráfico de distribuição de tarefas
                const ctxDistribuicao = document.getElementById('grafico-distribuicao').getContext('2d');
                new Chart(ctxDistribuicao, {
                    type: 'bar',
                    data: {
                        labels: usuarios,
                        datasets: [{
                            label: 'Total de Tarefas',
                            data: totalTarefas,
                            backgroundColor: 'rgba(60, 141, 188, 0.8)',
                            borderColor: 'rgba(60, 141, 188, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            },
                            title: {
                                display: true,
                                text: 'Distribuição de Tarefas por Usuário'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                }
                            }
                        }
                    }
                });
            }
            
            // Gráfico de status das tarefas
            if (document.getElementById('grafico-status')) {
                // Calcular somas a partir do array de usuários
                const pendentes = @json(collect($usuariosComTarefas)->sum('pendentes') ?? 0);
                const emAndamento = @json(collect($usuariosComTarefas)->sum('em_andamento') ?? 0);
                const noPrazo = @json(collect($usuariosComTarefas)->sum('no_prazo') ?? 0);
                const concluidas = @json(collect($usuariosComTarefas)->sum('concluidas') ?? 0);
                
                const ctxStatus = document.getElementById('grafico-status').getContext('2d');
                new Chart(ctxStatus, {
                    type: 'pie',
                    data: {
                        labels: ['Pendentes', 'Em Andamento', 'No Prazo', 'Concluídas'],
                        datasets: [{
                            data: [pendentes, emAndamento, noPrazo, concluidas],
                            backgroundColor: [
                                'rgba(255, 193, 7, 0.8)',  // Amarelo (warning)
                                'rgba(23, 162, 184, 0.8)', // Azul (info)
                                'rgba(0, 123, 255, 0.8)',  // Azul escuro (primary)
                                'rgba(40, 167, 69, 0.8)'   // Verde (success)
                            ],
                            borderColor: [
                                'rgba(255, 193, 7, 1)',
                                'rgba(23, 162, 184, 1)',
                                'rgba(0, 123, 255, 1)',
                                'rgba(40, 167, 69, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                            title: {
                                display: true,
                                text: 'Distribuição de Status das Tarefas'
                            }
                        }
                    }
                });
            }
        });
    </script>
@stop 