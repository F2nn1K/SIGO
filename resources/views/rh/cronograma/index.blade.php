@extends('adminlte::page')

@section('title', 'Cronograma')

@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)
@section('plugins.Tempusdominus', true)
@section('plugins.Select2', true)

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endpush

@push('js')
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@endpush

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1><i class="fas fa-calendar-alt mr-2"></i>Cronograma</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Cronograma</li>
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
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="card-title">Listagem de Tarefas</h3>
                            <div class="card-tools">
                                <!-- Botões temporariamente desativados
                                <button type="button" id="btn-criar-tarefa" class="btn btn-success btn-lg mr-2">
                                    <i class="fas fa-plus mr-1"></i> Nova Tarefa
                                </button>
                                <button type="button" id="sincronizar-rh" class="btn btn-danger btn-lg">
                                    <i class="fas fa-sync mr-1"></i> Sincronizar com RH
                                </button>
                                -->
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div id="loading-indicator" class="text-center py-3">
                                <i class="fas fa-spinner fa-spin fa-2x"></i>
                                <p class="mt-2">Carregando tarefas...</p>
                            </div>
                            <table class="table table-bordered table-striped" id="tabela-tarefas">
                                <thead>
                                    <tr>
                                        <th width="35%">Tarefa</th>
                                        <th width="15%">Prioridade</th>
                                        <th width="15%">Usuários</th>
                                        <th width="15%">Data</th>
                                        <th width="9%">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Os dados serão carregados via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="editarTarefaModal" tabindex="-1" role="dialog" aria-labelledby="editarTarefaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarTarefaModalLabel">Editar Tarefa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form-editar-tarefa">
                        <input type="hidden" id="tarefa_id" name="tarefa_id">
                        
                        <!-- Nome da tarefa (bloqueado) -->
                        <div class="form-group">
                            <label for="nome_tarefa">Nome da Tarefa</label>
                            <input type="text" class="form-control" id="nome_tarefa" name="nome_tarefa" readonly>
                        </div>
                        
                        <!-- Prioridade -->
                        <div class="form-group">
                            <label>Prioridade</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="status" value="baixa" autocomplete="off"> Baixa Prioridade
                                </label>
                                <label class="btn btn-outline-warning">
                                    <input type="radio" name="status" value="media" autocomplete="off"> Média Prioridade
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="status" value="alta" autocomplete="off"> Alta Prioridade
                                </label>
                            </div>
                        </div>
                        
                        <!-- Usuários -->
                        <div class="form-group">
                            <label for="usuario_id">Usuários</label>
                            <div class="card">
                                <div class="card-header py-2 bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="fas fa-users mr-2 text-primary"></i>
                                            <span><strong>Selecione os responsáveis</strong></span>
                                        </div>
                                        <button type="button" id="limpar-usuarios" class="btn btn-sm btn-outline-secondary" title="Remover todos os usuários">
                                            <i class="fas fa-times-circle"></i> Limpar
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <select class="form-control select2" id="usuario_id" name="usuario_id[]" multiple style="width: 100%; height: 120px;">
                                        <option value="">Carregando usuários...</option>
                                    </select>
                                    <small class="text-muted d-block mt-2"><i class="fas fa-info-circle mr-1"></i>Selecione até 5 usuários para esta tarefa</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Datas por mês -->
                        <div class="form-group">
                            <label>Datas por Mês</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Janeiro</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_janeiro" name="data_janeiro" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_janeiro">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Fevereiro</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_fevereiro" name="data_fevereiro" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_fevereiro">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Março</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_marco" name="data_marco" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_marco">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Abril</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_abril" name="data_abril" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_abril">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Maio</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_maio" name="data_maio" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_maio">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Junho</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_junho" name="data_junho" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_junho">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Julho</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_julho" name="data_julho" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_julho">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Agosto</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_agosto" name="data_agosto" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_agosto">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Setembro</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_setembro" name="data_setembro" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_setembro">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Outubro</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_outubro" name="data_outubro" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_outubro">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Novembro</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_novembro" name="data_novembro" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_novembro">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input-group mb-3">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">Dezembro</span>
                                        </div>
                                        <input type="text" class="form-control datepicker data-mes" id="data_dezembro" name="data_dezembro" placeholder="__/__/____">
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light calendar-trigger" data-target="data_dezembro">
                                                <i class="fas fa-calendar-alt"></i>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-salvar">Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Criar Nova Tarefa -->
    <div class="modal fade" id="criarTarefaModal" tabindex="-1" role="dialog" aria-labelledby="criarTarefaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="criarTarefaModalLabel">Nova Tarefa</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Fechar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form-criar-tarefa">
                        <!-- Nome da tarefa -->
                        <div class="form-group">
                            <label for="nova_tarefa">Nome da Tarefa</label>
                            <input type="text" class="form-control" id="nova_tarefa" name="nome_tarefa" required>
                        </div>
                        
                        <!-- Prioridade -->
                        <div class="form-group">
                            <label>Prioridade</label>
                            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="status" value="baixa" autocomplete="off"> Baixa Prioridade
                                </label>
                                <label class="btn btn-outline-warning active">
                                    <input type="radio" name="status" value="media" autocomplete="off" checked> Média Prioridade
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="status" value="alta" autocomplete="off"> Alta Prioridade
                                </label>
                            </div>
                        </div>
                        
                        <!-- Usuários -->
                        <div class="form-group">
                            <label for="novo_usuario_id">Usuários</label>
                            <div class="card">
                                <div class="card-header py-2 bg-light">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <i class="fas fa-users mr-2 text-primary"></i>
                                            <span><strong>Selecione os responsáveis</strong></span>
                                        </div>
                                        <button type="button" id="limpar-novos-usuarios" class="btn btn-sm btn-outline-secondary" title="Remover todos os usuários">
                                            <i class="fas fa-times-circle"></i> Limpar
                                        </button>
                                    </div>
                                </div>
                                <div class="card-body p-3">
                                    <select class="form-control select2" id="novo_usuario_id" name="usuario_id[]" multiple style="width: 100%; height: 120px;">
                                        <option value="">Carregando usuários...</option>
                                    </select>
                                    <small class="text-muted d-block mt-2"><i class="fas fa-info-circle mr-1"></i>Selecione até 5 usuários para esta tarefa</small>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btn-criar">Criar Tarefa</button>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid mt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-code mr-2"></i> Referência: Página RH Administrador
                </h3>
                <div class="card-tools">
                    <button type="button" id="toggle-iframe" class="btn btn-tool">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="iframe-container" id="iframe-container">
                    <!-- O iframe será carregado dinamicamente quando o usuário clicar no botão -->
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    /* Estilos para badges */
    .badge {
        font-size: 0.9em;
        padding: 0.5em 0.75em;
    }
    
    /* Frame para visualizar a origem (oculto por padrão) */
    .iframe-container {
        width: 100%;
        height: 0;
        overflow: hidden;
        transition: height 0.3s ease;
    }
    
    .iframe-container.show {
        height: 600px;
    }
    
    /* Estilos adaptados do sistema */
    .btn-group-sm > .btn, .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
        line-height: 1.5;
        border-radius: 0.2rem;
    }
    
    .table th, .table td {
        vertical-align: middle;
    }
    
    .btn-group .btn {
        margin-right: 5px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    .btn-group .btn i {
        margin: 0;
    }
    
    #loading-indicator {
        position: absolute;
        left: 0;
        right: 0;
        z-index: 1;
    }
    
    /* Estilos para o status no modal */
    .btn-group-toggle .btn {
        flex: 1;
        text-align: center;
        margin: 0 5px;
    }
    
    .btn-group-toggle .btn:first-child {
        margin-left: 0;
    }
    
    .btn-group-toggle .btn:last-child {
        margin-right: 0;
    }
    
    .btn-group-toggle .btn.active {
        font-weight: bold;
    }
    
    /* Cores mais fortes quando selecionados */
    .btn-outline-success.active {
        background-color: #28a745 !important;
        color: white !important;
    }
    
    .btn-outline-warning.active {
        background-color: #ffc107 !important;
        color: #212529 !important;
    }
    
    .btn-outline-danger.active {
        background-color: #dc3545 !important;
        color: white !important;
    }
    
    /* Estilos para calendários */
    .calendar-trigger {
        cursor: pointer;
    }
    
    .calendar-trigger:hover {
        background-color: #e9ecef !important;
    }
    
    /* Estilos para o modal */
    .modal-body {
        max-height: 70vh;
        overflow-y: auto;
    }
    
    /* Estilo para meses */
    .input-group-text {
        min-width: 85px;
    }
    
    /* Ajustes na altura e aparência do select2 */
    .select2-container--bootstrap4 .select2-selection--multiple {
        min-height: 38px;
        border-color: #ced4da;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
    }
    
    .select2-container--bootstrap4 .select2-selection--multiple .select2-search__field {
        margin-top: 3px;
    }
    
    .select2-container--bootstrap4 .select2-results__option--highlighted[aria-selected] {
        background-color: #3c8dbc;
    }
    
    .select2-container--bootstrap4 .select2-dropdown {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }
    
    /* Alinhamento do Select2 com o AdminLTE */
    .select2-container--bootstrap4 {
        vertical-align: middle;
    }
    
    .input-group .select2-container--bootstrap4 {
        flex: 1 1 auto;
        width: 1% !important;
    }
    
    /* Cores que combinam com o AdminLTE */
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        background-color: #17a2b8;
        border-color: #148a9d;
        color: #fff;
        padding: 0.2rem 0.6rem;
        margin-top: 0.3rem;
        margin-right: 0.5rem;
        border-radius: 3px;
        box-shadow: 0 1px 2px rgba(0,0,0,0.2);
        display: inline-flex;
        align-items: center;
    }
    
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 5px;
        font-weight: bold;
    }
    
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fff;
        opacity: 0.8;
    }
    
    /* Melhoria na exibição de usuários */
    #usuario_id + .select2-container .select2-selection {
        background-color: #f8f9fa;
    }
    
    /* Card para o seletor de usuários */
    .form-group .card {
        border: 1px solid #ddd;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .form-group .card .card-body {
        background-color: #f8f9fa;
        padding: 15px;
    }
    
    .form-group .card .card-header {
        border-bottom: 1px solid #eee;
    }
    
    /* Customização do Select2 dentro do card - AUMENTADO */
    .card .select2-container--bootstrap4 .select2-selection--multiple {
        border: 1px solid #ced4da;
        border-radius: 4px;
        min-height: 150px !important; /* Aumentado para 150px */
        padding: 10px;
    }
    
    /* Ajuste para o Select2 dentro do card - permite mais espaço para seleção */
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__rendered {
        display: block;
        padding: 0 5px;
        min-height: 130px; /* Aumentado para acompanhar o container */
    }
    
    /* Aumentar o tamanho dos chips de usuários */
    .select2-container--bootstrap4 .select2-selection--multiple .select2-selection__choice {
        font-size: 16px;
        padding: 8px 12px;
        margin: 5px;
        border-radius: 4px;
    }
    
    /* Aumentar a área de exibição do select2 */
    .select2-container--default .select2-results > .select2-results__options {
        max-height: 300px;
        overflow-y: auto;
    }
    
    /* Opções maiores no select2 */
    .select2-results__option {
        padding: 8px 12px;
        font-size: 14px;
    }
    
    /* Garante que o dropdown do Select2 apareça corretamente no modal */
    .select2-container--open {
        z-index: 9999;
    }
