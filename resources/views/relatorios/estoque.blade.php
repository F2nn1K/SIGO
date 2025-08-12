@extends('adminlte::page')

@section('title', 'Relatório de Estoque')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="font-weight-bold">
            <i class="fas fa-boxes text-primary mr-3"></i>
            Relatório de Estoque
        </h1>
        <p class="text-muted mt-1 mb-0">Relatórios completos do controle de estoque</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-filter mr-2"></i>
                        Filtros de Pesquisa
                    </h5>
                </div>
                <div class="card-body">
                    <form id="formFiltros">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="data_inicio" class="font-weight-bold">Data Início</label>
                                    <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="data_fim" class="font-weight-bold">Data Fim</label>
                                    <input type="date" class="form-control" id="data_fim" name="data_fim">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="produto_id" class="font-weight-bold">Produto</label>
                                    <select class="form-control" id="produto_id" name="produto_id">
                                        <option value="">Todos os produtos</option>
                                    </select>
                                </div>
                            </div>
                            
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search mr-1"></i>
                                    Gerar Relatório
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" id="btnLimparFiltros">
                                    <i class="fas fa-eraser mr-1"></i>
                                    Limpar Filtros
                                </button>
                                <button type="button" class="btn btn-info ml-2" id="btnImprimir" disabled>
                                    <i class="fas fa-print mr-1"></i>
                                    Imprimir
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Tabela de Resultados -->
    <div class="row" id="resultadosSection" style="display: none;">
        <div class="col-12">
            <div class="card card-modern">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table mr-2"></i>
                        Movimentações de Estoque
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-modern" id="tabelaRelatorio">
                            <thead class="table-dark">
                                <tr>
                                    <th width="80px">
                                        <i class="fas fa-calendar mr-1"></i>
                                        Data/Hora
                                    </th>
                                    <th>
                                        <i class="fas fa-user mr-1"></i>
                                        Funcionário
                                    </th>
                                    <th>
                                        <i class="fas fa-building mr-1"></i>
                                        Centro de Custo
                                    </th>
                                    <th>
                                        <i class="fas fa-boxes mr-1"></i>
                                        Produtos Retirados
                                    </th>
                                    <th width="80px">
                                        <i class="fas fa-sort-numeric-down mr-1"></i>
                                        Total Itens
                                    </th>
                                    <th>
                                        <i class="fas fa-comment mr-1"></i>
                                        Observações
                                    </th>
                                </tr>
                            </thead>
                            <tbody id="tabelaBody">
                                <!-- Dados serão inseridos via JavaScript -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div>
                            <small class="text-muted">
                                Mostrando <span id="registroInicio">0</span> a <span id="registroFim">0</span> 
                                de <span id="totalRegistros">0</span> registros
                            </small>
                        </div>
                        <nav>
                            <ul class="pagination pagination-sm mb-0" id="paginacao">
                                <!-- Paginação será inserida via JavaScript -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado Inicial -->
    <div class="row" id="estadoInicial">
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Relatório de Estoque</h4>
                <p class="text-muted">Configure os filtros acima e clique em "Gerar Relatório" para visualizar os dados</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    

    
    .table-modern {
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-modern th {
        background: #343a40;
        color: white;
        font-weight: 600;
        font-size: 13px;
        padding: 12px 8px;
        border: none;
    }
    
    .table-modern td {
        padding: 10px 8px;
        border-bottom: 1px solid #dee2e6;
        font-size: 13px;
    }
    
    .table-modern tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .badge-tipo {
        font-size: 11px;
        padding: 4px 8px;
    }
    
    .badge-secondary {
        background-color: #6c757d;
        color: white;
        font-size: 11px;
        padding: 3px 6px;
        margin-bottom: 2px;
        display: inline-block;
    }
    
    .badge-info {
        background-color: #17a2b8;
        color: white;
        font-size: 12px;
        padding: 4px 8px;
    }
    
    .badge-primary {
        background-color: #007bff;
        color: white;
        font-size: 12px;
        padding: 4px 8px;
        font-weight: bold;
    }
</style>
@stop

@section('js')
<script>
// Sanitização básica para evitar XSS em HTML injetado via template strings (função global)
function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, function(c){
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;','\'':'&#39;'}[c]);
    });
}

$(document).ready(function() {
    // Carregar dados iniciais
    carregarProdutos();
    
    // Configurar data padrão (último mês)
    const hoje = new Date();
    const mesPassado = new Date(hoje.getFullYear(), hoje.getMonth() - 1, hoje.getDate());
    
    $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
    $('#data_fim').val(hoje.toISOString().split('T')[0]);
    
    // Submissão do formulário
    $('#formFiltros').submit(function(e) {
        e.preventDefault();
        gerarRelatorio();
    });
    
    // Limpar filtros
    $('#btnLimparFiltros').click(function() {
        $('#formFiltros')[0].reset();
        $('#data_inicio').val(mesPassado.toISOString().split('T')[0]);
        $('#data_fim').val(hoje.toISOString().split('T')[0]);
        $('#resultadosSection').hide();
        $('#estadoInicial').show();
        $('#btnImprimir').prop('disabled', true);
    });
    
    // Botão Imprimir
    $('#btnImprimir').click(function() {
        imprimirRelatorio();
    });
});

