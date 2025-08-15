{{--
    CONTROLE DE ESTOQUE - BRS SISTEMA
    
    TODO: Implementações necessárias no Controller:
    1. Criar ControleEstoqueController
    2. Implementar métodos para:
       - Contagem de produtos em estoque ($totalProdutos)
       - Entradas do mês ($entradasMes) 
       - Saídas do mês ($saidasMes)
       - Produtos em falta ($produtosFalta)
       - Cálculos de tendência ($tendenciaProdutos, $tendenciaEntradas, etc.)
    
    TODO: Banco de dados necessário:
    - Tabela 'produtos' (id, nome, codigo, categoria, estoque_atual, estoque_minimo)
    - Tabela 'movimentacoes' (id, produto_id, tipo [entrada/saida], quantidade, data)
    - Relacionamentos apropriados
--}}

@extends('adminlte::page')

@section('title', 'Controle de Estoque')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-boxes text-primary mr-3"></i>
            Controle de Estoque
        </h1>
        <p class="text-muted mt-1 mb-0">Gerencie seu inventário de forma inteligente</p>
    </div>
    <div>
        <!-- Botão Novo Produto comentado temporariamente -->
        <!--
        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalCadastrarProduto">
            <i class="fas fa-plus mr-1"></i>
            Novo Produto
        </button>
        -->
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Alertas modernos -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show modern-alert">
        <i class="fas fa-check-circle mr-2"></i>
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show modern-alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Cards de estatísticas modernas -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">
                    <i class="fas fa-cubes"></i>
                </div>
                <div class="stat-content">
                    {{-- TODO: Implementar contagem real de produtos em estoque --}}
                    <div class="stat-number">{{ $totalProdutos ?? '--' }}</div>
                    <div class="stat-label">Produtos em Estoque</div>
                </div>
                <div class="stat-trend">
                    {{-- TODO: Implementar cálculo de tendência --}}
                    <i class="fas fa-circle text-muted"></i>
                    <span class="text-muted">{{ $tendenciaProdutos ?? '--' }}</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-card-success">
                <div class="stat-icon">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-content">
                    {{-- TODO: Implementar contagem de entradas do mês atual --}}
                    <div class="stat-number">{{ $entradasMes ?? '--' }}</div>
                    <div class="stat-label">Entradas do Mês</div>
                </div>
                <div class="stat-trend">
                    {{-- TODO: Implementar comparação com mês anterior --}}
                    <i class="fas fa-circle text-muted"></i>
                    <span class="text-muted">{{ $tendenciaEntradas ?? '--' }}</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-content">
                    {{-- TODO: Implementar contagem de saídas do mês atual --}}
                    <div class="stat-number">{{ $saidasMes ?? '--' }}</div>
                    <div class="stat-label">Saídas do Mês</div>
                </div>
                <div class="stat-trend">
                    {{-- TODO: Implementar comparação com mês anterior --}}
                    <i class="fas fa-circle text-muted"></i>
                    <span class="text-muted">{{ $tendenciaSaidas ?? '--' }}</span>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="stat-card stat-card-danger" id="cardProdutosFalta" style="cursor: pointer;" data-toggle="modal" data-target="#modalProdutosFalta">
                <div class="stat-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-content">
                    <div class="stat-number">{{ $produtosFalta ?? '0' }}</div>
                    <div class="stat-label">Produtos em Falta</div>
                </div>
                <div class="stat-trend">
                    <i class="fas fa-eye text-info"></i>
                    <span class="text-info">Clique para ver detalhes</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Seção de ações -->
    <div class="row justify-content-center">
        <div class="col-lg-10 mb-4">
            <div class="modern-card">
                <div class="card-header-modern">
                    <h5 class="card-title-modern">
                        <i class="fas fa-bolt text-warning mr-2"></i>
                        Ações Rápidas
                    </h5>
                </div>
                <div class="card-body-modern">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="action-btn action-btn-primary w-100" data-toggle="modal" data-target="#modalCadastrarProduto">
                                <div class="action-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <div class="action-content">
                                    <div class="action-title">Cadastrar Produto</div>
                                    <div class="action-desc">Adicionar novo item</div>
                                </div>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="action-btn action-btn-success w-100" data-toggle="modal" data-target="#modalRegistrarEntrada">
                                <div class="action-icon">
                                    <i class="fas fa-arrow-up"></i>
                                </div>
                                <div class="action-content">
                                    <div class="action-title">Registrar Entrada</div>
                                    <div class="action-desc">Adicionar estoque</div>
                                </div>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="action-btn action-btn-warning w-100" data-toggle="modal" data-target="#modalRegistrarSaida">
                                <div class="action-icon">
                                    <i class="fas fa-arrow-down"></i>
                                </div>
                                <div class="action-content">
                                    <div class="action-title">Registrar Saída</div>
                                    <div class="action-desc">Retirar do estoque</div>
                                </div>
                            </button>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <button class="action-btn action-btn-info w-100" data-toggle="modal" data-target="#modalConsultarProduto">
                                <div class="action-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <div class="action-content">
                                    <div class="action-title">Consultar Produto</div>
                                    <div class="action-desc">Buscar e editar</div>
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@stop

