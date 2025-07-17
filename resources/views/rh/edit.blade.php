@extends('adminlte::page')

@section('title', 'Editar Tarefa')

@section('plugins.Sweetalert2', true)
@section('plugins.TempusDominusBs4', true)

@section('content_header')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Meta tags para evitar cache -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Editar Problema</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('rh.administrador') }}">Administrador RH</a></li>
                    <li class="breadcrumb-item active">Editar Problema</li>
                </ol>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary">
                    <div class="card-header">
                        <h3 class="card-title">Dados da Tarefa</h3>
                    </div>
                    
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    
                    <form action="{{ route('rh.update', $problema->id) }}" method="POST" id="form-edicao">
                        @csrf
                        @method('PUT')
                        
                        <!-- Adicionar o campo de origem, se existir -->
                        @if(request()->has('origem'))
                            <input type="hidden" name="origem" value="{{ request('origem') }}">
                        @endif
                        
                        <div class="card-body">
                            <!-- Informações básicas da tarefa, visíveis para todos -->
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="descricao">Descrição</label>
                                        <input type="text" class="form-control @error('descricao') is-invalid @enderror" 
                                            id="descricao" name="descricao" 
                                            value="{{ old('descricao', $problema->descricao) }}" 
                                            {{ !Auth::user()->can('RH') ? 'readonly' : '' }}>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="prioridade">Prioridade</label>
                                        <select class="form-control @error('prioridade') is-invalid @enderror" 
                                            id="prioridade" name="prioridade"
                                            {{ !Auth::user()->can('RH') ? 'disabled' : '' }}>
                                            <option value="baixa" {{ old('prioridade', $problema->prioridade) == 'baixa' ? 'selected' : '' }}>Baixa</option>
                                            <option value="media" {{ old('prioridade', $problema->prioridade) == 'media' ? 'selected' : '' }}>Média</option>
                                            <option value="alta" {{ old('prioridade', $problema->prioridade) == 'alta' ? 'selected' : '' }}>Alta</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select class="form-control @error('status') is-invalid @enderror" 
                                            id="status" name="status"
                                            {{ !Auth::user()->can('RH') ? 'disabled' : '' }}>
                                            <option value="Pendente" {{ old('status', $problema->status) == 'Pendente' ? 'selected' : '' }}>Pendente</option>
                                            <option value="Em andamento" {{ old('status', $problema->status) == 'Em andamento' ? 'selected' : '' }}>Em andamento</option>
                                            <option value="No prazo" {{ old('status', $problema->status) == 'No prazo' ? 'selected' : '' }}>No prazo</option>
                                            <option value="Concluído" {{ old('status', $problema->status) == 'Concluído' ? 'selected' : '' }}>Concluído</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Criado por</label>
                                        <input type="text" class="form-control" value="{{ $problema->usuario->name ?? $problema->usuario_nome }}" readonly>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Criado em</label>
                                        <input type="text" class="form-control" value="{{ $problema->created_at ? $problema->created_at->format('d/m/Y H:i') : 'N/A' }}" readonly>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Campos avançados, visíveis apenas para administradores -->
                            @if(Auth::user()->can('RH'))
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="detalhes">Detalhes</label>
                                        <textarea class="form-control @error('detalhes') is-invalid @enderror" 
                                            id="detalhes" name="detalhes" 
                                            rows="3">{{ old('detalhes', $problema->detalhes) }}</textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="prazo_entrega">Prazo de Entrega</label>
                                <div class="input-group date" id="datetimepicker" data-target-input="nearest">
                                    <input type="text" class="form-control datetimepicker-input @error('prazo_entrega') is-invalid @enderror" 
                                        id="prazo_entrega" name="prazo_entrega" 
                                        value="{{ old('prazo_entrega', $problema->prazo_entrega ? $problema->prazo_entrega->format('d/m/Y H:i') : '') }}" 
                                        data-target="#datetimepicker"/>
                                    <div class="input-group-append" data-target="#datetimepicker" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Defina um prazo para a conclusão desta tarefa (opcional).</small>
                                @error('prazo_entrega')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            @endif
                            
                            <!-- Histórico de anotações, visível para todos -->
                            @if(count($anotacoes) > 0)
                            <div class="form-group">
                                <label>Histórico de Anotações</label>
                                <div class="timeline">
                                    @foreach($anotacoes as $anotacao)
                                    <div class="time-label">
                                        <span class="bg-info">{{ $anotacao->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                    <div>
                                        <i class="fas fa-comment bg-info"></i>
                                        <div class="timeline-item">
                                            <span class="time"><i class="fas fa-clock"></i> {{ $anotacao->created_at->diffForHumans() }}</span>
                                            <h3 class="timeline-header"><strong>{{ $anotacao->usuario->name ?? 'Sistema' }}</strong> adicionou uma anotação</h3>
                                            <div class="timeline-body">
                                                {{ $anotacao->conteudo }}
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                            
                            <!-- Campo para adicionar nova anotação, visível para todos -->
                            <div class="form-group">
                                <label for="resposta">Adicionar anotação</label>
                                <textarea class="form-control @error('resposta') is-invalid @enderror" 
                                    id="resposta" name="resposta" 
                                    rows="3" placeholder="Adicione uma anotação ou comentário sobre esta tarefa...">{{ old('resposta') }}</textarea>
                                <small class="form-text text-muted">Esta anotação será registrada no histórico da tarefa.</small>
                                @error('resposta')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            
                            @if(request()->has('origem') && request('origem') == 'tarefas-por-usuarios')
                                <a href="{{ route('rh.tarefas-por-usuarios') }}" class="btn btn-secondary">Voltar</a>
                            @else
                                <a href="{{ route('rh.administrador') }}" class="btn btn-secondary">Voltar</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <!-- Tempus Dominus Bootstrap 4 para o DateTimePicker -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css" />
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@stop

@section('js')
    <!-- Moment.js (necessário para o datetimepicker) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <!-- Tempus Dominus Bootstrap 4 para o DateTimePicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            console.log('Formulário inicializado');
            
            // Configuração do DateTimePicker com mais opções para garantir o formato correto
            $('.datetimepicker').datetimepicker({
                format: 'DD/MM/YYYY HH:mm',
                locale: 'pt-br',
                useCurrent: false,
                icons: {
                    time: 'fas fa-clock',
                    date: 'fas fa-calendar',
                    up: 'fas fa-chevron-up',
                    down: 'fas fa-chevron-down',
                    previous: 'fas fa-chevron-left',
                    next: 'fas fa-chevron-right',
                    today: 'fas fa-calendar-check',
                    clear: 'fas fa-trash',
                    close: 'fas fa-times'
                },
                debug: true, // Habilita modo debug para ver logs no console
                buttons: {
                    showToday: true,
                    showClear: true,
                    showClose: true
                }
            });

            // Adicionar evento para formatar a data corretamente após selecionar
            $('.datetimepicker').on('change.datetimepicker', function(e) {
                if (e.date) {
                    // Forçar o formato correto quando a data é modificada
                    var formattedDate = e.date.format('DD/MM/YYYY HH:mm');
                    $(this).find('input').val(formattedDate);
                    console.log('Data formatada pelo DateTimePicker:', formattedDate);
                }
            });

            // Função para sanitizar e validar a data
            function sanitizarData(dataStr) {
                // Se não houver data, retornar vazio
                if (!dataStr || dataStr.trim() === '') {
                    return '';
                }

                // Remover todos os caracteres que não sejam dígitos, '/', ':', ou espaço
                var dataLimpa = dataStr.replace(/[^\d\/: ]/g, '').trim();
                
                // Verificar se a data está no formato DD/MM/YYYY HH:MM
                var match = dataLimpa.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})\s+(\d{1,2}):(\d{1,2})$/);
                if (match) {
                    var dia = match[1].padStart(2, '0');
                    var mes = match[2].padStart(2, '0');
                    var ano = match[3];
                    var hora = match[4].padStart(2, '0');
                    var minuto = match[5].padStart(2, '0');
                    
                    // Reconstruir a data no formato correto
                    return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
                }
                
                // Se não corresponder ao formato esperado, retornar vazio
                console.error('Formato de data inválido após limpeza:', dataLimpa);
                return '';
            }

            // Submit do formulário com validação da data
            $('#form-edicao').submit(function(e) {
                // Verificar se o usuário tem permissão completa
                const isAdmin = {{ Auth::user()->can('RH') ? 'true' : 'false' }};
                
                // Se não for admin, deixar o formulário ser enviado normalmente
                if (!isAdmin) {
                    // Verificar apenas se a resposta não está vazia
                    if ($('#resposta').val().trim() === '') {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campo vazio',
                            text: 'Por favor, adicione uma anotação antes de salvar.',
                            confirmButtonText: 'OK'
                        });
                        return false;
                    }
                    // Deixa o formulário ser enviado normalmente - não previne o comportamento padrão
                    return true;
                }
                
                // A partir daqui, código apenas para administradores
                e.preventDefault();
                
                // Verificar se há erros de validação básicos
                let hasErrors = false;
                
                // Verificar campo de prioridade (se não estiver disabled)
                if (!$('#prioridade').prop('disabled') && $('#prioridade').val() === '') {
                    $('#prioridade').addClass('is-invalid');
                    $('#prioridade').after('<div class="invalid-feedback">A prioridade é obrigatória</div>');
                    hasErrors = true;
                }
                
                // Verificar campo de status (se não estiver disabled)
                if (!$('#status').prop('disabled') && $('#status').val() === '') {
                    $('#status').addClass('is-invalid');
                    $('#status').after('<div class="invalid-feedback">O status é obrigatório</div>');
                    hasErrors = true;
                }
                
                // Verificar campo de descrição (se não estiver readonly)
                if (!$('#descricao').prop('readonly') && $('#descricao').val().trim() === '') {
                    $('#descricao').addClass('is-invalid');
                    $('#descricao').after('<div class="invalid-feedback">A descrição é obrigatória</div>');
                    hasErrors = true;
                }
                
                if (hasErrors) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Atenção!',
                        text: 'Por favor, preencha todos os campos obrigatórios.',
                        confirmButtonText: 'Entendi'
                    });
                    return false;
                }
                
                // Obter e sanitizar a data antes de enviar
                var dataOriginal = $('#prazo_entrega').val();
                var dataSanitizada = sanitizarData(dataOriginal);
                
                console.log('Data original:', dataOriginal);
                console.log('Data sanitizada para envio:', dataSanitizada);
                
                // Se a data foi fornecida mas é inválida após sanitização
                if (dataOriginal && dataOriginal.trim() !== '' && dataSanitizada === '') {
                    Swal.fire({
                        title: 'Erro!',
                        text: 'Formato de data inválido. Use o formato DD/MM/AAAA HH:MM.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                
                // Atualizar o campo com a data sanitizada
                $('#prazo_entrega').val(dataSanitizada);
                
                // Mostrar loading enquanto processa
                Swal.fire({
                    title: 'Salvando...',
                    text: 'Processando as alterações',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Log dos dados enviados
                console.log('Dados do formulário:', $(this).serialize());
                console.log('Data sendo enviada:', $('#prazo_entrega').val());
                
                // Ativar registro SQL no servidor
                $.ajax({
                    url: '/debug/enable-sql-log',
                    method: 'GET',
                    async: false
                });
                
                // Enviar via AJAX
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(response) {
                        console.log('Resposta de sucesso:', response);
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: 'Problema atualizado com sucesso',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            // Redirecionar para a URL de redirecionamento
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            } else {
                                // Forçar recarregamento completo para evitar cache
                                window.location.reload(true);
                            }
                        });
                    },
                    error: function(xhr) {
                        console.error('Erro na requisição:', xhr);
                        
                        let mensagemErro = 'Ocorreu um erro ao atualizar o problema.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            mensagemErro = xhr.responseJSON.message;
                        }
                        
                        console.log('Mensagem de erro:', mensagemErro);
                        console.log('Status HTTP:', xhr.status);
                        console.log('Resposta completa:', xhr.responseText);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro!',
                            text: mensagemErro,
                            confirmButtonText: 'Tentar novamente'
                        });
                    }
                });
                
                return false;
            });
            
            // Log dos valores iniciais
            console.log('Status inicial:', $('#status').val());
            console.log('Prioridade inicial:', $('#prioridade').val());
            console.log('Prazo inicial:', $('#prazo_entrega').val());
            
            // Monitorar mudanças no select de status
            $('#status').on('change', function() {
                console.log('Status alterado para:', $(this).val());
            });
            
            // Adicionar evento ao botão de salvar
            $('#btn-salvar').on('click', function(e) {
                console.log('Botão salvar clicado');
                console.log('Status antes de enviar:', $('#status').val());
                console.log('Prioridade antes de enviar:', $('#prioridade').val());
                console.log('Dados do formulário:', $('#form-edicao').serialize());
                
                // Não bloquear o envio do formulário
            });
            
            // Limpar o campo de data se o valor estiver mal formatado
            $('#prazo_entrega').on('blur', function() {
                var valor = $(this).val().trim();
                if (valor && !moment(valor, ['DD/MM/YYYY HH:mm', 'D/M/YYYY H:mm'], true).isValid()) {
                    console.log('Data inválida: ' + valor);
                    $(this).val('');
                    Swal.fire({
                        icon: 'warning',
                        title: 'Data inválida',
                        text: 'O formato da data deve ser DD/MM/AAAA HH:MM',
                        confirmButtonText: 'Entendi'
                    });
                }
            });
            
            // Variáveis para detectar mudanças
            const descricaoOriginal = $('#descricao').val();
            const detalhesOriginal = $('#detalhes').val();
            const statusOriginal = $('#status').val();
            const prazoEntregaOriginal = $('#prazo_entrega').val();
            const respostaOriginal = $('#resposta').val();
            
            let formChanged = false;
            
            // Função para verificar se o formulário foi alterado
            function checkFormChanges() {
                const descricaoAtual = $('#descricao').val();
                const detalhesAtual = $('#detalhes').val();
                const statusAtual = $('#status').val();
                const prazoEntregaAtual = $('#prazo_entrega').val();
                const respostaAtual = $('#resposta').val();
                
                formChanged = (
                    descricaoAtual !== descricaoOriginal ||
                    detalhesAtual !== detalhesOriginal ||
                    statusAtual !== statusOriginal ||
                    prazoEntregaAtual !== prazoEntregaOriginal ||
                    respostaAtual !== respostaOriginal
                );
            }
            
            // Monitorar mudanças nos campos
            $('#descricao, #detalhes, #status, #prazo_entrega, #resposta').on('change keyup', function() {
                checkFormChanges();
            });
            
            // Mostrar erros de validação com SweetAlert2
            @if ($errors->any())
                Swal.fire({
                    icon: 'error',
                    title: 'Atenção!',
                    html: `@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach`,
                    confirmButtonText: 'Entendi'
                });
            @endif

            // Adicionar evento ao botão de atualizar status
            $('#btn-atualizar-status').on('click', function(e) {
                e.preventDefault();
                
                console.log('Botão atualizar status clicado');
                console.log('Status a ser atualizado:', $('#status').val());
                
                Swal.fire({
                    title: 'Confirmação',
                    text: 'Deseja atualizar apenas o status para ' + $('#status').val() + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sim, atualizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Mostrar carregamento
                        Swal.fire({
                            title: 'Atualizando status...',
                            text: 'Por favor, aguarde',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        
                        // Enviar requisição AJAX para atualizar apenas o status
                        $.ajax({
                            url: "{{ route('rh.update-status', $problema->id) }}",
                            method: 'PUT',
                            data: {
                                status: $('#status').val(),
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                console.log('Resposta de sucesso (status):', response);
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Status atualizado!',
                                    text: 'O status foi atualizado com sucesso para ' + response.status_atual,
                                    timer: 2000
                                }).then(() => {
                                    // Forçar recarregamento completo para limpar qualquer cache
                                    window.location.reload(true);
                                });
                            },
                            error: function(xhr, status, error) {
                                console.error('Erro na requisição (status):', error);
                                console.error('Status HTTP:', xhr.status);
                                console.error('Resposta:', xhr.responseText);
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Erro ao atualizar status',
                                    text: 'Ocorreu um erro ao atualizar o status. Consulte o console para mais detalhes.'
                                });
                            }
                        });
                    }
                });
            });

            // Adicionar evento ao botão btnAtualizarStatus (novo botão)
            $('#btnAtualizarStatus').on('click', function(e) {
                e.preventDefault();
                
                const statusSelecionado = $('#status').val();
                console.log('Botão btnAtualizarStatus clicado. Status selecionado:', statusSelecionado);
                
                // Usar a nova função para atualizar o status
                atualizarStatus({{ $problema->id }}, statusSelecionado);
            });
        });
        
        // Função para atualizar status
        function atualizarStatus(id, status) {
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
        }
    </script>
@stop 