function carregarProdutos() {
    $.get('/api/produtos')
        .done(function(produtos) {
            console.log('Produtos carregados:', produtos); // Debug
            let options = '<option value="">Todos os produtos</option>';
            produtos.forEach(function(produto) {
                options += `<option value="${produto.id}">${escapeHtml(produto.nome)}</option>`;
            });
            $('#produto_id').html(options);
        })
        .fail(function(xhr, status, error) {
            console.error('Erro ao carregar produtos:', error, xhr.responseText); // Debug
            $('#produto_id').html('<option value="">Erro ao carregar produtos</option>');
        });
}

// filtro de Centro de Custo removido

function gerarRelatorio() {
    const formData = new FormData($('#formFiltros')[0]);
    
    // Mostrar loading
    $('#tabelaBody').html(`
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                <br>
                <span class="text-muted">Carregando relatório...</span>
            </td>
        </tr>
    `);
    
    $('#estadoInicial').hide();
    $('#resultadosSection').show();
    
    $.ajax({
        url: '/api/relatorio-estoque',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                preencherTabelaComArmazenamento(response.dados);
                $('#btnImprimir').prop('disabled', false);
                
                // Atualizar contadores
                $('#totalRegistros').text(response.total_registros);
                $('#registroInicio').text(response.total_registros > 0 ? 1 : 0);
                $('#registroFim').text(response.total_registros);
            } else {
                mostrarErro('Erro ao gerar relatório: ' + response.message);
            }
        },
        error: function(xhr) {
            console.error('Erro:', xhr);
            let message = 'Erro interno do servidor';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            }
            mostrarErro('Erro ao gerar relatório: ' + message);
        }
    });
}

function preencherTabela(dados) {
    if (dados.length === 0) {
        $('#tabelaBody').html(`
            <tr>
                <td colspan="6" class="text-center py-4">
                    <i class="fas fa-inbox text-muted fa-2x mb-2"></i>
                    <br>
                    <span class="text-muted">Nenhuma movimentação encontrada no período</span>
                </td>
            </tr>
        `);
        return;
    }
    
    let html = '';
    dados.forEach(function(item) {
        let produtosHtml = '';
        item.produtos.forEach(function(produto, index) {
            if (index > 0) produtosHtml += '<br>';
            produtosHtml += `<span class="badge badge-secondary mr-1">${escapeHtml(produto.nome)} (${produto.quantidade})</span>`;
        });
        
        html += `
            <tr>
                <td>
                    <div class="font-weight-bold">${item.data}</div>
                    <small class="text-muted">${item.hora}</small>
                </td>
                <td>
                    <div class="font-weight-bold">${escapeHtml(item.funcionario.nome)}</div>
                    <small class="text-muted">${escapeHtml(item.funcionario.funcao)}</small>
                </td>
                <td>
                    <span class="badge badge-info">${escapeHtml(item.centro_custo.nome)}</span>
                </td>
                <td>
                    ${produtosHtml}
                </td>
                <td class="text-center">
                    <span class="badge badge-primary">${item.total_itens}</span>
                </td>
                <td>
                    <small class="text-muted">${item.observacoes || 'Sem observações'}</small>
                </td>
            </tr>
        `;
    });
    
    $('#tabelaBody').html(html);
}

function mostrarErro(mensagem) {
    $('#tabelaBody').html(`
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
                <br>
                    <span class="text-danger">${escapeHtml(mensagem)}</span>
            </td>
        </tr>
    `);
}

let dadosRelatorio = []; // Variável global para armazenar dados

function preencherTabelaComArmazenamento(dados) {
    dadosRelatorio = dados; // Armazenar dados para impressão
    preencherTabela(dados);
}