<!-- Modal de Registro de Saída -->
<div class="modal fade" id="modalRegistrarSaida" tabindex="-1" role="dialog" aria-labelledby="modalRegistrarSaidaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title" id="modalRegistrarSaidaLabel">
                    <i class="fas fa-arrow-down mr-2"></i>
                    Registrar Saída de Estoque
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formRegistrarSaida">
                <div class="modal-body">
                    <!-- Seleção de Funcionário -->
                    <div class="form-group">
                        <label for="funcionario" class="font-weight-bold">
                            <i class="fas fa-user mr-1"></i>
                            Funcionário
                        </label>
                        <div class="funcionario-search-container">
                            <input type="text" class="form-control" id="funcionario" name="funcionario_search" 
                                   placeholder="Digite pelo menos 3 letras para buscar..." autocomplete="off" required>
                            <input type="hidden" id="funcionario_id" name="funcionario_id">
                            <div id="funcionarioResults" class="search-results"></div>
                        </div>
                        <small class="text-muted">Digite o nome do funcionário que receberá os produtos</small>
                    </div>

                    <!-- Seleção de Centro de Custo -->
                    <div class="form-group">
                        <label for="centro-custo" class="font-weight-bold">
                            <i class="fas fa-building mr-1"></i>
                            Centro de Custo <span class="text-danger">*</span>
                        </label>
                        <div class="centro-custo-search-container">
                            <input type="text" class="form-control" id="centro-custo" name="centro_custo_search" 
                                   placeholder="Digite pelo menos 3 letras para buscar..." autocomplete="off" required>
                            <input type="hidden" id="cc" name="centro_custo_id">
                            <div id="centroCustoResults" class="centro-custo-results"></div>
                        </div>
                        <small class="text-muted">Digite o nome do centro de custo</small>
                    </div>

                    <!-- Informações do Funcionário -->
                    <div id="infoFuncionario" class="alert alert-info" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nome:</strong> <span id="funcionarioNome"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Função:</strong> <span id="funcionarioFuncao"></span>
                            </div>
                            <div class="col-md-12 mt-2">
                                <strong>CPF:</strong> <span id="funcionarioCpf"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Produtos -->
                    <div class="form-group">
                        <label class="font-weight-bold">
                            <i class="fas fa-boxes mr-1"></i>
                            Produtos a Entregar
                        </label>
                        <div id="listaProdutos">
                            <!-- Primeiro produto -->
                            <div class="produto-item border rounded p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Produto</label>
                                        <div class="produto-search-container">
                                            <input type="text" class="form-control produto-search" placeholder="Digite o nome do produto..." autocomplete="off" required>
                                            <input type="hidden" class="produto-id" name="produtos[0][produto_id]" required>
                                            <div class="produto-results"></div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label>Quantidade</label>
                                        <input type="number" class="form-control quantidade-input" name="produtos[0][quantidade]" min="1" required>
                                    </div>
                                    <div class="col-md-2 d-flex align-items-end">
                                        <button type="button" class="btn btn-danger btn-sm remover-produto" style="display: none;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-12">
                                        <small class="estoque-info text-muted"></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="adicionarProduto">
                            <i class="fas fa-plus mr-1"></i> Adicionar Produto
                        </button>
                    </div>

                    <!-- Observações -->
                    <div class="form-group">
                        <label for="observacoes" class="font-weight-bold">
                            <i class="fas fa-comment mr-1"></i>
                            Observações (opcional)
                        </label>
                        <textarea class="form-control" id="observacoes" name="observacoes" rows="3" 
                                placeholder="Digite observações sobre esta saída..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Registrar Saída
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Produtos em Falta -->
<div class="modal fade" id="modalProdutosFalta" tabindex="-1" role="dialog" aria-labelledby="modalProdutosFaltaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalProdutosFaltaLabel">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Produtos em Falta no Estoque
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="loadingProdutosFalta" class="text-center p-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <p class="mt-2 text-muted">Carregando produtos em falta...</p>
                </div>
                
                <div id="contentProdutosFalta" style="display: none;">
                    <!-- Alert compacto -->
                    <div class="alert alert-warning alert-compact mb-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i>
                            <div>
                                <strong>Atenção:</strong> <span id="totalProdutosFalta">0</span> produtos com estoque zerado
                                <small class="d-block text-muted mt-1" id="ultimaAtualizacao">Atualizado agora</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tabela moderna -->
                    <div class="table-container">
                        <div class="table-responsive">
                            <table class="table table-hover modern-table">
                                <thead class="table-dark">
                                    <tr>
                                        <th scope="col" class="text-center compact-header" style="width: 60px;">ID</th>
                                        <th scope="col" class="compact-header" style="width: 50%;">Produto</th>
                                        <th scope="col" class="compact-header" style="width: 25%;">Descrição</th>
                                        <th scope="col" class="text-center compact-header" style="width: 60px;">Qtd</th>
                                        <th scope="col" class="text-center compact-header" style="width: 110px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody id="tabelaProdutosFalta">
                                    <!-- Produtos serão carregados via JavaScript -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Footer compacto -->
                    <div class="modal-footer-compact">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Produtos com estoque zerado • <span id="modalGeneratedTime"></span>
                        </small>
                    </div>
                    
                    <!-- Área oculta para impressão (não será exibida no modal) -->
                    <div class="print-area" style="display: none;">
                        <div class="print-header">
                            <div class="print-title">Relatório de Produtos em Falta</div>
                            <div class="print-subtitle">Sistema de Controle de Estoque</div>
                            <div class="print-date" id="printDate"></div>
                        </div>
                        
                        <table class="print-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome do Produto</th>
                                    <th>Descrição</th>
                                    <th>Qtd</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="printTableBody">
                                <!-- Produtos para impressão serão copiados aqui -->
                            </tbody>
                        </table>
                        
                        <div class="print-footer">
                            Relatório gerado automaticamente • Produtos com estoque zerado que necessitam reposição urgente
                        </div>
                    </div>
                </div>
                
                <div id="noProdutosFalta" style="display: none;">
                    <div class="alert alert-success text-center">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h5>Parabéns! 🎉</h5>
                        <p class="mb-0">Todos os produtos têm estoque disponível.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Fechar
                </button>
                <button type="button" class="btn btn-warning" id="btnImprimirProdutosFalta">
                    <i class="fas fa-print mr-1"></i> Imprimir Lista
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Consultar Produto -->
<div class="modal fade" id="modalConsultarProduto" tabindex="-1" role="dialog" aria-labelledby="modalConsultarProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalConsultarProdutoLabel">
                    <i class="fas fa-search mr-2"></i>
                    Consultar Produto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Busca de produto -->
                <div class="search-section mb-4">
                    <label for="buscaProduto" class="form-label font-weight-bold">
                        <i class="fas fa-search mr-1"></i>
                        Buscar Produto
                    </label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="buscaProduto" placeholder="Digite o nome do produto para buscar..." autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-info" type="button" id="btnBuscarProduto">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <small class="form-text text-muted">Digite pelo menos 3 caracteres para buscar</small>
                </div>

                <!-- Resultados da busca -->
                <div id="resultadosBusca" style="display: none;">
                    <h6 class="mb-3">
                        <i class="fas fa-list mr-1"></i>
                        Produtos Encontrados
                    </h6>
                    <div class="list-group" id="listaProdutosEncontrados">
                        <!-- Produtos encontrados serão inseridos aqui -->
                    </div>
                </div>

                <!-- Detalhes do produto selecionado -->
                <div id="detalhesProduto" style="display: none;">
                    <hr>
                    <h6 class="mb-3">
                        <i class="fas fa-edit mr-1"></i>
                        Editar Produto
                    </h6>
                    
                    <form id="formEditarProduto">
                        <input type="hidden" id="produtoId">
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="produtoNome" class="font-weight-bold">Nome do Produto</label>
                                    <input type="text" class="form-control" id="produtoNome" placeholder="Nome do produto" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="produtoQuantidade" class="font-weight-bold">Quantidade em Estoque</label>
                                    <input type="number" class="form-control" id="produtoQuantidade" min="0" placeholder="0" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="produtoDescricao" class="font-weight-bold">Descrição</label>
                            <textarea class="form-control" id="produtoDescricao" rows="3" placeholder="Descrição do produto (opcional)"></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                <strong>Informações do Produto:</strong>
                                <ul class="mb-0 mt-2">
                                    <li>ID: <span id="infoProdutoId">-</span></li>
                                    <li>Criado em: <span id="infoProdutoCriado">-</span></li>
                                    <li>Última atualização: <span id="infoProdutoAtualizado">-</span></li>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-success mr-2">
                                <i class="fas fa-save mr-1"></i>
                                Salvar Alterações
                            </button>
                            <button type="button" class="btn btn-secondary" id="btnCancelarEdicao">
                                <i class="fas fa-times mr-1"></i>
                                Cancelar
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Estado inicial -->
                <div id="estadoInicial">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Busque por um produto</h5>
                        <p class="text-muted">Digite o nome do produto no campo acima para encontrá-lo</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Cadastrar Produto -->
