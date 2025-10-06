@extends('adminlte::page')

@section('title', 'Frota - Conferência de NF')
@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-file-search text-primary mr-3"></i>
            Conferência de NF
        </h1>
        <p class="text-muted mt-1 mb-0">Consulte e visualize os lotes de abastecimentos consolidados em NF</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="container-fluid">
    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-filter mr-2"></i>
                Filtros de Busca
            </h5>
        </div>
        <div class="card-body">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="dataInicio">Data Inicial</label>
                    <input type="date" id="dataInicio" class="form-control">
                </div>
                <div class="form-group col-md-3">
                    <label for="dataFim">Data Final</label>
                    <input type="date" id="dataFim" class="form-control">
                </div>
                <div class="form-group col-md-4">
                    <label for="numeroNF">Número da NF</label>
                    <input type="text" id="numeroNF" class="form-control" placeholder="Digite o número da NF...">
                </div>
                <div class="form-group col-md-2 d-flex align-items-end">
                    <button class="btn btn-primary btn-block" onclick="carregarLotes()">
                        <i class="fas fa-search mr-1"></i>
                        Buscar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabela de Resultados -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-list mr-2"></i>
                Lotes de NF Consolidados
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped" id="tabelaLotes">
                    <thead>
                        <tr>
                            <th>Lote</th>
                            <th>Número NF</th>
                            <th>Qtd Itens</th>
                            <th>Total Litros</th>
                            <th>Total Valor</th>
                            <th>Data Criação</th>
                            <th class="text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalhes -->
<div class="modal fade" id="modalLote" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-eye mr-2"></i>
                    Detalhes do Lote
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="cabecalhoLote" class="mb-3 p-3" style="background-color: #f8f9fa; border-radius: 5px; border-left: 4px solid #007bff;"></div>
                <div class="table-responsive">
                    <table class="table table-striped" id="tabelaItens">
                        <thead>
                            <tr>
                                <th>Data</th>
                                <th>Veículo</th>
                                <th>KM</th>
                                <th>Litros</th>
                                <th>Valor</th>
                                <th>Preço/L</th>
                                <th>Posto</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="imprimirLote()">
                    <i class="fas fa-print mr-1"></i>
                    Imprimir
                </button>
                <button type="button" class="btn btn-primary" data-dismiss="modal">
                    Fechar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.card {
    box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    margin-bottom: 1rem;
}
.table th {
    background-color: #f8f9fa;
    border-top: none;
}
.btn-sm {
    margin: 0 2px;
}
#modalLote .modal-dialog {
    max-width: 1000px;
}
#modalLote .table {
    font-size: 0.9rem;
}
@media (max-width: 767.98px) {
    .form-row > .col-md-2 {
        margin-top: 10px;
    }
    #modalLote .modal-dialog {
        max-width: 95%;
        margin: 0.5rem;
    }
}
</style>
@stop

@section('js')
<script>
$(function(){
    // Carregar dados iniciais
    carregarLotes();
});

function carregarLotes(){
    const p = {
        data_inicio: $('#dataInicio').val(),
        data_fim: $('#dataFim').val(),
        numero_nf: $('#numeroNF').val()
    };
    
    // Indicador de loading
    const tbody = $('#tabelaLotes tbody');
    tbody.html('<tr><td colspan="7" class="text-center"><i class="fas fa-spinner fa-spin"></i> Carregando...</td></tr>');
    
    $.get('/frota/api/relatorios/conferencia-nf', p).done(rows => {
        tbody.empty();
        if(!rows.length){
            tbody.html('<tr><td colspan="7" class="text-center text-muted">Nenhum lote encontrado</td></tr>');
            return;
        }
        rows.forEach(l => {
            const dataFormatada = l.created_at ? new Date(l.created_at).toLocaleDateString('pt-BR') + ' ' + new Date(l.created_at).toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'}) : '';
            tbody.append(`
                <tr>
                    <td><span class="badge badge-primary">#${l.id}</span></td>
                    <td><strong>${escapeHtml(l.numero_nf||'')}</strong></td>
                    <td><span class="badge badge-info">${l.qtd_itens}</span></td>
                    <td>${Number(l.total_litros).toFixed(3)}L</td>
                    <td><strong class="text-success">R$ ${Number(l.total_valor).toFixed(2)}</strong></td>
                    <td>${dataFormatada}</td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-info" onclick="verLote(${l.id})" title="Ver detalhes">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    }).fail(xhr => {
        tbody.html('<tr><td colspan="7" class="text-center text-danger">Erro ao carregar dados</td></tr>');
        Swal.fire('Erro', 'Falha ao carregar os lotes.', 'error');
    });
}

function verLote(id){
    $.get(`/frota/api/relatorios/conferencia-nf/${id}`).done(res => {
        if(!res.ok){ 
            Swal.fire('Erro', res.message||'Falha ao carregar.', 'error'); 
            return; 
        }
        const { lote, itens } = res;
        
        // Cabeçalho do lote
        $('#cabecalhoLote').html(`
            <strong>Lote #${lote.id}</strong> | 
            <strong>NF:</strong> ${lote.numero_nf} | 
            <strong>Itens:</strong> ${lote.qtd_itens} | 
            <strong>Total Litros:</strong> ${Number(lote.total_litros).toFixed(3)}L | 
            <strong>Total Valor:</strong> R$ ${Number(lote.total_valor).toFixed(2)}
        `);
        
        // Tabela de itens
        const tbody = $('#tabelaItens tbody');
        tbody.empty();
        (itens||[]).forEach(i => {
            const data = i.data ? new Date(i.data+'T00:00:00').toLocaleDateString('pt-BR') : '';
            const veiculo = i.placa || i.veiculo_id;
            tbody.append(`
                <tr>
                    <td>${data}</td>
                    <td><span class="badge badge-secondary">${escapeHtml(veiculo)}</span></td>
                    <td>${Number(i.km).toLocaleString()} km</td>
                    <td>${Number(i.litros)}L</td>
                    <td class="text-success">R$ ${Number(i.valor).toFixed(2)}</td>
                    <td>R$ ${Number(i.preco_litro).toFixed(3)}</td>
                    <td>${escapeHtml(i.posto||'')}</td>
                </tr>
            `);
        });
        
        // Armazenar o ID do lote atual para impressão
        window.currentLoteId = id;
        $('#modalLote').modal('show');
    }).fail(xhr => {
        Swal.fire('Erro', 'Falha ao carregar detalhes do lote.', 'error');
    });
}

function escapeHtml(s){
    const d = document.createElement('div'); 
    d.innerText = (s==null?'':String(s)); 
    return d.innerHTML;
}

// Buscar com Enter
$('#numeroNF').on('keypress', function(e){
    if(e.which === 13) carregarLotes();
});

// Função para imprimir diretamente
function imprimirLote(){
    if(!window.currentLoteId) return;
    
    // Criar iframe oculto para carregar e imprimir
    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    iframe.src = `/frota/relatorios/conferencia-nf/imprimir/${window.currentLoteId}`;
    
    iframe.onload = function() {
        iframe.contentWindow.print();
        // Remover iframe após 2 segundos
        setTimeout(() => document.body.removeChild(iframe), 2000);
    };
    
    document.body.appendChild(iframe);
}
</script>
@stop