</style>
@stop

@section('js')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Moment.js para manipulação de datas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/pt-br.min.js"></script>
    <!-- Toastr.js para notificações -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- jQuery Mask para formatação de campos -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <!-- Bootstrap Datepicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/locales/bootstrap-datepicker.pt-BR.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Configurar toastr
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: "toast-top-right",
                timeOut: 5000
            };
            
            // Token CSRF para requisições AJAX
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            
            // Aplicar máscara para campos de data
            $('.data-mes').mask('00/00/0000', {
                placeholder: "DD/MM/AAAA",
                clearIfNotMatch: true
            });
            
            // Inicializar Bootstrap Datepicker para todos os campos de data
            $('.data-mes').datepicker({
                format: 'dd/mm/yyyy',
                language: 'pt-BR',
                autoclose: true,
                todayHighlight: true,
                clearBtn: true
            });
            
            // Evento para ícones de calendário
            $('.calendar-trigger').click(function() {
                const targetId = $(this).data('target');
                $('#' + targetId).focus();
            });
            
            // Inicializar Select2 para o campo de usuários
            $('.select2').select2({
                theme: 'bootstrap4',
                placeholder: 'Selecione os usuários',
                allowClear: true,
                width: '100%',
                maximumSelectionLength: 5,
                language: {
                    maximumSelected: function (e) {
                        return 'Você só pode selecionar ' + e.maximum + ' usuários';
                    },
                    noResults: function() {
                        return "Nenhum resultado encontrado";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                templateResult: formatUserResult,
                templateSelection: formatUserSelection
            });
            
            // Inicialização específica para cada Select2 considerando o modal parent adequado
            $('#usuario_id').select2({
                dropdownParent: $('#editarTarefaModal .modal-body')
            });
            
            $('#novo_usuario_id').select2({
                dropdownParent: $('#criarTarefaModal .modal-body')
            });
            
            // Garantir que o Select2 permita remover usuários já selecionados
            $('#usuario_id').on('select2:unselecting', function(e) {
                // Permitir a remoção do usuário
            });
            
            // Adicionar evento para quando a seleção é alterada
            $('#usuario_id').on('change', function(e) {
                const valores = $(this).val() || [];
            });
            
            // Formatar opções de usuários
            function formatUserResult(user) {
                if (!user.id) return user.text;
                return $('<span><i class="fas fa-user mr-2"></i>' + user.text + '</span>');
            }
            
            // Formatar opções selecionadas
            function formatUserSelection(user) {
                if (!user.id) return user.text;
                return $('<span>' + user.text + '</span>');
            }
            
            // Evento para o botão editar
            $(document).on('click', '.btn-editar', function() {
                const tarefa_id = $(this).data('id');
                const descricao = $(this).data('descricao');
                const status = $(this).data('status');
                const usuarios = $(this).data('usuarios');
                
                // Preencher o formulário de edição
                $('#tarefa_id').val(tarefa_id);
                $('#nome_tarefa').val(descricao);
                
                // Selecionar a prioridade correta
                $('.btn-group-toggle .btn').removeClass('active');
                $('input[name="status"]').prop('checked', false);
                
                $(`input[name="status"][value="${status}"]`).prop('checked', true);
                $(`input[name="status"][value="${status}"]`).parent().addClass('active');
                
                // Limpar select de usuários
                $('#usuario_id').val(null).trigger('change');
                
                // Se houver usuários, selecionar
                if (usuarios && Array.isArray(usuarios) && usuarios.length > 0) {
                    $('#usuario_id').val(usuarios).trigger('change');
                } else if (typeof usuarios === 'string') {
                    try {
                        // Tentar extrair valores caso seja uma string JSON
                        const usuariosArray = JSON.parse(usuarios);
                        if (Array.isArray(usuariosArray)) {
                            $('#usuario_id').val(usuariosArray).trigger('change');
                        }
                    } catch (e) {
                        // Fallback - tentar usar como string simples
                        $('#usuario_id').val([usuarios]).trigger('change');
                    }
                }
                
                // Carregar datas se necessário
                carregarDatas(tarefa_id);
                
                // Abrir o modal
                $('#editarTarefaModal').modal('show');
            });
            
            // Botão Salvar no modal
            $('#btn-salvar').click(function() {
                const tarefaId = $('#tarefa_id').val();
                
                // Obter dados do formulário
                const formData = new FormData($('#form-editar-tarefa')[0]);
                
                // Adicionar método PUT via _method para Laravel
                formData.append('_method', 'PUT');
                
                // Lidar com a seleção de usuários
                const usuariosSelecionados = $('#usuario_id').val();
                
                // Remover quaisquer entradas anteriores de usuários
                for (const pair of formData.entries()) {
                    if (pair[0] === 'usuario_id[]' || pair[0] === 'usuario_id') {
                        formData.delete(pair[0]);
                    }
                }
                
                // Se tiver usuários selecionados, adicionar ao formData
                if (usuariosSelecionados && usuariosSelecionados.length > 0) {
                    // Adicionar cada ID de usuário separadamente
                    usuariosSelecionados.forEach(function(id) {
                        formData.append('usuario_id[]', id);
                    });
                } else {
                    // Se não tiver usuários selecionados, definir flag para limpar
                    formData.append('usuarios_vazio', 'true');
                }
                
                // Adicionar datas manualmente para garantir que estão sendo enviadas
                $('.data-mes').each(function() {
                    const fieldName = $(this).attr('name');
                    const fieldValue = $(this).val();
                    formData.append(fieldName, fieldValue || ''); // Enviar vazio se não tiver valor
                });
                
                // Verificar prioridade
                const prioridade = $('input[name="status"]:checked').val();
                formData.append('status', prioridade || 'media');
                
                // Botão de loading
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');
                
                // Enviar via AJAX
                $.ajax({
                    url: "{{ route('rh.cronograma.update', ['id' => ':id']) }}".replace(':id', tarefaId),
                    type: 'POST', // Usando POST com _method para simular PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Tarefa atualizada com sucesso!');
                            $('#editarTarefaModal').modal('hide');
                            carregarTarefas();
                        } else {
                            toastr.error(response.error || 'Erro ao atualizar tarefa');
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            Object.keys(errors).forEach(key => {
                                toastr.error(errors[key][0]);
                            });
                        } else {
                            toastr.error('Erro ao atualizar tarefa: ' + (xhr.responseJSON?.message || xhr.statusText));
                        }
                    },
                    complete: function() {
                        // Restaurar botão
                        $('#btn-salvar').prop('disabled', false).html('Salvar');
                    }
                });
            });
            
            // Carregar usuários
            carregarUsuarios();
            
            // Carregar tarefas iniciais
            carregarTarefas();
            
            // Evento de clique no botão de sincronização
            $('#sincronizar-rh').click(function() {
                // Confirmar sincronização e limpar dados
                Swal.fire({
                    title: 'Sincronizar Tarefas',
                    text: 'Isso vai importar todas as tarefas da tabela RH para o cronograma. Continuar?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sim, sincronizar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        sincronizarTarefas();
                    }
                });
            });
            
            // Função para forçar a sincronização em caso de erro
            function sincronizarTarefas() {
                $('#sincronizar-rh').prop('disabled', true);
                $('#sincronizar-rh').html('<i class="fas fa-spinner fa-spin mr-1"></i> Sincronizando...');
                
                // Mostrar notificação de processamento
                toastr.info('Sincronizando dados com a tabela rh_problemas...', 'Aguarde');
                
                $.ajax({
                    url: "/rh/cronograma/sincronizar",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Sincronização concluída com sucesso!', 'Sucesso');
                            
                            // Tentar carregar as tarefas novamente
                            setTimeout(function() {
                                carregarTarefas();
                            }, 1000); // Aguardar 1 segundo antes de tentar novamente
                        } else {
                            toastr.error(response.error || 'Erro ao sincronizar com RH', 'Erro');
                            
                            Swal.fire({
                                title: 'Erro na Sincronização',
                                text: response.error || 'Ocorreu um erro ao sincronizar os dados. Tente novamente mais tarde.',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        let mensagemErro = 'Ocorreu um erro ao sincronizar com RH';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            mensagemErro = xhr.responseJSON.error;
                        }
                        
                        toastr.error(mensagemErro, 'Erro');
                        
                        Swal.fire({
                            title: 'Erro na Sincronização',
                            text: mensagemErro,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    },
                    complete: function() {
                        $('#sincronizar-rh').prop('disabled', false);
                        $('#sincronizar-rh').html('<i class="fas fa-sync mr-1"></i> Sincronizar com RH');
                    }
                });
            }
            
            // Função para carregar usuários
            function carregarUsuarios() {
                // Inicializar os selects com mensagem de carregamento
                const selects = ['#usuario_id', '#novo_usuario_id'];
                
                selects.forEach(function(select) {
                    $(select).empty();
                    $(select).append(new Option('Carregando usuários...', '', false, false));
                });
                
                $.ajax({
                    url: "/api/usuarios",
                    type: 'GET',
                    success: function(response) {
                        selects.forEach(function(select) {
                            $(select).empty();
                        });
                        
                        if (Array.isArray(response) && response.length > 0) {
                            response.forEach(function(usuario) {
                                selects.forEach(function(select) {
                                    $(select).append(new Option(usuario.name, usuario.id, false, false));
                                });
                            });
                        } else {
                            selects.forEach(function(select) {
                                $(select).append(new Option('Nenhum usuário encontrado', '', false, false));
                            });
                        }
                    },
                    error: function(xhr) {
                        selects.forEach(function(select) {
                            $(select).empty();
                            $(select).append(new Option('Erro ao carregar usuários', '', false, false));
                        });
                    }
                });
            }
            
            // Função para carregar as tarefas
            function carregarTarefas() {
                $('#loading-indicator').show();
                
                $.ajax({
                    url: "/rh/cronograma/eventos",
                    type: 'GET',
                    success: function(response) {
                        $('#loading-indicator').hide();
                        const tarefas = response.data || [];
                        renderizarTabela(tarefas);
                    },
                    error: function(xhr) {
                        // Renderizar tabela vazia
                        renderizarTabela([]);
                        
                        let mensagemErro = 'Ocorreu um erro ao carregar as tarefas.';
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            mensagemErro = xhr.responseJSON.error;
                        }
                        
                        // Mostrar mensagem de erro mais informativa
                        toastr.error(mensagemErro, 'Erro!');
                        
                        // Notificar o usuário de forma mais amigável
                        Swal.fire({
                            title: 'Atenção!',
                            text: 'Clique no botão "Sincronizar com RH" para tentar corrigir os dados e recarregar as tarefas.',
                            icon: 'warning',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
            
            // Função para forçar a sincronização em caso de erro
            function sincronizarTarefas() {
                $.ajax({
                    url: "/rh/cronograma/sincronizar",
                    type: 'POST',
                    success: function(response) {
                        if (response.success) {
                            // Tentar carregar as tarefas novamente
                            setTimeout(function() {
                                carregarTarefas();
                            }, 1000); // Aguardar 1 segundo antes de tentar novamente
                        } else {
                            Swal.fire({
                                title: 'Erro!',
                                text: response.error || 'Erro ao sincronizar com RH',
                                icon: 'error',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Erro!',
                            text: xhr.responseJSON?.error || 'Erro ao sincronizar com RH',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            }
            
            // Função para renderizar a tabela
            function renderizarTabela(tarefas) {
                const tbody = $('#tabela-tarefas tbody');
                tbody.empty();
                
                if (!tarefas || tarefas.length === 0) {
                    tbody.html(`
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times text-muted fa-3x mb-3"></i>
                                    <h5 class="text-muted">Nenhuma tarefa encontrada</h5>
                                    <p class="text-muted">Os dados são carregados diretamente da tabela rh_problemas.<br>
                                    <small class="text-muted">Certifique-se que existem registros na tabela rh_problemas.</small></p>
                                </div>
                            </td>
                        </tr>
                    `);
                    return;
                }
                
                $.each(tarefas, function(index, tarefa) {
                    const usuariosNomes = tarefa.usuarios_nomes || 'Não atribuído';
                    const dataProxima = tarefa.proxima_data ? formatarData(tarefa.proxima_data) : 'Não definida';
                    
                    // Usar diretamente a prioridade da tarefa (já normalizada pelo servidor)
                    let prioridade = tarefa.prioridade || 'media';
                    
                    // Definir as badges de prioridade com cores e ícones consistentes
                    let badgePrioridade = '';
                    
                    if (prioridade === 'baixa') {
                        badgePrioridade = '<span class="badge badge-success p-2"><i class="fas fa-arrow-down mr-1"></i> Baixa Prioridade</span>';
                    } else if (prioridade === 'alta') {
                        badgePrioridade = '<span class="badge badge-danger p-2"><i class="fas fa-arrow-up mr-1"></i> Alta Prioridade</span>';
                    } else {
                        // Média é o padrão
                        badgePrioridade = '<span class="badge badge-warning p-2"><i class="fas fa-equals mr-1"></i> Média Prioridade</span>';
                    }
                    
                    // Garantir que usuarios_ids seja um array
                    let usuariosIds = [];
                    if (tarefa.usuarios_ids) {
                        if (Array.isArray(tarefa.usuarios_ids)) {
                            usuariosIds = tarefa.usuarios_ids;
                        } else if (typeof tarefa.usuarios_ids === 'string') {
                            try {
                                // Tentar processar de diferentes formas possíveis
                                if (tarefa.usuarios_ids.trim().startsWith('[')) {
                                    usuariosIds = JSON.parse(tarefa.usuarios_ids);
                                } else if (tarefa.usuarios_ids.includes(',')) {
                                    // Se for uma string separada por vírgulas
                                    usuariosIds = tarefa.usuarios_ids.split(',').map(id => id.trim());
                                } else {
                                    // Se for um único ID
                                    usuariosIds = [tarefa.usuarios_ids];
                                }
                            } catch (e) {
                                // Fallback - tentar usar como string simples
                                usuariosIds = [tarefa.usuarios_ids];
                            }
                        }
                    }
                    
                    // Serializar com segurança para o atributo data
                    const usuariosIdsJson = JSON.stringify(usuariosIds).replace(/'/g, '&#39;');
                    
                    const linha = `
                        <tr>
                            <td><strong>${tarefa.descricao || 'Sem descrição'}</strong></td>
                            <td class="text-center">${badgePrioridade}</td>
                            <td>
                                ${usuariosNomes === 'Não atribuído' 
                                    ? '<span class="text-muted"><i class="fas fa-user-slash mr-1"></i> Não atribuído</span>' 
                                    : '<i class="fas fa-users mr-1 text-info"></i> ' + usuariosNomes}
                            </td>
                            <td>
                                ${tarefa.proxima_data 
                                    ? `<span class="text-nowrap">
                                        <i class="far fa-calendar-check mr-1 text-success"></i> ${dataProxima}
                                      </span>` 
                                    : '<span class="text-muted"><i class="far fa-calendar-times mr-1"></i> Não definida</span>'}
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-primary btn-editar" 
                                    data-toggle="tooltip" title="Editar Tarefa"
                                    data-id="${tarefa.id}" 
                                    data-descricao="${tarefa.descricao || ''}" 
                                    data-status="${prioridade}"
                                    data-usuarios='${usuariosIdsJson}'>
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    
                    tbody.append(linha);
                });
                
                // Inicializar tooltips após adicionar os botões
                $('[data-toggle="tooltip"]').tooltip();
            }
            
            // Função para carregar as datas de uma tarefa
            function carregarDatas(id) {
                // Limpar todas as datas primeiro
                $('.data-mes').val('');
                
                // Mostrar indicador de carregamento
                const loadingHtml = '<i class="fas fa-spinner fa-spin"></i>';
                const camposDatas = $('.data-mes');
                const btnSalvar = $('#btn-salvar');
                
                btnSalvar.prop('disabled', true);
                camposDatas.prop('disabled', true);
                camposDatas.each(function() {
                    $(this).after('<span class="loading-date ml-2">' + loadingHtml + '</span>');
                });
                
                $.ajax({
                    url: "{{ route('rh.cronograma.datas', ['id' => ':id']) }}".replace(':id', id),
                    type: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.data && response.data.length === 0) {
                            }
                            
                            // Preencher as datas recebidas
                            $.each(response.data, function(key, value) {
                                const campo = $('#' + key);
                                if (campo.length) {
                                    campo.val(value);
                                    // Destacar campos com valores
                                    campo.addClass('bg-light').addClass('text-success');
                                }
                            });
                        } else {
                            toastr.error(response.error || 'Erro ao carregar datas');
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Erro ao carregar datas. Consulte o console para mais detalhes.');
                    },
                    complete: function() {
                        // Remover indicadores de carregamento
                        $('.loading-date').remove();
                        camposDatas.prop('disabled', false);
                        btnSalvar.prop('disabled', false);
                    }
                });
            }
            
            // Funções auxiliares
            function formatarData(data) {
                return moment(data).format('DD/MM/YYYY');
            }
            
            function obterNomeMes(numeroMes) {
                const meses = [
                    'janeiro', 'fevereiro', 'marco', 'abril', 'maio', 'junho',
                    'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'
                ];
                
                return meses[numeroMes - 1] || null;
            }
            
            // Evento para limpar todos os usuários
            $('#limpar-usuarios').click(function() {
                // Limpar a seleção do Select2
                $('#usuario_id').val(null).trigger('change');
                
                // Mostrar alerta de feedback
                Swal.fire({
                    icon: 'info',
                    title: 'Usuários removidos',
                    text: 'Todos os usuários foram removidos da tarefa',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
            
            // Controle de visibilidade do iframe de referência
            $('#toggle-iframe').click(function() {
                const container = $('#iframe-container');
                const icon = $(this).find('i');
                
                if (container.hasClass('show')) {
                    container.removeClass('show');
                    icon.removeClass('fa-minus').addClass('fa-plus');
                    container.empty(); // Remove o iframe para liberar memória
                } else {
                    container.addClass('show');
                    icon.removeClass('fa-plus').addClass('fa-minus');
                    
                    // Criar o iframe apenas quando necessário
                    if (container.children('iframe').length === 0) {
                        container.html('<iframe src="/rh/administrador" width="100%" height="600" frameborder="0"></iframe>');
                    }
                }
            });
            
            // Adicionar evento para o botão de criação de nova tarefa
            $('#btn-criar-tarefa').click(function() {
                // Limpar o formulário
                $('#form-criar-tarefa')[0].reset();
                
                // Resetar os botões de prioridade (média como padrão)
                $('.btn-group-toggle .btn').removeClass('active');
                $('input[name="status"]').prop('checked', false);
                $('input[name="status"][value="media"]').prop('checked', true);
                $('input[name="status"][value="media"]').parent().addClass('active');
                
                // Limpar select2
                $('#novo_usuario_id').val(null).trigger('change');
                
                // Abrir o modal
                $('#criarTarefaModal').modal('show');
            });
            
            // Botão Criar no modal
            $('#btn-criar').click(function() {
                // Verificar campos necessários
                const novaTarefa = $('#nova_tarefa').val();
                const status = $('input[name="status"]:checked').val() || 'media';
                
                if (!novaTarefa) {
                    toastr.error('Por favor, informe um nome para a tarefa');
                    return;
                }
                
                // Mostrar loading no botão
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Criando...');
                
                // Montar dados para envio
                const formData = new FormData();
                formData.append('nova_tarefa', novaTarefa);
                formData.append('status', status);
                
                // Adicionar usuários selecionados
                const usuariosSelecionados = $('#novo_usuario_id').val();
                if (usuariosSelecionados && usuariosSelecionados.length > 0) {
                    usuariosSelecionados.forEach(function(id) {
                        formData.append('novo_usuario_id[]', id);
                    });
                } else {
                    // Informar que não há usuários selecionados
                    formData.append('sem_usuarios', 'true');
                }
                
                // Enviar requisição
                $.ajax({
                    url: "{{ route('rh.cronograma.store') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message || 'Tarefa criada com sucesso!');
                            $('#criarTarefaModal').modal('hide');
                            carregarTarefas();
                        } else {
                            toastr.error(response.error || 'Erro ao criar tarefa');
                        }
                    },
                    error: function(xhr) {
                        const errors = xhr.responseJSON?.errors;
                        if (errors) {
                            Object.keys(errors).forEach(key => {
                                toastr.error(errors[key][0]);
                            });
                        } else {
                            toastr.error('Erro ao criar tarefa: ' + (xhr.responseJSON?.message || xhr.statusText));
                        }
                    },
                    complete: function() {
                        // Restaurar estado do botão
                        $('#btn-criar').prop('disabled', false).html('Criar Tarefa');
                    }
                });
            });
            
            // Handler para o botão limpar usuários no modal de criação
            $('#limpar-novos-usuarios').click(function() {
                $('#novo_usuario_id').val(null).trigger('change');
                
                // Mostrar alerta de feedback
                Swal.fire({
                    icon: 'info',
                    title: 'Usuários removidos',
                    text: 'Todos os usuários foram removidos da tarefa',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
            });
        });
    </script>
@stop 