<div class="modal fade" id="modalCadastrarProduto" tabindex="-1" role="dialog" aria-labelledby="modalCadastrarProdutoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalCadastrarProdutoLabel">
                    <i class="fas fa-plus mr-2"></i>
                    Cadastrar Novo Produto
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCadastrarProduto">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="novoProdutoNome" class="font-weight-bold">
                                    <i class="fas fa-box mr-1"></i>
                                    Nome do Produto <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="novoProdutoNome" placeholder="Digite o nome do produto" required>
                                <small class="form-text text-muted">Nome único para identificar o produto</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="novoProdutoDescricao" class="font-weight-bold">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Descrição
                                </label>
                                <textarea class="form-control" id="novoProdutoDescricao" rows="3" placeholder="Descrição detalhada do produto (opcional)"></textarea>
                                <small class="form-text text-muted">Informações adicionais sobre o produto</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="novoProdutoQuantidade" class="font-weight-bold">
                                    <i class="fas fa-sort-numeric-up mr-1"></i>
                                    Quantidade Inicial <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control" id="novoProdutoQuantidade" min="0" placeholder="0" required>
                                <small class="form-text text-muted">Quantidade em estoque</small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-lightbulb mr-2"></i>
                                <strong>Dica:</strong> Certifique-se de que o nome do produto seja único e descritivo. A quantidade pode ser ajustada posteriormente através da função "Consultar Produto".
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="submit" form="formCadastrarProduto" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i>
                    Cadastrar Produto
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Registrar Entrada -->
<div class="modal fade" id="modalRegistrarEntrada" tabindex="-1" role="dialog" aria-labelledby="modalRegistrarEntradaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRegistrarEntradaLabel">
                    <i class="fas fa-arrow-up mr-2"></i>
                    Registrar Entrada de Produtos
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Busca de produto -->
                <div class="search-section mb-4">
                    <label for="buscaProdutoEntrada" class="form-label font-weight-bold">
                        <i class="fas fa-search mr-1"></i>
                        Buscar Produto <span class="text-danger">*</span>
                    </label>
                    <div class="produto-entrada-search-container position-relative">
                        <input type="text" class="form-control" id="buscaProdutoEntrada" placeholder="Digite o nome do produto..." autocomplete="off">
                        <input type="hidden" id="produtoEntradaId">
                        <div class="produto-entrada-results" id="produtoEntradaResults" style="display: none;"></div>
                    </div>
                    <small class="form-text text-muted">Digite pelo menos 3 caracteres para buscar</small>
                </div>

                <!-- Informações do produto selecionado -->
                <div id="produtoEntradaSelecionado" style="display: none;">
                    <div class="card border-info">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-box mr-1"></i>
                                Produto Selecionado
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <h5 id="nomeProdutoEntrada" class="text-primary">-</h5>
                                    <p id="descricaoProdutoEntrada" class="text-muted mb-1">-</p>
                                    <small class="text-muted">ID: #<span id="idProdutoEntrada">-</span></small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="estoque-atual">
                                        <label class="text-muted small">Estoque Atual:</label>
                                        <div class="h4 text-info mb-0">
                                            <i class="fas fa-boxes mr-1"></i>
                                            <span id="estoqueAtualEntrada">0</span> un.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Formulário de entrada -->
                <div id="formEntradaSection" style="display: none;">
                    <hr>
                    <form id="formRegistrarEntrada">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="quantidadeEntrada" class="font-weight-bold">
                                        <i class="fas fa-plus-circle mr-1 text-success"></i>
                                        Quantidade de Entrada <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control" id="quantidadeEntrada" min="1" placeholder="Digite a quantidade" required>
                                    <small class="form-text text-muted">Quantidade a ser adicionada ao estoque</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        <i class="fas fa-calculator mr-1 text-info"></i>
                                        Novo Total
                                    </label>
                                    <div class="form-control-plaintext bg-light rounded p-2">
                                        <strong id="novoTotalEntrada" class="text-success">0 unidades</strong>
                                    </div>
                                    <small class="form-text text-muted">Estoque após a entrada</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="observacoesEntrada" class="font-weight-bold">
                                <i class="fas fa-comment mr-1"></i>
                                Observações
                            </label>
                            <textarea class="form-control" id="observacoesEntrada" rows="2" placeholder="Observações sobre esta entrada (opcional)"></textarea>
                        </div>

                        <div class="alert alert-success">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Informação:</strong> Esta operação irá adicionar a quantidade informada ao estoque atual do produto selecionado.
                        </div>
                    </form>
                </div>

                <!-- Estado inicial -->
                <div id="estadoInicialEntrada">
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Selecione um produto</h5>
                        <p class="text-muted">Digite o nome do produto no campo acima para encontrá-lo</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i>
                    Cancelar
                </button>
                <button type="submit" form="formRegistrarEntrada" class="btn btn-success" id="btnConfirmarEntrada" disabled>
                    <i class="fas fa-check mr-1"></i>
                    Confirmar Entrada
                </button>
            </div>
        </div>
    </div>