function imprimirRelatorio() {
    if (dadosRelatorio.length === 0) {
        alert('Nenhum dado disponível para impressão. Gere um relatório primeiro.');
        return;
    }
    
    // Obter dados dos filtros
    const dataInicio = $('#data_inicio').val();
    const dataFim = $('#data_fim').val();
    const produtoSelecionado = $('#produto_id option:selected').text();
    
    // Criar HTML da impressão
    let htmlImpressao = `
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Relatório de Movimentação de Estoque</title>
        <style>
            @page {
                size: A4;
                margin: 2cm 1.5cm;
            }
            
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: 'Arial', sans-serif;
                font-size: 12px;
                color: #333;
                line-height: 1.4;
            }
            
            .header {
                text-align: center;
                margin-bottom: 25px;
                padding-bottom: 15px;
                border-bottom: 2px solid #333;
            }
            
            .header h1 {
                font-size: 18px;
                font-weight: bold;
                margin-bottom: 5px;
                color: #000;
            }
            
            .header h2 {
                font-size: 14px;
                font-weight: normal;
                color: #666;
            }
            
            .filtros {
                margin-bottom: 20px;
                padding: 10px;
                background: #f8f9fa;
                border: 1px solid #ddd;
                border-radius: 5px;
            }
            
            .filtros h3 {
                font-size: 12px;
                margin-bottom: 8px;
                font-weight: bold;
            }
            
            .filtros p {
                font-size: 10px;
                margin: 2px 0;
                color: #555;
            }
            
            .table-container {
                margin-bottom: 20px;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10px;
            }
            
            thead {
                background: #333;
                color: white;
            }
            
            th {
                padding: 8px 6px;
                text-align: left;
                font-weight: bold;
                border: 1px solid #333;
                font-size: 10px;
            }
            
            td {
                padding: 6px 6px;
                border: 1px solid #ddd;
                font-size: 10px;
                vertical-align: top;
            }
            
            tr:nth-child(even) {
                background: #f9f9f9;
            }
            
            .funcionario {
                font-weight: bold;
                color: #000;
            }
            
            .funcao {
                font-size: 9px;
                color: #666;
                font-style: italic;
            }
            
            .centro-custo {
                background: #e3f2fd;
                padding: 2px 4px;
                border-radius: 3px;
                font-weight: bold;
                font-size: 9px;
                color: #1976d2;
            }
            
            .produto-item {
                margin-bottom: 2px;
                padding: 1px 3px;
                background: #f5f5f5;
                border-radius: 2px;
                font-size: 9px;
                border-left: 2px solid #28a745;
            }
            
            .total-badge {
                background: #007bff;
                color: white;
                padding: 2px 6px;
                border-radius: 3px;
                font-weight: bold;
                font-size: 9px;
            }
            
            .data-hora {
                font-weight: bold;
                color: #000;
            }
            
            .hora {
                font-size: 8px;
                color: #666;
            }
            
            .footer {
                margin-top: 20px;
                padding-top: 15px;
                border-top: 1px solid #ddd;
                text-align: center;
                font-size: 10px;
                color: #666;
            }
            

        </style>
    </head>
    <body>
        <div class="header">
            <h1>RELATÓRIO DE MOVIMENTAÇÃO DE ESTOQUE</h1>
            <h2>Sistema de Controle Interno</h2>
        </div>
        
        <div class="filtros">
            <h3>Filtros Aplicados:</h3>
            <p><strong>Período:</strong> ${formatarData(dataInicio)} até ${formatarData(dataFim)}</p>
            <p><strong>Produto:</strong> ${produtoSelecionado}</p>
            <p><strong>Data de Impressão:</strong> ${new Date().toLocaleString('pt-BR')}</p>
        </div>
        

        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th width="20%">Funcionário</th>
                        <th width="15%">Centro de Custo</th>
                        <th width="35%">Produtos</th>
                        <th width="10%">Total</th>
                        <th width="20%">Data</th>
                    </tr>
                </thead>
                <tbody>`;
    
    // Preencher dados da tabela
    dadosRelatorio.forEach(function(item) {
        let produtosHtml = '';
        item.produtos.forEach(function(produto) {
            produtosHtml += `<div class="produto-item">${produto.nome} (${produto.quantidade})</div>`;
        });
        
        htmlImpressao += `
                    <tr>
                        <td>
                            <div class="funcionario">${item.funcionario.nome}</div>
                            <div class="funcao">${item.funcionario.funcao}</div>
                        </td>
                        <td>
                            <span class="centro-custo">${item.centro_custo.nome}</span>
                        </td>
                        <td>
                            ${produtosHtml}
                        </td>
                        <td style="text-align: center;">
                            <span class="total-badge">${item.total_itens}</span>
                        </td>
                        <td>
                            <div class="data-hora">${item.data}</div>
                            <div class="hora">${item.hora}</div>
                        </td>
                    </tr>`;
    });
    
    htmlImpressao += `
                </tbody>
            </table>
        </div>
        
        <div class="footer">
            <p>Relatório gerado automaticamente pelo Sistema de Controle de Estoque</p>
            <p>Total de ${dadosRelatorio.length} movimentação(ões) encontrada(s) no período especificado</p>
        </div>
    </body>
    </html>`;
    
    // Abrir nova janela e imprimir
    const janelaImpressao = window.open('', '_blank');
    janelaImpressao.document.write(htmlImpressao);
    janelaImpressao.document.close();
    
    // Aguardar carregar e imprimir
    janelaImpressao.onload = function() {
        janelaImpressao.print();
        janelaImpressao.close();
    };
}

function formatarData(data) {
    if (!data) return 'Não especificado';
    return new Date(data + 'T00:00:00').toLocaleDateString('pt-BR');
}

function contarTotalItens() {
    return dadosRelatorio.reduce((total, item) => total + item.total_itens, 0);
}

function contarFuncionarios() {
    const funcionarios = new Set();
    dadosRelatorio.forEach(item => funcionarios.add(item.funcionario.id));
    return funcionarios.size;
}

function contarCentros() {
    const centros = new Set();
    dadosRelatorio.forEach(item => centros.add(item.centro_custo.id));
    return centros.size;
}
</script>
@stop