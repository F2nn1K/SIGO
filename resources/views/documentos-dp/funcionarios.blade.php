@extends('adminlte::page')

@section('title', 'Funcionários - Documentos DP')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-users text-primary mr-3"></i>
            Funcionários
        </h1>
        <p class="text-muted mt-1 mb-0">Consulte e gerencie documentos de funcionários</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Card de Busca -->
    <div id="area_busca" class="mb-4">
        <div class="modern-card mb-4">
            <div class="card-header-modern">
                <h3 class="card-title-modern">
                    <i class="fas fa-search mr-2 text-primary"></i>
                    Pesquisar Funcionário
                </h3>
            </div>
            <div class="card-body-modern">
                <div class="row">
                    <div class="col-lg-6">
                        <div class="modern-search-container">
                            <label for="busca_nome" class="font-weight-bold text-muted mb-2">
                                <i class="fas fa-user-search mr-1"></i>
                                Nome do Funcionário
                            </label>
                            <input 
                                type="text" 
                                class="form-control modern-search-input" 
                                id="busca_nome" 
                                placeholder="Digite pelo menos 3 letras do nome para buscar..."
                            >
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex align-items-end">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle mr-1"></i>
                            A busca será realizada automaticamente conforme você digita
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resultados da Busca -->
        <div id="resultado_busca" class="mb-4"></div>
    </div>

    <!-- Dados do Funcionário Selecionado -->
    <div id="dados_funcionario" class="d-none">
        <!-- Header com Voltar -->
        <div class="modern-card mb-4">
            <div class="card-header-modern d-flex justify-content-between align-items-center">
                <h3 class="card-title-modern mb-0">
                    <i class="fas fa-user-circle mr-2 text-primary"></i>
                    <span id="funcionario_nome_header">Funcionário</span>
                </h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-voltar-busca">
                    <i class="fas fa-arrow-left mr-1"></i>
                    Voltar
                </button>
            </div>
        </div>

        <!-- Navegação por Abas -->
        <div class="modern-card mb-4">
            <div class="card-body-modern p-0">
                <ul class="nav nav-tabs" id="funcionario-tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active" id="dados-tab" data-toggle="tab" href="#dados-content" role="tab">
                            <i class="fas fa-user mr-2"></i>
                            Funcionário
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="atestados-tab" data-toggle="tab" href="#atestados-content" role="tab">
                            <i class="fas fa-file-medical mr-2"></i>
                            Atestados
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="advertencias-tab" data-toggle="tab" href="#advertencias-content" role="tab">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Advertências
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link" id="epi-tab" data-toggle="tab" href="#epi-content" role="tab">
                            <i class="fas fa-hard-hat mr-2"></i>
                            EPI
                        </a>
                    </li>
                </ul>

                <div class="tab-content p-4" id="funcionario-tab-content">
                    <!-- Aba Funcionário -->
                    <div class="tab-pane fade show active" id="dados-content" role="tabpanel">
                        <!-- Informações Básicas -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-info-circle mr-2 text-primary"></i>
                                    Informações Básicas
                                </h5>
                                <div class="table-responsive">
                                    <table class="modern-table">
                                        <tbody>
                                            <tr>
                                                <th style="width: 200px; font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-user mr-2"></i>Nome
                                                </th>
                                                <td id="f_nome" class="font-weight-500"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-id-card mr-2"></i>CPF
                                                </th>
                                                <td id="f_cpf"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-venus-mars mr-2"></i>Sexo
                                                </th>
                                                <td id="f_sexo"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-briefcase mr-2"></i>Função
                                                </th>
                                                <td id="f_funcao"></td>
                                            </tr>
                                            <tr>
                                                <th style="font-weight: 600; color: #64748b;">
                                                    <i class="fas fa-calendar mr-2"></i>Criado em
                                                </th>
                                                <td id="f_created"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-cogs mr-2 text-warning"></i>
                                    Alterar Status do Funcionário
                                </h5>
                                <div class="text-center">
                                    <div class="dropdown">
                                        <button class="btn btn-outline-secondary btn-md dropdown-toggle" type="button" id="dropdownStatusFuncionario" data-toggle="dropdown">
                                            <i class="fas fa-user-edit mr-2"></i>
                                            Alterar Status
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('trabalhando')">
                                                <i class="fas fa-user-check mr-2 text-success"></i>
                                                Readmitir / Ativar
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('afastado')">
                                                <i class="fas fa-user-clock mr-2 text-warning"></i>
                                                Afastar
                                            </a>
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('ferias')">
                                                <i class="fas fa-umbrella-beach mr-2 text-info"></i>
                                                Colocar em Férias
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#" onclick="alterarStatusFuncionario('demitido')">
                                                <i class="fas fa-user-times mr-2 text-danger"></i>
                                                Demitir
                                            </a>
                                        </div>
                                    </div>
                                    <small class="d-block text-muted mt-2">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Status atual: <span id="status-atual-funcionario"></span>
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Documentos -->
                        <h5 class="mb-3">
                            <i class="fas fa-file-alt mr-2 text-primary"></i>
                            Documentos Anexados
                        </h5>
                        <div id="lista_documentos" class="mb-4"></div>

                        <!-- Anexar Documentos -->
                        <h5 class="mb-3">
                            <i class="fas fa-plus-circle mr-2 text-success"></i>
                            Anexar Documento Faltante
                        </h5>
                        <form id="form-anexar" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <label for="tipo_documento" class="font-weight-bold text-muted mb-2">
                                        <i class="fas fa-list mr-1"></i>
                                        Tipo de Documento
                                    </label>
                                    <select id="tipo_documento" name="tipo_documento" class="form-control">
                                        @php
                                        $docs = [
                                            '02 fotos 3x4',
                                            'Carteira de saúde atualizada com foto 3x4',
                                            'Encaminhamento para exame admissional',
                                            'Antecedente cível e criminal',
                                            'R.G. (identidade)',
                                            'CPF',
                                            'CNH (carteira nacional de habilitação)',
                                            'Título Eleitoral',
                                            'Comprovante de endereço (com CEP)',
                                            'Carteira de trabalho, frente e verso',
                                            'Certidão de nascimento',
                                            'CPF filho',
                                            'Carteira de vacinação (menor 07 anos)',
                                            'Comprovante de frequência escolar (maior 07 anos)'
                                        ];
                                        @endphp
                                        @foreach($docs as $d)
                                            <option value="{{ $d }}">{{ $d }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <label for="arquivo" class="font-weight-bold text-muted mb-2">
                                        <i class="fas fa-file-upload mr-1"></i>
                                        Arquivo (PDF/JPG/PNG)
                                    </label>
                                    <input type="file" id="arquivo" name="arquivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                    <small class="text-muted mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Tamanho máximo: 15MB
                                    </small>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-success btn-lg px-4">
                                    <i class="fas fa-paperclip mr-2"></i>
                                    Anexar Documento
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Aba Atestados -->
                    <div class="tab-pane fade" id="atestados-content" role="tabpanel">
                        <!-- Lista de Atestados -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-file-medical mr-2 text-primary"></i>
                                    Atestados Anexados
                                </h5>
                                <div id="lista_atestados"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-success"></i>
                                    Adicionar Atestado
                                </h5>
                                <form id="form-atestado" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="tipo_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-medical-kit mr-1"></i>
                                            Tipo de Atestado
                                        </label>
                                        <select id="tipo_atestado" name="tipo_atestado" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <option value="Médico">Médico</option>
                                            <option value="Odontológico">Odontológico</option>
                                            <option value="Psicológico">Psicológico</option>
                                            <option value="Fisioterapia">Fisioterapia</option>
                                            <option value="Exame">Exame</option>
                                            <option value="Outros">Outros</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data do Atestado
                                        </label>
                                        <input type="date" id="data_atestado" name="data_atestado" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="dias_afastamento" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-clock mr-1"></i>
                                            Dias de Afastamento
                                        </label>
                                        <input type="number" id="dias_afastamento" name="dias_afastamento" class="form-control" min="0" placeholder="0 = sem afastamento">
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_atestado" name="observacoes" class="form-control" rows="2" placeholder="Observações adicionais..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_atestado" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-upload mr-1"></i>
                                            Arquivo do Atestado
                                        </label>
                                        <input type="file" id="arquivo_atestado" name="arquivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="text-muted mt-1">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            PDF, JPG ou PNG - Máx: 15MB
                                        </small>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-plus mr-2"></i>
                                            Anexar Atestado
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba Advertências -->
                    <div class="tab-pane fade" id="advertencias-content" role="tabpanel">
                        <!-- Lista de Advertências -->
                        <div class="row mb-4">
                            <div class="col-lg-8">
                                <h5 class="mb-3">
                                    <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
                                    Advertências Aplicadas
                                </h5>
                                <div id="lista_advertencias"></div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3">
                                    <i class="fas fa-plus-circle mr-2 text-danger"></i>
                                    Aplicar Advertência
                                </h5>
                                <form id="form-advertencia" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="tipo_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-gavel mr-1"></i>
                                            Tipo de Advertência
                                        </label>
                                        <select id="tipo_advertencia" name="tipo_advertencia" class="form-control" required>
                                            <option value="">Selecione...</option>
                                            <option value="verbal">Verbal</option>
                                            <option value="escrita">Escrita</option>
                                            <option value="suspensao">Suspensão</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="motivo_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-reason mr-1"></i>
                                            Motivo
                                        </label>
                                        <input type="text" id="motivo_advertencia" name="motivo" class="form-control" maxlength="500" placeholder="Motivo da advertência..." required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="data_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-calendar mr-1"></i>
                                            Data da Advertência
                                        </label>
                                        <input type="date" id="data_advertencia" name="data_advertencia" class="form-control" required>
                                    </div>
                                    <div class="mb-3" id="dias_suspensao_group" style="display: none;">
                                        <label for="dias_suspensao" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-ban mr-1"></i>
                                            Dias de Suspensão
                                        </label>
                                        <input type="number" id="dias_suspensao" name="dias_suspensao" class="form-control" min="1" max="30">
                                    </div>
                                    <div class="mb-3">
                                        <label for="observacoes_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-comment mr-1"></i>
                                            Observações
                                        </label>
                                        <textarea id="observacoes_advertencia" name="observacoes" class="form-control" rows="2" placeholder="Observações adicionais..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="arquivo_advertencia" class="font-weight-bold text-muted mb-2">
                                            <i class="fas fa-file-upload mr-1"></i>
                                            Documento da Advertência
                                        </label>
                                        <input type="file" id="arquivo_advertencia" name="arquivo" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                                        <small class="text-muted mt-1">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            PDF, JPG ou PNG - Máx: 15MB
                                        </small>
                                    </div>
                                    <div class="text-center">
                                        <button type="submit" class="btn btn-danger btn-block">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            Aplicar Advertência
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Aba EPI -->
                    <div class="tab-pane fade" id="epi-content" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">
                                <i class="fas fa-boxes mr-2 text-primary"></i>
                                Materiais Retirados
                            </h5>
                            <button class="btn btn-primary btn-sm" onclick="abrirModalCompleto()">
                                <i class="fas fa-table mr-2"></i>
                                Ver Histórico Completo
                            </button>
                        </div>
                        <div id="lista_epis"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes dos Materiais Retirados -->
<div class="modal fade" id="modalDetalhesMaterial" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesMaterialLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalDetalhesMaterialLabel">
                    <i class="fas fa-boxes mr-2"></i>
                    <span id="modal_titulo">Histórico de Materiais Retirados</span> - <span id="modal_funcionario_nome"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th width="25%">
                                    <i class="fas fa-box mr-1"></i>
                                    Produto
                                </th>
                                <th width="10%" class="text-center">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>
                                    Quantidade
                                </th>
                                <th width="18%">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Data/Hora
                                </th>
                                <th width="20%">
                                    <i class="fas fa-building mr-1"></i>
                                    Centro de Custo
                                </th>
                                <th width="15%">
                                    <i class="fas fa-user mr-1"></i>
                                    Entregue por
                                </th>
                                <th width="12%">
                                    <i class="fas fa-comment mr-1"></i>
                                    Observações
                                </th>
                            </tr>
                        </thead>
                        <tbody id="modal_tabela_materiais">
                            <!-- Dados serão inseridos via JavaScript -->
                        </tbody>
                    </table>
                </div>
                
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body py-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Total de retiradas: <span class="font-weight-bold" id="modal_total_retiradas">0</span>
                                </small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card bg-light border-0">
                            <div class="card-body py-2">
                                <small class="text-muted">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Período: <span class="font-weight-bold" id="modal_periodo"></span>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-2"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
/* Estilo personalizado para lista de resultados */
.search-result-item {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #f1f5f9;
    padding: 16px 20px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: fadeInUp 0.3s ease-out;
}

/* Hover removido conforme solicitado */

.search-result-content {
    flex-grow: 1;
}

.search-result-name {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.search-result-function {
    font-size: 14px;
    color: #64748b;
}

.document-count-badge {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    min-width: 30px;
    text-align: center;
}

/* Status badges removidos */

/* Estilo para lista de documentos */
.document-item-modern {
    background: #fff;
    border: 1px solid #f1f5f9;
    border-radius: 12px;
    padding: 16px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    text-decoration: none;
    color: #334155;
    display: block;
}

/* Hover removido conforme solicitado */

.document-title {
    font-size: 15px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
}

.document-meta {
    font-size: 13px;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.document-size {
    background: #f1f5f9;
    padding: 2px 6px;
    border-radius: 6px;
    font-size: 11px;
    color: #475569;
}

/* Animações */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mensagens de estado */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
}

.empty-state-icon {
    font-size: 48px;
    color: #94a3b8;
    margin-bottom: 16px;
}

.empty-state-text {
    font-size: 16px;
    color: #64748b;
    margin-bottom: 0;
}

/* Loading state */
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid #f3f3f3;
    border-top: 2px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
@stop

@section('js')
<script>
(function() {
    let funcionarioSelecionado = null;

    // Função para formatar data no padrão brasileiro (DD/MM/AAAA HH:MM)
    function formatarDataBR(dataISO) {
        if (!dataISO) return '—';
        const data = new Date(dataISO);
        if (isNaN(data.getTime())) return '—';
        
        const dia = String(data.getDate()).padStart(2, '0');
        const mes = String(data.getMonth() + 1).padStart(2, '0');
        const ano = data.getFullYear();
        const hora = String(data.getHours()).padStart(2, '0');
        const minuto = String(data.getMinutes()).padStart(2, '0');
        
        return `${dia}/${mes}/${ano} ${hora}:${minuto}`;
    }

// Função getStatusBadge removida

    function renderResultados(funcs){
        const box = document.getElementById('resultado_busca');
        box.innerHTML = '';
        
        if(!funcs || funcs.length === 0){
            box.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <p class="empty-state-text">Nenhum funcionário encontrado com este nome.</p>
                </div>
            `;
            return;
        }

        funcs.forEach((f, index) => {
            const item = document.createElement('div');
            item.className = 'search-result-item';
            item.style.animationDelay = `${index * 0.1}s`;
            item.innerHTML = `
                <div class="search-result-content">
                    <div class="search-result-name">
                        <i class="fas fa-user mr-2 text-primary"></i>
                        ${f.nome}
                    </div>
                    <div class="search-result-function">
                        <i class="fas fa-briefcase mr-1"></i>
                        ${f.funcao}
                    </div>
                </div>
                <div class="text-right">
                    <div class="document-count-badge">
                        ${f.total_documentos || 0} docs
                    </div>
                </div>
            `;
            item.addEventListener('click', () => selecionarFuncionario(f));
            box.appendChild(item);
        });
    }

    function selecionarFuncionario(f){
        funcionarioSelecionado = f;
        
        // Ocultar área de busca e mostrar dados do funcionário
        document.getElementById('area_busca').style.display = 'none';
        const dadosSection = document.getElementById('dados_funcionario');
        dadosSection.classList.remove('d-none');
        
        // Scroll para o topo
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Preencher dados
        document.getElementById('funcionario_nome_header').textContent = f.nome;
        document.getElementById('f_nome').innerHTML = `<strong>${f.nome}</strong>`;
        document.getElementById('f_cpf').textContent = formatarCPF(f.cpf);
        document.getElementById('f_sexo').textContent = f.sexo === 'M' ? 'Masculino' : 'Feminino';
        document.getElementById('f_funcao').textContent = f.funcao;
        document.getElementById('f_created').textContent = formatarDataBR(f.created_at);
        
        // Mostrar status atual
        const statusTexto = {
            'trabalhando': 'Trabalhando',
            'demitido': 'Demitido',
            'afastado': 'Afastado',
            'ferias': 'Em Férias'
        }[f.status] || f.status;
        document.getElementById('status-atual-funcionario').textContent = statusTexto;
        
        // Carregar documentos, atestados, advertências e EPIs
        carregarDocumentos(f.id);
        carregarAtestados(f.id);
        carregarAdvertencias(f.id);
        carregarEpis(f.id);
    }

    function voltarParaBusca(){
        // Ocultar dados do funcionário e mostrar área de busca
        document.getElementById('dados_funcionario').classList.add('d-none');
        document.getElementById('area_busca').style.display = 'block';
        
        // Limpar funcionário selecionado
        funcionarioSelecionado = null;
        
        // Scroll para o topo
        window.scrollTo({ top: 0, behavior: 'smooth' });
        
        // Focar no campo de busca
        document.getElementById('busca_nome').focus();
    }

    async function carregarDocumentos(id){
        const lista = document.getElementById('lista_documentos');
        
        // Loading state
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando documentos...
            </div>
        `;

        try {
            const res = await fetch(`{{ route('documentos-dp.documentos', ['id' => 'ID_PLACE']) }}`.replace('ID_PLACE', id));
            const data = await res.json();
            
            lista.innerHTML = '';
            
            if(!data.success || !data.documentos || data.documentos.length === 0){
                lista.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-times"></i>
                        </div>
                        <p class="empty-state-text">Nenhum documento anexado ainda.</p>
                    </div>
                `;
                return;
            }
            
            data.documentos.forEach((d, index) => {
                const link = document.createElement('a');
                link.className = 'document-item-modern';
                link.href = `{{ route('documentos-dp.arquivo', ['id' => 'ARQ_PLACE']) }}`.replace('ARQ_PLACE', d.id);
                link.target = '_blank';
                link.style.animationDelay = `${index * 0.1}s`;
                link.innerHTML = `
                    <div class="document-title">
                        <i class="fas fa-file-pdf mr-2 text-danger"></i>
                        ${d.tipo_documento}
                    </div>
                    <div class="document-meta">
                        <span>
                            <i class="fas fa-file mr-1"></i>
                            ${d.arquivo_nome}
                        </span>
                        <span class="document-size">
                            ${(d.arquivo_tamanho/1024).toFixed(1)} KB
                        </span>
                    </div>
                `;
                lista.appendChild(link);
            });
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar documentos. Tente novamente.
                </div>
            `;
        }
    }

    // Busca com loading state
    let timer = null;
    document.getElementById('busca_nome').addEventListener('input', function(){
        const v = this.value.trim();
        const resultBox = document.getElementById('resultado_busca');
        
        clearTimeout(timer);
        
        if(v.length < 3){
            resultBox.innerHTML = '';
            return;
        }

        // Loading state
        resultBox.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Buscando funcionários...
            </div>
        `;
        
        timer = setTimeout(async () => {
            try {
                const url = `{{ route('documentos-dp.buscar') }}?nome=${encodeURIComponent(v)}`;
                const res = await fetch(url);
                const data = await res.json();
                if(data.success){
                    renderResultados(data.funcionarios);
                }
            } catch (error) {
                resultBox.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        Erro na busca. Tente novamente.
                    </div>
                `;
            }
        }, 300);
    });

    // Anexar documento com feedback visual
    document.getElementById('form-anexar').addEventListener('submit', async function(e){
        e.preventDefault();
        if(!funcionarioSelecionado){ return; }
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        // Loading state no botão
        submitBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Anexando...';
        submitBtn.disabled = true;
        
        try {
            const fd = new FormData(this);
            const url = `{{ route('documentos-dp.anexar', ['id' => 'ID_FUNC']) }}`.replace('ID_FUNC', funcionarioSelecionado.id);
            const res = await fetch(url, { method: 'POST', body: fd });
            const data = await res.json();
            
            if(data.success){
                this.reset();
                carregarDocumentos(funcionarioSelecionado.id);
                
                // Feedback de sucesso
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Anexado!';
                submitBtn.className = 'btn btn-success btn-lg px-4';
                
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Falha ao anexar');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: error.message,
                confirmButtonColor: '#3085d6'
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Evento do botão "Voltar"
    document.getElementById('btn-voltar-busca').addEventListener('click', voltarParaBusca);

    // ========================================
    // FUNÇÕES PARA ATESTADOS
    // ========================================
    
    async function carregarAtestados(funcionarioId) {
        const lista = document.getElementById('lista_atestados');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando atestados...
            </div>
        `;

        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/atestados`);
            const data = await response.json();
            
            lista.innerHTML = '';
            
            if(!data.success || !data.atestados || data.atestados.length === 0){
                lista.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <p class="empty-state-text">Nenhum atestado anexado ainda.</p>
                    </div>
                `;
                return;
            }
            
            data.atestados.forEach((atestado, index) => {
                const link = document.createElement('div');
                link.className = 'document-item-modern';
                link.style.animationDelay = `${index * 0.1}s`;
                link.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="document-title">
                            <i class="fas fa-file-medical mr-2 text-primary"></i>
                            ${atestado.tipo_atestado}
                        </div>
                    </div>
                    <div class="document-meta">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-calendar mr-1"></i>
                                Data: ${formatarDataBR(atestado.data_atestado)}
                            </small>
                            ${atestado.dias_afastamento ? `<br><small class="text-muted"><i class="fas fa-clock mr-1"></i>Afastamento: ${atestado.dias_afastamento} dias</small>` : ''}
                        </div>
                        <div class="text-right">
                            <a href="/documentos-dp/atestado/${atestado.id}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            <span class="document-size ml-2">
                                ${(atestado.arquivo_tamanho/1024).toFixed(1)} KB
                            </span>
                        </div>
                    </div>
                    ${atestado.observacoes ? `<div class="mt-2"><small class="text-muted"><strong>Obs:</strong> ${atestado.observacoes}</small></div>` : ''}
                `;
                lista.appendChild(link);
            });
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar atestados. Tente novamente.
                </div>
            `;
        }
    }

    // ========================================
    // FUNÇÕES PARA ADVERTÊNCIAS
    // ========================================
    
    async function carregarAdvertencias(funcionarioId) {
        const lista = document.getElementById('lista_advertencias');
        
        lista.innerHTML = `
            <div class="text-center py-4">
                <div class="loading-spinner mr-2"></div>
                Carregando advertências...
            </div>
        `;

        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/advertencias`);
            const data = await response.json();
            
            lista.innerHTML = '';
            
            if(!data.success || !data.advertencias || data.advertencias.length === 0){
                lista.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        <p class="empty-state-text">Nenhuma advertência aplicada.</p>
                    </div>
                `;
                return;
            }
            
            data.advertencias.forEach((advertencia, index) => {
                const tipoClass = {
                    'verbal': 'info',
                    'escrita': 'warning', 
                    'suspensao': 'danger'
                }[advertencia.tipo_advertencia] || 'secondary';

                const link = document.createElement('div');
                link.className = 'document-item-modern';
                link.style.animationDelay = `${index * 0.1}s`;
                link.innerHTML = `
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="document-title">
                            <i class="fas fa-exclamation-triangle mr-2 text-${tipoClass}"></i>
                            ${advertencia.tipo_advertencia.charAt(0).toUpperCase() + advertencia.tipo_advertencia.slice(1)}
                        </div>
                    </div>
                    <div class="mb-2">
                        <strong>Motivo:</strong> ${advertencia.motivo}
                    </div>
                    <div class="document-meta">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-calendar mr-1"></i>
                                Data: ${formatarDataBR(advertencia.data_advertencia)}
                            </small>
                            ${advertencia.dias_suspensao ? `<br><small class="text-muted"><i class="fas fa-ban mr-1"></i>Suspensão: ${advertencia.dias_suspensao} dias</small>` : ''}
                        </div>
                        <div class="text-right">
                            <a href="/documentos-dp/advertencia/${advertencia.id}" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-eye mr-1"></i>Ver
                            </a>
                            <span class="document-size ml-2">
                                ${(advertencia.arquivo_tamanho/1024).toFixed(1)} KB
                            </span>
                        </div>
                    </div>
                    ${advertencia.observacoes ? `<div class="mt-2"><small class="text-muted"><strong>Obs:</strong> ${advertencia.observacoes}</small></div>` : ''}
                `;
                lista.appendChild(link);
            });
        } catch (error) {
            lista.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Erro ao carregar advertências. Tente novamente.
                </div>
            `;
        }
    }

    // ========================================
    // EVENTOS DOS FORMULÁRIOS
    // ========================================
    
    // Mostrar/ocultar campo de dias de suspensão
    document.getElementById('tipo_advertencia').addEventListener('change', function() {
        const diasSuspensaoGroup = document.getElementById('dias_suspensao_group');
        if (this.value === 'suspensao') {
            diasSuspensaoGroup.style.display = 'block';
            document.getElementById('dias_suspensao').required = true;
        } else {
            diasSuspensaoGroup.style.display = 'none';
            document.getElementById('dias_suspensao').required = false;
        }
    });

    // Envio do formulário de atestado
    document.getElementById('form-atestado').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Enviando...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/atestados`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarAtestados(funcionarioSelecionado.id);
                
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Anexado!';
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Erro ao anexar atestado');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro: ' + error.message,
                confirmButtonColor: '#3085d6'
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Envio do formulário de advertência
    document.getElementById('form-advertencia').addEventListener('submit', async function(e) {
        e.preventDefault();
        if (!funcionarioSelecionado) return;
        
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        try {
            submitBtn.innerHTML = '<div class="loading-spinner mr-2"></div>Enviando...';
            submitBtn.disabled = true;
            
            const formData = new FormData(this);
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/advertencias`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.reset();
                carregarAdvertencias(funcionarioSelecionado.id);
                
                submitBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Aplicada!';
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            } else {
                throw new Error(data.message || 'Erro ao aplicar advertência');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro: ' + error.message,
                confirmButtonColor: '#3085d6'
            });
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }
    });

    // Função para alterar status do funcionário
    window.alterarStatusFuncionario = async function(novoStatus) {
        if (!funcionarioSelecionado) {
            Swal.fire({
                icon: 'warning',
                title: 'Atenção!',
                text: 'Nenhum funcionário selecionado.',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        const statusTextos = {
            'trabalhando': 'readmitir/ativar',
            'demitido': 'demitir',
            'afastado': 'afastar',
            'ferias': 'colocar em férias'
        };

        const acao = statusTextos[novoStatus] || 'alterar status de';
        
        // Confirmação com SweetAlert
        const result = await Swal.fire({
            title: 'Confirmar Alteração',
            text: `Tem certeza que deseja ${acao} ${funcionarioSelecionado.nome}?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sim, alterar!',
            cancelButtonText: 'Cancelar'
        });
        
        if (!result.isConfirmed) return;

        const btnDropdown = document.getElementById('dropdownStatusFuncionario');
        const originalText = btnDropdown.innerHTML;
        
        try {
            btnDropdown.innerHTML = '<div class="loading-spinner mr-2"></div>Alterando...';
            btnDropdown.disabled = true;
            
            // Usar a mesma rota de demitir, mas generalizada
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/alterar-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ status: novoStatus })
            });
            
            const data = await response.json();
            
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso!',
                    text: `Status alterado para "${novoStatus}" com sucesso!`,
                    confirmButtonColor: '#3085d6',
                    timer: 3000,
                    timerProgressBar: true
                });
                
                // Atualizar status local e exibição
                funcionarioSelecionado.status = novoStatus;
                const statusTexto = {
                    'trabalhando': 'Trabalhando',
                    'demitido': 'Demitido',
                    'afastado': 'Afastado',
                    'ferias': 'Em Férias'
                }[novoStatus] || novoStatus;
                document.getElementById('status-atual-funcionario').textContent = statusTexto;
                
                btnDropdown.innerHTML = originalText;
                btnDropdown.disabled = false;
            } else {
                throw new Error(data.message || 'Erro ao alterar status');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: error.message,
                confirmButtonColor: '#3085d6'
            });
            btnDropdown.innerHTML = originalText;
            btnDropdown.disabled = false;
        }
    };

    // Função para carregar materiais retirados pelo funcionário
    window.carregarEpis = async function(funcionarioId) {
        try {
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioId}/epis`);
            const data = await response.json();
            
            const listaEpis = document.getElementById('lista_epis');
            
            if (data.length === 0) {
                listaEpis.innerHTML = '<div class="empty-state"><i class="fas fa-boxes fa-3x text-muted mb-3"></i><p class="text-muted">Nenhum material retirado ainda</p></div>';
                return;
            }
            
            let html = '';
            data.forEach(function(lancamento) {
                const dataRetirada = formatarDataBR(lancamento.data_baixa);
                
                // Criar lista de produtos do lançamento
                let produtosList = '';
                lancamento.produtos.forEach(function(produto, index) {
                    if (index > 0) produtosList += ', ';
                    produtosList += `${produto.produto_nome} (${produto.quantidade})`;
                });
                
                html += `
                    <div class="document-item-modern mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">
                                    <i class="fas fa-boxes mr-2 text-primary"></i>
                                    Lançamento - ${lancamento.produtos.length} produto(s)
                                </h6>
                                <div class="small text-muted mb-2">
                                    <div><i class="fas fa-calendar mr-1"></i>Data: ${dataRetirada}</div>
                                    <div><i class="fas fa-box mr-1"></i>Produtos: ${produtosList}</div>
                                    <div><i class="fas fa-sort-numeric-up mr-1"></i>Total: ${lancamento.total_quantidade} item(s)</div>
                                    <div><i class="fas fa-building mr-1"></i>Centro de Custo: ${lancamento.centro_custo_nome || 'Não informado'}</div>
                                    <div><i class="fas fa-user mr-1"></i>Entregue por: ${lancamento.usuario_entrega || 'Não informado'}</div>
                                    ${lancamento.observacoes ? `<div><i class="fas fa-comment mr-1"></i>Obs: ${lancamento.observacoes}</div>` : ''}
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="mb-2">
                                    <span class="badge badge-success">${lancamento.produtos.length}</span>
                                    <br><small class="text-muted">produtos</small>
                                </div>
                                <button class="btn btn-outline-primary btn-sm" onclick="abrirModalLancamento(${lancamento.id})">
                                    <i class="fas fa-eye mr-1"></i>Ver Lançamento
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            listaEpis.innerHTML = html;
        } catch (error) {
            document.getElementById('lista_epis').innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i>Erro ao carregar materiais</div>';
        }
    };

    // Função para abrir modal completo com todos os materiais
    window.abrirModalCompleto = async function() {
        if (!funcionarioSelecionado) {
            return;
        }
        
        try {
            // Buscar todos os materiais do funcionário
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/epis`);
            const materiais = await response.json();
            
                    // Preencher nome do funcionário no título
        document.getElementById('modal_titulo').textContent = 'Histórico de Materiais Retirados';
        document.getElementById('modal_funcionario_nome').textContent = funcionarioSelecionado.nome;
            
            // Preencher tabela
            const tbody = document.getElementById('modal_tabela_materiais');
            
            if (materiais.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <br>
                            Nenhum material retirado
                        </td>
                    </tr>
                `;
            } else {
                let html = '';
                materiais.forEach(function(lancamento) {
                    const dataRetirada = formatarDataBR(lancamento.data_baixa);
                    
                    // Primeira linha do lançamento
                    const primeiroproduto = lancamento.produtos[0];
                    const rowspan = lancamento.produtos.length;
                    
                    html += `
                        <tr>
                            <td>${primeiroproduto.produto_nome || 'Produto não identificado'}</td>
                            <td class="text-center">
                                <span class="badge badge-primary">${primeiroproduto.quantidade}</span>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                <small class="text-muted">${dataRetirada}</small>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                <small>${lancamento.centro_custo_nome || 'Não informado'}</small>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                <small>${lancamento.usuario_entrega || 'Não informado'}</small>
                            </td>
                            <td rowspan="${rowspan}" class="align-middle">
                                ${lancamento.observacoes ? `<small class="text-info">${lancamento.observacoes}</small>` : '<small class="text-muted">-</small>'}
                            </td>
                        </tr>
                    `;
                    
                    // Linhas adicionais para outros produtos do mesmo lançamento
                    for (let i = 1; i < lancamento.produtos.length; i++) {
                        const produto = lancamento.produtos[i];
                        html += `
                            <tr>
                                <td>${produto.produto_nome || 'Produto não identificado'}</td>
                                <td class="text-center">
                                    <span class="badge badge-primary">${produto.quantidade}</span>
                                </td>
                            </tr>
                        `;
                    }
                });
                tbody.innerHTML = html;
            }
            
            // Atualizar estatísticas
            document.getElementById('modal_total_retiradas').textContent = materiais.length;
            
            // Calcular período
            if (materiais.length > 0) {
                const datas = materiais.map(m => new Date(m.data_baixa)).sort();
                const primeira = formatarDataBR(datas[0].toISOString());
                const ultima = formatarDataBR(datas[datas.length - 1].toISOString());
                document.getElementById('modal_periodo').textContent = primeira === ultima ? primeira : `${primeira} a ${ultima}`;
            } else {
                document.getElementById('modal_periodo').textContent = 'Nenhuma retirada';
            }
            
            // Abrir modal
            $('#modalDetalhesMaterial').modal('show');
            
        } catch (error) {
            console.error('Erro ao carregar dados para modal:', error);
        }
    };

    // Função para abrir modal com lançamento específico
    window.abrirModalLancamento = async function(lancamentoId) {
        if (!funcionarioSelecionado) {
            return;
        }
        
        try {
            // Buscar todos os materiais e filtrar pelo lançamento
            const response = await fetch(`/documentos-dp/funcionario/${funcionarioSelecionado.id}/epis`);
            const todosLancamentos = await response.json();
            
            // Encontrar o lançamento específico
            const lancamentoSelecionado = todosLancamentos.find(l => l.id == lancamentoId);
            
            if (!lancamentoSelecionado) {
                return;
            }
            
            // Preencher nome do funcionário no título
            document.getElementById('modal_titulo').textContent = 'Detalhes do Lançamento';
            document.getElementById('modal_funcionario_nome').textContent = funcionarioSelecionado.nome;
            
            // Preencher tabela com apenas os produtos do lançamento selecionado
            const tbody = document.getElementById('modal_tabela_materiais');
            const dataRetirada = formatarDataBR(lancamentoSelecionado.data_baixa);
            
            let html = '';
            lancamentoSelecionado.produtos.forEach(function(produto) {
                html += `
                    <tr>
                        <td>
                            <strong>${produto.produto_nome || 'Produto não identificado'}</strong>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-primary">${produto.quantidade}</span>
                        </td>
                        <td>
                            <small class="text-muted">${dataRetirada}</small>
                        </td>
                        <td>
                            <small>${lancamentoSelecionado.centro_custo_nome || 'Não informado'}</small>
                        </td>
                        <td>
                            <small>${lancamentoSelecionado.usuario_entrega || 'Não informado'}</small>
                        </td>
                        <td>
                            ${lancamentoSelecionado.observacoes ? `<small class="text-info">${lancamentoSelecionado.observacoes}</small>` : '<small class="text-muted">-</small>'}
                        </td>
                    </tr>
                `;
            });
            
            tbody.innerHTML = html;
            
            // Atualizar estatísticas para este lançamento específico
            document.getElementById('modal_total_retiradas').textContent = `1 lançamento (${lancamentoSelecionado.produtos.length} produtos)`;
            document.getElementById('modal_periodo').textContent = dataRetirada;
            
            // Abrir modal
            $('#modalDetalhesMaterial').modal('show');
            
        } catch (error) {
            console.error('Erro ao carregar dados do lançamento:', error);
        }
    };

    // Função para formatar CPF
    function formatarCPF(cpf) {
        if (!cpf) return '';
        
        // Remove tudo que não é dígito
        const numeros = cpf.replace(/\D/g, '');
        
        // Aplica a máscara
        return numeros.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }
})();
</script>
@stop