</div>

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    /* Cards de estatísticas modernas */
    .stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #f1f5f9;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
    }
    
    .stat-card-primary::before { background: #007bff; }
    .stat-card-success::before { background: #28a745; }
    .stat-card-warning::before { background: #ffc107; }
    .stat-card-danger::before { background: #dc3545; }
    
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
        float: right;
        margin-top: -8px;
    }
    
    .stat-card-primary .stat-icon { background: #007bff; }
    .stat-card-success .stat-icon { background: #28a745; }
    .stat-card-warning .stat-icon { background: #ffc107; }
    .stat-card-danger .stat-icon { background: #dc3545; }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
        margin-bottom: 4px;
    }
    
    .stat-label {
        font-size: 14px;
        color: #64748b;
        font-weight: 500;
    }
    
    .stat-trend {
        margin-top: 12px;
        font-size: 12px;
        font-weight: 600;
    }
    
    /* Cards modernos */
    .modern-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        border: 1px solid #f1f5f9;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    
    .modern-card:hover {
        box-shadow: 0 8px 30px rgba(0,0,0,0.12);
    }
    
    .card-header-modern {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        padding: 20px 24px;
    }
    
    .card-title-modern {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #1e293b;
    }
    
    .card-body-modern {
        padding: 24px;
    }
    
    /* Botões de ação modernos */
    .action-btn {
        background: #fff;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 18px 16px;
        display: flex;
        align-items: center;
        text-align: left;
        text-decoration: none;
        color: inherit;
        transition: all 0.3s ease;
        min-height: 85px;
        height: auto;
    }
    
    .action-btn:hover {
        border-color: var(--hover-color);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        text-decoration: none;
        color: inherit;
    }
    
    .action-btn-primary { --hover-color: #007bff; }
    .action-btn-success { --hover-color: #28a745; }
    .action-btn-warning { --hover-color: #ffc107; }
    .action-btn-info { --hover-color: #17a2b8; }
    
    .action-btn:hover .action-icon {
        background: var(--hover-color);
        color: white;
    }
    
    .action-icon {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 14px;
        font-size: 18px;
        color: #64748b;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .action-content {
        flex: 1;
    }
    
    .action-title {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 3px;
        line-height: 1.3;
    }
    
    .action-desc {
        font-size: 12px;
        color: #64748b;
        line-height: 1.4;
        word-wrap: break-word;
        overflow-wrap: break-word;
    }
    

    
    /* Alertas modernos */
    .modern-alert {
        border-radius: 12px;
        border: none;
        padding: 16px 20px;
        font-weight: 500;
    }
    
    /* Melhorias no header */
    .content-header h1 {
        font-size: 28px;
    }
    
    /* Responsivo */
    @media (max-width: 768px) {
        .stat-card {
            padding: 20px;
            margin-bottom: 16px;
        }
        
        .stat-number {
            font-size: 24px;
        }
        
        .action-btn {
            height: auto;
            padding: 16px;
        }
        
        .action-icon {
            width: 40px;
            height: 40px;
            margin-right: 12px;
        }
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Debug removido
        
        // Animação dos cards de estatísticas
        $('.stat-card').each(function(index) {
            $(this).css('opacity', '0').css('transform', 'translateY(20px)');
            setTimeout(() => {
                $(this).css('transition', 'all 0.6s ease')
                      .css('opacity', '1')
                      .css('transform', 'translateY(0)');
            }, index * 100);
        });
        
        // Tooltip para botões de ação
        $('.action-btn').tooltip({
            placement: 'top',
            title: function() {
                return $(this).find('.action-desc').text();
            }
        });
        

        
    });
    
    // Função para mostrar notificações simples
    function showModernNotification(message, type = 'info') {
        // Usar SweetAlert2 se disponível, senão alert simples
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: 'Aviso',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        } else {
            alert(message);
        }
    }
    
    // Variáveis globais para dados
    let funcionarios = [];
    let centroCustos = [];
    let produtos = [];
    let contadorProdutos = 1;
    
    // Carregar dados quando o modal for aberto
    $('#modalRegistrarSaida').on('show.bs.modal', function() {
        carregarFuncionarios();
        carregarCentroCustos();
        carregarProdutos();
    });
    
    // Função para carregar funcionários (agora só carrega a lista)
    function carregarFuncionarios() {
        $.get('/api/funcionarios')
            .done(function(data) {
                funcionarios = data;
                $('#funcionario').attr('placeholder', 'Digite pelo menos 3 letras para buscar...');
            })
            .fail(function() {
                $('#funcionario').attr('placeholder', 'Erro ao carregar funcionários');
            });
    }
    
    // Função para carregar centro de custos
    function carregarCentroCustos() {
        // Novo endpoint livre para telas autenticadas
        $.get('/api/centros-custo')
            .done(function(resp) {
                // aceita tanto array direto quanto {success, data}
                centroCustos = (resp && resp.data) ? resp.data : (resp || []);
                $('#centro-custo').attr('placeholder', 'Digite pelo menos 3 letras para buscar...');
            })
            .fail(function() {
                // fallback para endpoint antigo (caso exista)
                $.get('/api/centro-custos')
                    .done(function(respAntigo){
                        centroCustos = (respAntigo && respAntigo.data) ? respAntigo.data : (respAntigo || []);
                        $('#centro-custo').attr('placeholder', 'Digite pelo menos 3 letras para buscar...');
                    })
                    .fail(function(){
                        $('#centro-custo').attr('placeholder', 'Erro ao carregar centros de custo');
                    });
            });
    }
    
    // Autocomplete para funcionários
    $('#funcionario').on('input', function() {
        const query = $(this).val().trim();
        const resultsContainer = $('#funcionarioResults');
        
        if (query.length < 3) {
            resultsContainer.hide().empty();
            $('#funcionario_id').val('');
            $('#infoFuncionario').hide();
            return;
        }
        
        // Filtrar funcionários que começam com a consulta
        const filteredFuncionarios = funcionarios.filter(funcionario => 
            funcionario.nome.toLowerCase().startsWith(query.toLowerCase())
        );
        
        if (filteredFuncionarios.length > 0) {
            let resultsHtml = '';
            
            // Mostrar até 20 resultados para evitar lista muito longa
            const maxResults = Math.min(filteredFuncionarios.length, 20);
            
            for (let i = 0; i < maxResults; i++) {
                const funcionario = filteredFuncionarios[i];
                resultsHtml += `
                    <div class="search-result-item" data-id="${funcionario.id}" 
                         data-nome="${funcionario.nome}" 
                         data-funcao="${funcionario.funcao}" 
                         data-cpf="${funcionario.cpf}">
                        <div class="search-result-name">${funcionario.nome}</div>
                        <div class="search-result-info">${funcionario.funcao} - CPF: ${funcionario.cpf}</div>
                    </div>
                `;
            }
            
            // Adicionar indicador se há mais resultados
            if (filteredFuncionarios.length > 20) {
                resultsHtml += `<div class="no-results" style="color: #3b82f6; font-weight: 500;">
                                   Mostrando 20 de ${filteredFuncionarios.length} resultados. Digite mais caracteres para refinar.
                               </div>`;
            }
            
            resultsContainer.html(resultsHtml).show();
        } else {
            resultsContainer.html('<div class="no-results">Nenhum funcionário encontrado</div>').show();
        }
    });
    
    // Selecionar funcionário do autocomplete
    $(document).on('click', '.search-result-item', function() {
        const funcionarioId = $(this).data('id');
        const nome = $(this).data('nome');
        const funcao = $(this).data('funcao');
        const cpf = $(this).data('cpf');
        
        // Preencher campos
        $('#funcionario').val(nome);
        $('#funcionario_id').val(funcionarioId);
        $('#funcionarioResults').hide();
        
        // Mostrar informações do funcionário
        $('#funcionarioNome').text(nome);
        $('#funcionarioFuncao').text(funcao);
        $('#funcionarioCpf').text(cpf);
        $('#infoFuncionario').show();
    });
    
    // Ocultar resultados quando clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.funcionario-search-container').length) {
            $('#funcionarioResults').hide();
        }
        if (!$(e.target).closest('.centro-custo-search-container').length) {
            $('#centroCustoResults').hide();
        }
    });
    
    // Autocomplete para centro de custos
    $('#centro-custo').on('input', function() {
        const query = $(this).val().trim();
        const resultsContainer = $('#centroCustoResults');
        
        if (query.length < 3) {
            resultsContainer.hide();
            return;
        }
        
        // Filtrar centro de custos
        const filteredCentroCustos = (centroCustos || []).filter(function(centro) {
            return centro.nome.toLowerCase().includes(query.toLowerCase());
        }).slice(0, 20);
        
        if (filteredCentroCustos.length > 0) {
            let resultsHtml = '';
            for (let i = 0; i < filteredCentroCustos.length; i++) {
                const centro = filteredCentroCustos[i];
                resultsHtml += `<div class="centro-custo-item" data-id="${centro.id}" data-nome="${escapeHtml(centro.nome)}">
                                   ${escapeHtml(centro.nome)}
                               </div>`;
            }
            
            resultsContainer.html(resultsHtml).show();
        } else {
            // fallback consulta direta ao backend
            $.get('/api/centros-custo/buscar', { termo: query })
                .done(function(resp){
                    const lista = (resp && resp.data) ? resp.data : [];
                    if (lista.length === 0) {
                        resultsContainer.html('<div class="centro-custo-item">Nenhum centro de custo encontrado</div>').show();
                        return;
                    }
                    let html = '';
                    lista.forEach(function(centro){
                        html += `<div class="centro-custo-item" data-id="${centro.id}" data-nome="${escapeHtml(centro.nome)}">${escapeHtml(centro.nome)}</div>`;
                    });
                    resultsContainer.html(html).show();
                })
                .fail(function(){
                    resultsContainer.html('<div class="centro-custo-item">Erro ao buscar centros de custo</div>').show();
                });
        }
    });
    
    // Selecionar centro de custo do autocomplete
    $(document).on('click', '.centro-custo-item', function() {
        const centroId = $(this).data('id');
        const nome = $(this).data('nome');
        
        if (centroId) {
            // Preencher campos
            $('#centro-custo').val(nome);
            $('#cc').val(centroId);
            $('#centroCustoResults').hide();
        }
    });
    
    // Função para carregar produtos
    function carregarProdutos() {
        $.get('/api/produtos')
            .done(function(data) {
                produtos = data;
                // Debug removido
            })
            .fail(function() {
                // Silenciar logs no navegador
            });
    }
    
    // Autocomplete de produtos
    // escape simples para conteúdo controlado pelo usuário
    function escapeHtml(str){ return String(str||'').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c])); }

    $(document).on('input', '.produto-search', function() {
        const query = $(this).val().trim();
        const resultsContainer = $(this).siblings('.produto-results');
        
        if (query.length < 3) {
            resultsContainer.hide();
            $(this).siblings('.produto-id').val('');
            $(this).closest('.produto-item').find('.estoque-info').text('');
            return;
        }
        
        // Filtrar produtos que começam com a consulta
        let filteredProdutos = (produtos || []).filter(produto => 
            (produto.nome||'').toLowerCase().startsWith(query.toLowerCase()) ||
            (produto.descricao||'').toLowerCase().startsWith(query.toLowerCase())
        );
        
        if (filteredProdutos.length > 0) {
            let resultsHtml = '';
            
            // Mostrar até 20 resultados para evitar lista muito longa
            const maxResults = Math.min(filteredProdutos.length, 20);
            
            for (let i = 0; i < maxResults; i++) {
                const produto = filteredProdutos[i];
                resultsHtml += `
                    <div class="produto-result-item" data-id="${produto.id}" 
                         data-nome="${escapeHtml(produto.nome)}" 
                         data-estoque="${produto.quantidade}"
                         data-descricao="${escapeHtml(produto.descricao || '')}">
                        <div class="produto-result-name">${escapeHtml(produto.nome)}</div>
                        <div class="produto-result-info">Estoque: ${produto.quantidade} unidades${produto.descricao ? ' - ' + escapeHtml(produto.descricao) : ''}</div>
                    </div>
                `;
            }
            
            // Adicionar indicador se há mais resultados
            if (filteredProdutos.length > 20) {
                resultsHtml += `<div class="no-results" style="color: #3b82f6; font-weight: 500;">
                                   Mostrando 20 de ${filteredProdutos.length} resultados. Digite mais caracteres para refinar.
                               </div>`;
            }
            
            resultsContainer.html(resultsHtml).show();
        } else {
            // Fallback: buscar no backend se local não encontrou (endpoint exclusivo do estoque)
            $.get('/api/estoque/produtos/buscar', { nome: query })
                .done(function(remotos){
                    if (!remotos || remotos.length === 0) {
                        resultsContainer.html('<div class="no-results">Nenhum produto encontrado</div>').show();
                        return;
                    }
                    // Atualiza cache local mesclando pelo id
                    const byId = new Map((produtos||[]).map(p=>[p.id,p]));
                    remotos.forEach(p=>{ byId.set(p.id, p); });
                    produtos = Array.from(byId.values());

                    let resultsHtml = '';
                    const maxResults = Math.min(remotos.length, 20);
                    for (let i = 0; i < maxResults; i++) {
                        const produto = remotos[i];
                        resultsHtml += `
                            <div class="produto-result-item" data-id="${produto.id}" 
                                 data-nome="${escapeHtml(produto.nome)}" 
                                 data-estoque="${produto.quantidade}"
                                 data-descricao="${escapeHtml(produto.descricao || '')}">
                                <div class="produto-result-name">${escapeHtml(produto.nome)}</div>
                                <div class="produto-result-info">Estoque: ${produto.quantidade} unidades${produto.descricao ? ' - ' + escapeHtml(produto.descricao) : ''}</div>
                            </div>
                        `;
                    }
                    resultsContainer.html(resultsHtml).show();
                })
                .fail(function(){
                    resultsContainer.html('<div class="no-results">Erro ao buscar produtos</div>').show();
                });
        }
    });
    
    // Selecionar produto do autocomplete
    $(document).on('click', '.produto-result-item', function() {
        const produtoId = $(this).data('id');
        const produtoNome = $(this).data('nome');
        const produtoEstoque = $(this).data('estoque');
        const produtoDescricao = $(this).data('descricao');
        
        const container = $(this).closest('.produto-search-container');
        const produtoItem = container.closest('.produto-item');
        
        // Preencher os campos
        container.find('.produto-search').val(produtoNome);
        container.find('.produto-id').val(produtoId);
        container.find('.produto-results').hide();
        
        // Mostrar informações completas do produto
        let infoText = `Estoque disponível: ${produtoEstoque} unidades`;
        if (produtoDescricao && produtoDescricao.trim() !== '') {
            infoText += ` - ${produtoDescricao}`;
        }
        produtoItem.find('.estoque-info').text(infoText);
        
        // Definir o máximo da quantidade baseado no estoque real
        const quantidadeInput = produtoItem.find('.quantidade-input');
        quantidadeInput.attr('max', produtoEstoque);
        
        // Adicionar validação para estoque zerado
        if (produtoEstoque <= 0) {
            quantidadeInput.attr('max', 0).attr('disabled', true);
            produtoItem.find('.estoque-info').addClass('text-danger').text('⚠️ Produto sem estoque disponível');
        } else {
            quantidadeInput.attr('disabled', false).removeClass('text-danger');
        }
    });
    
    // Esconder resultados ao clicar fora
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.produto-search-container').length) {
            $('.produto-results').hide();
        }
    });
    
    // Adicionar novo produto
    $('#adicionarProduto').click(function() {
        const novoProdutoHtml = `
            <div class="produto-item border rounded p-3 mb-3">
                <div class="row">
                    <div class="col-md-6">
                        <label>Produto</label>
                        <div class="produto-search-container">
                            <input type="text" class="form-control produto-search" placeholder="Digite o nome do produto..." autocomplete="off" required>
                            <input type="hidden" class="produto-id" name="produtos[${contadorProdutos}][produto_id]" required>
                            <div class="produto-results"></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>Quantidade</label>
                        <input type="number" class="form-control quantidade-input" name="produtos[${contadorProdutos}][quantidade]" min="1" required>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remover-produto">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <small class="estoque-info text-muted"></small>
                    </div>
                </div>
            </div>
        `;
        
        $('#listaProdutos').append(novoProdutoHtml);
        contadorProdutos++;
        
        // Mostrar botões de remover se há mais de um produto
        if ($('.produto-item').length > 1) {
            $('.remover-produto').show();
        }
    });
    
    // Remover produto
    $(document).on('click', '.remover-produto', function() {
        $(this).closest('.produto-item').remove();
        
        // Esconder botões de remover se só há um produto
        if ($('.produto-item').length <= 1) {
            $('.remover-produto').hide();
        }
    });
    
    // Enviar formulário
    $('#formRegistrarSaida').submit(function(e) {
        e.preventDefault();
        
        const funcionarioId = $('#funcionario_id').val();
        const centroCustoId = $('#cc').val();
        const observacoes = $('#observacoes').val();
        
        if (!funcionarioId) {
            showModernNotification('Por favor, selecione um funcionário', 'warning');
            return;
        }
        
        if (!centroCustoId) {
            showModernNotification('Por favor, selecione um centro de custo', 'warning');
            return;
        }
        
        // Coletar dados dos produtos
        const baixas = [];
        $('.produto-item').each(function() {
            const produtoId = $(this).find('.produto-id').val();
            const quantidade = $(this).find('.quantidade-input').val();
            
            if (produtoId && quantidade) {
                baixas.push({
                    produto_id: produtoId,
                    quantidade: parseInt(quantidade)
                });
            }
        });
        
        if (baixas.length === 0) {
            showModernNotification('Por favor, adicione pelo menos um produto', 'warning');
            return;
        }
        
        // Mostrar loading
        const submitBtn = $('#formRegistrarSaida button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Registrando...').prop('disabled', true);
        
        // Enviar dados
        $.ajax({
            url: '/api/baixas',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                funcionario_id: funcionarioId,
                centro_custo_id: centroCustoId,
                baixas: baixas,
                observacoes: observacoes
            }),
            success: function(response) {
                showModernNotification(response.message, 'success');
                $('#modalRegistrarSaida').modal('hide');
                
                // Recarregar a página para atualizar os dados
                setTimeout(() => {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showModernNotification(response?.message || 'Erro ao registrar saída', 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Limpar formulário quando modal fechar
    $('#modalRegistrarSaida').on('hidden.bs.modal', function() {
        $('#formRegistrarSaida')[0].reset();
        $('#funcionario_id').val('');
        $('#funcionarioResults').hide();
        $('#infoFuncionario').hide();
        $('#cc').val('');
        $('#centroCustoResults').hide();
        
        // Limpar campos de autocomplete
        $('.produto-search').val('');
        $('.produto-id').val('');
        $('.produto-results').hide();
        $('.estoque-info').text('');
        
        // Manter apenas um produto
        const primeiroItem = $('.produto-item').first();
        $('.produto-item').not(primeiroItem).remove();
        $('.remover-produto').hide();
        
        contadorProdutos = 1;
    });
    
    // Carregar produtos em falta quando o modal for aberto
    $('#modalProdutosFalta').on('show.bs.modal', function() {
        $('#loadingProdutosFalta').show();
        $('#contentProdutosFalta').hide();
        $('#noProdutosFalta').hide();
        
        $.get('/api/produtos-em-falta')
            .done(function(produtos) {
                $('#loadingProdutosFalta').hide();
                
                if (produtos.length === 0) {
                    $('#noProdutosFalta').show();
                } else {
                    $('#contentProdutosFalta').show();
                    
                                        // Atualizar contador de produtos
                    $('#totalProdutosFalta').text(produtos.length);
                    
                    // Atualizar timestamp
                    const now = new Date();
                    const dataAtual = now.toLocaleDateString('pt-BR', {
                        year: 'numeric',
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    $('#ultimaAtualizacao').text('Atualizado agora');
                    $('#modalGeneratedTime').text(dataAtual);
                    $('#printDate').text(`Gerado em: ${dataAtual}`);
                    
                    // Criar linhas da tabela com classes modernas
                    let tableRows = '';
                    let printRows = '';
                    
                    produtos.forEach(function(produto) {
                        // Linha para o modal (com classes modernas)
                        tableRows += `
                            <tr>
                                <td class="text-center">
                                    <span class="product-id">#${produto.id}</span>
                                </td>
                                <td>
                                    <div class="product-name">${produto.nome}</div>
                                </td>
                                <td>
                                    <div class="product-description">${produto.descricao || 'Sem descrição'}</div>
                                </td>
                                <td class="text-center">
                                    <span class="quantity-zero">0</span>
                                </td>
                                <td class="text-center">
                                    <span class="status-badge">SEM ESTOQUE</span>
                                </td>
                            </tr>
                        `;
                        
                        // Linha para impressão (simples)
                        printRows += `
                            <tr>
                                <td>#${produto.id}</td>
                                <td>${produto.nome}</td>
                                <td>${produto.descricao || 'Sem descrição'}</td>
                                <td>0</td>
                                <td>SEM ESTOQUE</td>
                            </tr>
                        `;
                    });
                    
                    // Preencher ambas as tabelas
                    $('#tabelaProdutosFalta').html(tableRows);
                    $('#printTableBody').html(printRows);
                }
            })
            .fail(function() {
                $('#loadingProdutosFalta').hide();
                $('#contentProdutosFalta').show();
                $('#tabelaProdutosFalta').html(`
                    <tr>
                        <td colspan="5" class="text-center text-danger">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Erro ao carregar produtos em falta
                        </td>
                    </tr>
                `);
            });
    });
    
    // Funcionalidade do botão de impressão
    $('#btnImprimirProdutosFalta').click(function() {
        // Abrir uma nova janela com layout específico para impressão
        var printWindow = window.open('', '_blank', 'width=800,height=600');
        
        // Obter os dados dos produtos em falta
        $.get('/api/produtos-em-falta')
            .done(function(produtos) {
                var printContent = `
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Relatório de Produtos em Falta</title>
    <style>
        @page {
            size: A4;
            margin: 1.5cm;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            color: #000;
            font-size: 12px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .company-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        
        .report-title {
            font-size: 22px;
            font-weight: bold;
            color: #000;
            margin: 8px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .report-info {
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }
        
        .summary {
            background: #f8f9fa;
            padding: 12px;
            margin: 10px 0;
            border-left: 4px solid #dc3545;
            border-radius: 3px;
        }
        
        .summary h3 {
            margin: 0 0 8px 0;
            color: #dc3545;
            font-size: 14px;
        }
        
        .summary p {
            margin: 3px 0;
            font-size: 12px;
            line-height: 1.3;
        }
        
        .table-container {
            margin: 15px 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-top: 10px;
        }
        
        th {
            background: #343a40;
            color: white;
            padding: 8px 5px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            border: 1px solid #000;
        }
        
        td {
            padding: 6px 5px;
            border: 1px solid #333;
            text-align: left;
            font-size: 11px;
            line-height: 1.2;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .col-id {
            width: 8%;
            text-align: center;
            font-weight: bold;
        }
        
        .col-produto {
            width: 50%;
            font-weight: 600;
        }
        
        .col-descricao {
            width: 25%;
            font-style: italic;
            color: #666;
        }
        
        .col-qtd {
            width: 8%;
            text-align: center;
            font-weight: bold;
            color: #dc3545;
            font-size: 12px;
        }
        
        .col-status {
            width: 9%;
            text-align: center;
            color: #dc3545;
            font-weight: bold;
            font-size: 10px;
        }
        
        .footer {
            position: absolute;
            bottom: 1cm;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 8px;
        }
        
        .actions-section {
            margin-top: 20px;
            padding: 12px;
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 3px;
        }
        
        .actions-section h3 {
            color: #856404;
            margin-bottom: 8px;
            font-size: 13px;
        }
        
        .actions-section p {
            font-size: 11px;
            line-height: 1.3;
            margin: 4px 0;
        }
        
        @media print {
            body { print-color-adjust: exact; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">Sistema de Controle de Estoque</div>
        <div class="report-title">Relatório de Produtos em Falta</div>
        <div class="report-info">
            Gerado em: ${new Date().toLocaleDateString('pt-BR', {
                year: 'numeric',
                month: 'long', 
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })}
        </div>
    </div>
    
    <div class="summary">
        <h3>⚠️ Resumo Executivo</h3>
        <p><strong>Total de produtos em falta:</strong> ${produtos.length} itens</p>
        <p><strong>Status:</strong> Reposição urgente necessária</p>
        <p><strong>Critério:</strong> Produtos com estoque = 0 unidades</p>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="col-id">ID</th>
                    <th class="col-produto">Nome do Produto</th>
                    <th class="col-descricao">Descrição</th>
                    <th class="col-qtd">Qtd</th>
                    <th class="col-status">Status</th>
                </tr>
            </thead>
            <tbody>`;
                
                produtos.forEach(function(produto, index) {
                    printContent += `
                <tr>
                    <td class="col-id">#${produto.id}</td>
                    <td class="col-produto">${produto.nome}</td>
                    <td class="col-descricao">${produto.descricao || 'Sem descrição'}</td>
                    <td class="col-qtd">0</td>
                    <td class="col-status">SEM ESTOQUE</td>
                </tr>`;
                });
                
                printContent += `
            </tbody>
        </table>
    </div>
    
    <div class="actions-section">
        <h3>📋 Ações Recomendadas</h3>
        <p>• <strong>Prioridade Alta:</strong> Contactar fornecedores para reposição imediata</p>
        <p>• <strong>Monitoramento:</strong> Verificar demanda e estabelecer estoque mínimo</p>
        <p>• <strong>Controle:</strong> Implementar alertas automáticos para estoque baixo</p>
    </div>
    
    <div class="footer">
        <p>Relatório gerado automaticamente pelo Sistema de Controle de Estoque</p>
        <p>localhost:8000/brs/controle-estoque | Página 1/1</p>
    </div>
</body>
</html>`;
                
                printWindow.document.write(printContent);
                printWindow.document.close();
                
                // Aguardar carregar e imprimir
                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.print();
                        printWindow.close();
                    }, 250);
                };
            })
            .fail(function() {
                alert('Erro ao carregar dados para impressão');
                printWindow.close();
            });
    });
    
    // Funcionalidades do modal Consultar Produto
    let produtoSelecionado = null;
    
    // Busca de produtos
    function buscarProdutos() {
        const nome = $('#buscaProduto').val().trim();
        
        if (nome.length < 3) {
            $('#resultadosBusca').hide();
            return;
        }
        
        $.get('/api/estoque/produtos/buscar', { nome: nome })
            .done(function(produtos) {
                if (produtos.length > 0) {
                    let listHtml = '';
                    produtos.forEach(function(produto) {
                        listHtml += `
                            <div class="list-group-item list-group-item-action produto-item" data-produto='${JSON.stringify(produto)}'>
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${produto.nome}</h6>
                                    <small>ID: #${produto.id}</small>
                                </div>
                                <p class="mb-1">${produto.descricao || 'Sem descrição'}</p>
                                <small>Estoque: <span class="badge badge-${produto.quantidade > 0 ? 'success' : 'danger'}">${produto.quantidade} unidades</span></small>
                            </div>
                        `;
                    });
                    
                    $('#listaProdutosEncontrados').html(listHtml);
                    $('#resultadosBusca').show();
                    $('#estadoInicial').hide();
                } else {
                    $('#listaProdutosEncontrados').html('<div class="text-center p-3 text-muted">Nenhum produto encontrado</div>');
                    $('#resultadosBusca').show();
                    $('#estadoInicial').hide();
                }
            })
            .fail(function() {
                alert('Erro ao buscar produtos');
            });
    }
    
    // Buscar ao digitar
    $('#buscaProduto').on('input', function() {
        buscarProdutos();
    });
    
    // Buscar ao clicar no botão
    $('#btnBuscarProduto').click(function() {
        buscarProdutos();
    });
    
    // Selecionar produto da lista
    $(document).on('click', '.produto-item', function() {
        produtoSelecionado = JSON.parse($(this).attr('data-produto'));
        
        // Preencher formulário
        $('#produtoId').val(produtoSelecionado.id);
        $('#produtoNome').val(produtoSelecionado.nome);
        $('#produtoDescricao').val(produtoSelecionado.descricao || '');
        $('#produtoQuantidade').val(produtoSelecionado.quantidade);
        
        // Preencher informações
        $('#infoProdutoId').text('#' + produtoSelecionado.id);
        $('#infoProdutoCriado').text(new Date(produtoSelecionado.created_at).toLocaleDateString('pt-BR'));
        $('#infoProdutoAtualizado').text(new Date(produtoSelecionado.updated_at).toLocaleDateString('pt-BR'));
        
        // Mostrar seção de edição
        $('#detalhesProduto').show();
        $('#resultadosBusca').hide();
        
        // Destacar item selecionado
        $('.produto-item').removeClass('active');
        $(this).addClass('active');
    });
    
    // Salvar alterações
    $('#formEditarProduto').submit(function(e) {
        e.preventDefault();
        
        if (!produtoSelecionado) return;
        
        const data = {
            nome: $('#produtoNome').val(),
            descricao: $('#produtoDescricao').val(),
            quantidade: parseInt($('#produtoQuantidade').val())
        };
        
        const submitBtn = $('#formEditarProduto button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Salvando...').prop('disabled', true);
        
        $.ajax({
            url: `/api/produtos/${produtoSelecionado.id}`,
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            success: function(response) {
                showModernNotification(response.message, 'success');
                
                // Atualizar dados do produto selecionado
                produtoSelecionado.nome = data.nome;
                produtoSelecionado.descricao = data.descricao;
                produtoSelecionado.quantidade = data.quantidade;
                produtoSelecionado.updated_at = new Date().toISOString();
                
                $('#infoProdutoAtualizado').text(new Date().toLocaleDateString('pt-BR'));
                
                // Recarregar dados da página principal após 1 segundo
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showModernNotification(response?.message || 'Erro ao atualizar produto', 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Cancelar edição
    $('#btnCancelarEdicao').click(function() {
        $('#detalhesProduto').hide();
        $('#resultadosBusca').show();
        produtoSelecionado = null;
    });
    
    // Limpar modal ao fechar
    $('#modalConsultarProduto').on('hidden.bs.modal', function() {
        $('#buscaProduto').val('');
        $('#resultadosBusca').hide();
        $('#detalhesProduto').hide();
        $('#estadoInicial').show();
        produtoSelecionado = null;
        $('#formEditarProduto')[0].reset();
    });
    
    // Funcionalidades do modal Cadastrar Produto
    $('#formCadastrarProduto').submit(function(e) {
        e.preventDefault();
        
        const data = {
            nome: $('#novoProdutoNome').val().trim(),
            descricao: $('#novoProdutoDescricao').val().trim(),
            quantidade: parseInt($('#novoProdutoQuantidade').val()) || 0
        };
        
        // Validação básica
        if (!data.nome) {
            showModernNotification('Por favor, informe o nome do produto', 'warning');
            $('#novoProdutoNome').focus();
            return;
        }
        
        const submitBtn = $('#formCadastrarProduto').closest('.modal').find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Cadastrando...').prop('disabled', true);
        
        $.ajax({
            url: '/api/produtos',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            success: function(response) {
                showModernNotification(response.message, 'success');
                
                // Fechar modal e limpar formulário
                $('#modalCadastrarProduto').modal('hide');
                $('#formCadastrarProduto')[0].reset();
                
                // Recarregar a página após 1 segundo para atualizar os dados
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                let errorMessage = 'Erro ao cadastrar produto';
                
                if (response && response.errors) {
                    // Erro de validação
                    const errors = Object.values(response.errors).flat();
                    errorMessage = errors.join(', ');
                } else if (response && response.message) {
                    errorMessage = response.message;
                }
                
                showModernNotification(errorMessage, 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Limpar modal ao fechar
    $('#modalCadastrarProduto').on('hidden.bs.modal', function() {
        $('#formCadastrarProduto')[0].reset();
        // Remover classes de validação se existirem
        $('#formCadastrarProduto').find('.is-invalid').removeClass('is-invalid');
        $('#formCadastrarProduto').find('.invalid-feedback').remove();
    });
    
    // Validação em tempo real para o nome do produto
    $('#novoProdutoNome').on('input', function() {
        const valor = $(this).val().trim();
        if (valor.length > 0 && valor.length < 3) {
            $(this).addClass('is-invalid');
            if ($(this).next('.invalid-feedback').length === 0) {
                $(this).after('<div class="invalid-feedback">Nome deve ter pelo menos 3 caracteres</div>');
            }
        } else {
            $(this).removeClass('is-invalid');
            $(this).next('.invalid-feedback').remove();
        }
    });
    
    // Funcionalidades do modal Registrar Entrada
    let produtoEntradaSelecionado = null;
    
    // Busca de produtos para entrada
    function buscarProdutosEntrada() {
        const nome = $('#buscaProdutoEntrada').val().trim();
        
        if (nome.length < 3) {
            $('#produtoEntradaResults').hide();
            return;
        }
        
        $.get('/api/estoque/produtos/buscar', { nome: nome })
            .done(function(produtos) {
                if (produtos.length > 0) {
                    let listHtml = '';
                    produtos.forEach(function(produto) {
                        listHtml += `
                            <div class="produto-entrada-result-item" data-produto='${JSON.stringify(produto)}'>
                                <div class="produto-nome">${produto.nome}</div>
                                <div class="produto-descricao">${produto.descricao || 'Sem descrição'}</div>
                                <div class="produto-estoque">Estoque atual: ${produto.quantidade} unidades</div>
                            </div>
                        `;
                    });
                    
                    $('#produtoEntradaResults').html(listHtml).show();
                } else {
                    $('#produtoEntradaResults').html('<div class="text-center p-3 text-muted">Nenhum produto encontrado</div>').show();
                }
            })
            .fail(function() {
                alert('Erro ao buscar produtos');
            });
    }
    
    // Buscar ao digitar
    $('#buscaProdutoEntrada').on('input', function() {
        buscarProdutosEntrada();
    });
    
    // Selecionar produto da lista
    $(document).on('click', '.produto-entrada-result-item', function() {
        produtoEntradaSelecionado = JSON.parse($(this).attr('data-produto'));
        
        // Preencher informações do produto
        $('#produtoEntradaId').val(produtoEntradaSelecionado.id);
        $('#buscaProdutoEntrada').val(produtoEntradaSelecionado.nome);
        $('#nomeProdutoEntrada').text(produtoEntradaSelecionado.nome);
        $('#descricaoProdutoEntrada').text(produtoEntradaSelecionado.descricao || 'Sem descrição');
        $('#idProdutoEntrada').text(produtoEntradaSelecionado.id);
        $('#estoqueAtualEntrada').text(produtoEntradaSelecionado.quantidade);
        
        // Mostrar seções e ocultar outras
        $('#produtoEntradaResults').hide();
        $('#produtoEntradaSelecionado').show();
        $('#formEntradaSection').show();
        $('#estadoInicialEntrada').hide();
        $('#btnConfirmarEntrada').prop('disabled', false);
        
        // Limpar campos do formulário
        $('#quantidadeEntrada').val('');
        $('#observacoesEntrada').val('');
        atualizarNovoTotal();
    });
    
    // Atualizar novo total quando a quantidade mudar
    $('#quantidadeEntrada').on('input', function() {
        atualizarNovoTotal();
    });
    
    function atualizarNovoTotal() {
        if (produtoEntradaSelecionado) {
            const quantidadeEntrada = parseInt($('#quantidadeEntrada').val()) || 0;
            const estoqueAtual = produtoEntradaSelecionado.quantidade;
            const novoTotal = estoqueAtual + quantidadeEntrada;
            
            $('#novoTotalEntrada').text(novoTotal + ' unidades');
            
            // Habilitar/desabilitar botão de confirmar baseado na quantidade
            $('#btnConfirmarEntrada').prop('disabled', quantidadeEntrada <= 0);
        }
    }
    
    // Submeter formulário de entrada
    $('#formRegistrarEntrada').submit(function(e) {
        e.preventDefault();
        
        if (!produtoEntradaSelecionado) {
            showModernNotification('Por favor, selecione um produto', 'warning');
            return;
        }
        
        const quantidade = parseInt($('#quantidadeEntrada').val());
        if (!quantidade || quantidade <= 0) {
            showModernNotification('Por favor, informe uma quantidade válida', 'warning');
            $('#quantidadeEntrada').focus();
            return;
        }
        
        const data = {
            produto_id: produtoEntradaSelecionado.id,
            quantidade: quantidade,
            observacoes: $('#observacoesEntrada').val().trim()
        };
        
        const submitBtn = $('#btnConfirmarEntrada');
        const originalText = submitBtn.html();
        submitBtn.html('<i class="fas fa-spinner fa-spin mr-1"></i> Processando...').prop('disabled', true);
        
        $.ajax({
            url: '/api/entradas',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Content-Type': 'application/json'
            },
            data: JSON.stringify(data),
            success: function(response) {
                showModernNotification(response.message, 'success');
                
                // Fechar modal e limpar
                $('#modalRegistrarEntrada').modal('hide');
                
                // Recarregar página após 1 segundo
                setTimeout(() => {
                    location.reload();
                }, 1000);
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showModernNotification(response?.message || 'Erro ao registrar entrada', 'error');
            },
            complete: function() {
                submitBtn.html(originalText).prop('disabled', false);
            }
        });
    });
    
    // Limpar modal ao fechar
    $('#modalRegistrarEntrada').on('hidden.bs.modal', function() {
        $('#buscaProdutoEntrada').val('');
        $('#produtoEntradaResults').hide();
        $('#produtoEntradaSelecionado').hide();
        $('#formEntradaSection').hide();
        $('#estadoInicialEntrada').show();
        $('#btnConfirmarEntrada').prop('disabled', true);
        $('#formRegistrarEntrada')[0].reset();
        produtoEntradaSelecionado = null;
    });
</script>

<style>
    /* Animação ripple */
    @keyframes ripple {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    /* Estilos removidos - notificações agora usam SweetAlert2 */
    
    /* Autocomplete de funcionários */
    .funcionario-search-container {
        position: relative;
    }
    
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1050;
        display: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    /* Personalizar a barra de rolagem */
    .search-results::-webkit-scrollbar {
        width: 8px;
    }
    
    .search-results::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    .search-results::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .search-results::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Autocomplete de produtos */
    .produto-search-container {
        position: relative;
    }
    
    .produto-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 8px 8px;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1050;
        display: none;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    /* Personalizar a barra de rolagem dos produtos */
    .produto-results::-webkit-scrollbar {
        width: 8px;
    }
    
    .produto-results::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    .produto-results::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .produto-results::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Estilos para autocomplete do modal de entrada */
    .produto-entrada-search-container {
        position: relative;
    }
    
    .produto-entrada-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 250px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 0 0 5px 5px;
    }
    
    .produto-entrada-result-item {
        padding: 12px 15px;
        border-bottom: 1px solid #f0f0f0;
        cursor: pointer;
        transition: background-color 0.2s;
    }
    
    .produto-entrada-result-item:hover {
        background-color: #f8f9fa;
    }
    
    .produto-entrada-result-item:last-child {
        border-bottom: none;
    }
    
    .produto-entrada-result-item .produto-nome {
        font-weight: 600;
        color: #333;
        margin-bottom: 3px;
    }
    
    .produto-entrada-result-item .produto-descricao {
        font-size: 0.9em;
        color: #666;
        margin-bottom: 3px;
    }
    
    .produto-entrada-result-item .produto-estoque {
        font-size: 0.85em;
        color: #28a745;
        font-weight: 500;
    }
    
    .produto-entrada-results::-webkit-scrollbar {
        width: 6px;
    }
    
    .produto-entrada-results::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .produto-entrada-results::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .produto-entrada-results::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    /* Estilos para autocomplete do centro de custo */
    .centro-custo-search-container {
        position: relative;
    }
    
    .centro-custo-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        border-radius: 0 0 5px 5px;
        display: none;
    }
    
    .centro-custo-item {
        padding: 10px 12px;
        cursor: pointer;
        border-bottom: 1px solid #f0f0f0;
        transition: background-color 0.2s;
        font-size: 14px;
    }
    
    .centro-custo-item:hover {
        background-color: #f8f9fa;
    }
    
    .centro-custo-item:last-child {
        border-bottom: none;
    }
    
    .centro-custo-results::-webkit-scrollbar {
        width: 6px;
    }
    
    .centro-custo-results::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .centro-custo-results::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 3px;
    }
    
    .centro-custo-results::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    
    .produto-result-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }
    
    .produto-result-item:hover {
        background-color: #f8fafc;
    }
    
    .produto-result-item:last-child {
        border-bottom: none;
    }
    
    .produto-result-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }
    
    .produto-result-info {
        font-size: 12px;
        color: #64748b;
    }
    
    /* Estilos para o modal compacto */
    .alert-compact {
        padding: 12px 15px;
        border-radius: 8px;
        border: 1px solid #ffeaa7;
        background: #fff3cd;
    }
    
    .compact-header {
        padding: 8px 12px !important;
        font-size: 13px !important;
        font-weight: 600 !important;
    }
    
    .modern-table {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        border: none;
        margin-bottom: 0;
    }
    
    .modern-table thead th {
        background: #495057;
        color: white;
        border: none;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    
    .modern-table tbody tr {
        transition: background-color 0.2s ease;
        border: none;
    }
    
    .modern-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .modern-table tbody td {
        padding: 8px 12px;
        border: none;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
        font-size: 13px;
    }
    
    .product-id {
        font-weight: bold;
        color: #495057;
        background: #e9ecef;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 11px;
    }
    
    .product-name {
        font-weight: 600;
        color: #212529;
        font-size: 13px;
    }
    
    .product-description {
        font-style: italic;
        color: #6c757d;
        font-size: 12px;
    }
    
    .quantity-zero {
        background: #dc3545;
        color: white;
        padding: 2px 6px;
        border-radius: 12px;
        font-weight: bold;
        font-size: 11px;
        min-width: 20px;
        text-align: center;
        display: inline-block;
    }
    
    .status-badge {
        background: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 12px;
        font-weight: bold;
        font-size: 10px;
        text-transform: uppercase;
        white-space: nowrap;
        display: inline-block;
        min-width: fit-content;
    }
    
    .modal-footer-compact {
        margin-top: 15px;
        padding-top: 10px;
        border-top: 1px solid #e9ecef;
        text-align: center;
    }
    
    .table-container {
        max-height: 350px;
        overflow-y: auto;
        border-radius: 8px;
    }
    
    /* Personalizar scrollbar da tabela */
    .table-container::-webkit-scrollbar {
        width: 8px;
    }
    
    .table-container::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    
    .table-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
        transition: all 0.2s ease;
    }
    
    .table-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
    
    /* Estilos específicos para impressão A4 */
    @media print {
        @page {
            size: A4;
            margin: 20mm 15mm;
        }
        
        body * {
            visibility: hidden;
        }
        
        .print-area, .print-area * {
            visibility: visible;
        }
        
        .print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            font-family: 'Arial', sans-serif;
        }
        
        /* Cabeçalho otimizado para A4 */
        .print-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #000;
        }
        
        .print-title {
            font-size: 28px;
            font-weight: bold;
            color: #000;
            margin: 0 0 12px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .print-subtitle {
            font-size: 18px;
            color: #333;
            margin: 0 0 8px 0;
            font-weight: 500;
        }
        
        .print-date {
            font-size: 16px;
            color: #666;
            margin: 0;
            font-style: italic;
        }
        
        /* Tabela otimizada para A4 */
        .print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        
        .print-table th {
            background-color: #e0e0e0;
            border: 2px solid #000;
            padding: 12px 8px;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
            color: #000;
        }
        
        .print-table td {
            border: 1px solid #000;
            padding: 10px 8px;
            font-size: 14px;
            vertical-align: middle;
            line-height: 1.3;
        }
        
        /* Zebra striping para melhor leitura */
        .print-table tbody tr:nth-child(odd) {
            background-color: #f8f8f8;
        }
        
        /* Larguras específicas das colunas */
        .print-table th:nth-child(1), .print-table td:nth-child(1) {
            width: 60px;
            text-align: center;
            font-weight: bold;
        }
        
        .print-table th:nth-child(2), .print-table td:nth-child(2) {
            width: 250px;
            font-weight: 600;
        }
        
        .print-table th:nth-child(3), .print-table td:nth-child(3) {
            width: 180px;
            font-style: italic;
            color: #666;
        }
        
        .print-table th:nth-child(4), .print-table td:nth-child(4) {
            width: 70px;
            text-align: center;
            font-weight: bold;
            color: #d32f2f;
            font-size: 16px;
        }
        
        .print-table th:nth-child(5), .print-table td:nth-child(5) {
            width: 120px;
            text-align: center;
            font-weight: bold;
            color: #d32f2f;
            font-size: 13px;
        }
        
        /* Rodapé compacto */
        .print-footer {
            position: fixed;
            bottom: 15mm;
            left: 0;
            right: 0;
            padding-top: 15px;
            border-top: 2px solid #ccc;
            font-size: 12px;
            color: #666;
            text-align: center;
            background: white;
            line-height: 1.4;
        }
        
        /* Quebra de página se necessário */
        .print-table {
            page-break-inside: auto;
        }
        
        .print-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }
        
        /* Remover elementos desnecessários */
        .modal-header,
        .modal-footer,
        .btn,
        .alert,
        .close {
            display: none !important;
        }
    }
    
    .search-result-item {
        padding: 12px 16px;
        cursor: pointer;
        border-bottom: 1px solid #f1f5f9;
        transition: all 0.2s ease;
    }
    
    .search-result-item:hover {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05) 0%, rgba(147, 197, 253, 0.05) 100%);
    }
    
    .search-result-item:last-child {
        border-bottom: none;
    }
    
    .search-result-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 2px;
    }
    
    .search-result-info {
        font-size: 12px;
        color: #64748b;
    }
    
    .no-results {
        padding: 12px 16px;
        text-align: center;
        color: #64748b;
        font-style: italic;
    }
</style>
@stop