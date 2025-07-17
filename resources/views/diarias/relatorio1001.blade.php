@extends('adminlte::page')

@section('title', '1001 - Relatório de Diárias Gerentes')

@section('plugins.Sweetalert2', true)
@section('plugins.Datatables', true)

@push('meta')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush

@section('content_header')
<div class="header-highlight"></div>
<h1 class="m-0 text-dark"><i class="fas fa-users mr-2"></i>1001 - Relatório de Diárias Gerentes</h1>
@stop

@section('content')
    <!-- Modal de Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1" role="dialog" aria-labelledby="modalDetalhesLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalDetalhesLabel">Detalhes das Diárias</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped" id="tabela-detalhes">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Departamento</th>
                                <th>Função</th>
                                <th>Valor</th>
                                <th>Data</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Área de Filtro -->
    <div class="container-fluid screen-only">
        <!-- Link temporário para teste de hash -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light section-header d-flex justify-content-between align-items-center py-2">
                <h5 class="card-title mb-0"><i class="fas fa-filter mr-2"></i>Filtro de Relatórios</h5>
            </div>
            <div class="card-body">
                <form id="filtro-relatorio">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="form-group">
                                <label for="gerente"><strong>Gerente:</strong></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </div>
                                    <select class="form-control" id="gerente" name="gerente" required>
                                        <option value="">Selecione um gerente</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="data_inicial"><strong>Data Inicial:</strong></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="date" class="form-control" id="data_inicial" name="data_inicial" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="data_final"><strong>Data Final:</strong></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                    </div>
                                    <input type="date" class="form-control" id="data_final" name="data_final" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-search mr-2"></i>Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Diárias Agrupadas -->
        <div class="card shadow-sm">
            <div class="card-header bg-light section-header py-2">
                <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>Resultados</h5>
            </div>
            <div class="card-body p-0">
                <div id="lista-diarias" class="p-3">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>Utilize os filtros acima para buscar os dados.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Área da Tabela (Apenas para Impressão) -->
    <div id="relatorio-print" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: white; z-index: 9999; padding: 20px; overflow: visible !important;">
        <div class="header-print">
            <h3 class="text-center" style="font-size: 16px; margin-bottom: 5px;">1001 - Relatório de Diárias Gerentes</h3>
            <p class="text-center" id="periodo-relatorio" style="font-size: 12px; margin-bottom: 15px;"></p>
        </div>
        <table id="tabela-diarias" style="width: 100%; border-collapse: collapse; border: 1px solid black;">
            <thead>
                <tr>
                    <th style="width: 18%; background-color: black; color: white; border: 1px solid black; text-align: left; padding: 5px 8px; font-size: 12px;">Nome</th>
                    <th style="width: 18%; background-color: black; color: white; border: 1px solid black; text-align: left; padding: 5px 8px; font-size: 12px;">Departamento</th>
                    <th style="width: 18%; background-color: black; color: white; border: 1px solid black; text-align: left; padding: 5px 8px; font-size: 12px;">Função</th>
                    <th style="width: 12%; background-color: black; color: white; border: 1px solid black; text-align: left; padding: 5px 8px; font-size: 12px;">Valor</th>
                    <th style="width: 14%; background-color: black; color: white; border: 1px solid black; text-align: left; padding: 5px 8px; font-size: 12px;">Data</th>
                    <th style="width: 20%; background-color: black; color: white; border: 1px solid black; text-align: left; padding: 5px 8px; font-size: 12px;">Assinatura</th>
                </tr>
            </thead>
            <tbody id="tabela-body">
            </tbody>
        </table>
        <div class="assinaturas-container" style="display: flex; justify-content: space-between; margin-top: 30px;">
            <div class="assinatura" style="text-align: center; width: 300px; position: relative;">
                <p class="hash-text" style="font-size: 8px; word-break: break-all; margin-bottom: 5px; height: 20px;"></p>
                <div class="linha" style="border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                <p id="nome-gerente" style="margin: 0; font-size: 10px;"><strong><span id="nome-admin">Administrator</span></strong></p>
                <p style="margin: 0; font-size: 10px;"><strong>GERENTE</strong></p>
                <img id="assinatura-gerente" src="" alt="Assinatura do Gerente" style="display: none; max-width: 200px; margin-top: -30px;">
            </div>
            <div class="assinatura" style="text-align: center; width: 300px; position: relative;">
                <p style="font-size: 8px; margin-bottom: 5px; height: 20px; visibility: hidden;">espaço reservado</p>
                <div class="linha" style="border-bottom: 1px solid #000; margin-bottom: 5px;"></div>
                <p style="margin: 0; font-size: 10px;"><strong>Responsável Departamento Pessoal</strong></p>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        /* Destaque azul no topo */
        .header-highlight {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #3b82f6, #4f46e5, #8b5cf6);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.7);
            z-index: 100;
            margin-top: -1px;
        }
        
        .content-header {
            position: relative;
            padding-top: 1.5rem;
            box-shadow: 0 4px 12px -5px rgba(59, 130, 246, 0.15);
            margin-bottom: 1.5rem;
            background: linear-gradient(180deg, #f9fafb 0%, rgba(249, 250, 251, 0) 100%);
        }
        
        .section-header {
            background-color: #f8fafc;
            border-bottom: 1px solid #edf2f7;
        }
        
        /* Cards modernos */
        .card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        
        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }
        
        .shadow-sm {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05) !important;
        }
        
        .card:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1) !important;
        }

        /* Estilos para Tela */
        .screen-only {
            display: block;
        }

        #relatorio-print {
            display: none;
        }

        /* Estilos para os grupos de diárias */
        .grupo-diarias {
            background: #fff;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .grupo-diarias:hover {
            background: #f8f9fa;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .grupo-diarias h5 {
            margin: 0;
            color: #2c3e50;
            font-size: 16px;
            font-weight: 600;
        }

        .grupo-diarias .info {
            font-size: 0.9em;
            color: #6c757d;
            margin-top: 8px;
        }

        /* Estilos para visualização e impressão */
        #relatorio-view {
            background: white;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        #relatorio-view table {
            width: 100%;
            border-collapse: collapse;
        }
        
        #relatorio-view th {
            background-color: #343a40;
            color: white;
            padding: 8px;
            text-align: center;
            border: 1px solid #000;
        }
        
        #relatorio-view td {
            padding: 8px;
            border: 1px solid #dee2e6;
            text-align: center;
        }
        
        #relatorio-view td:first-child {
            text-align: left;
        }

        /* Estilos para Impressão */
        @media print {
            @page {
                size: A4 landscape;
                margin: 10mm;
            }

            body * {
                visibility: hidden;
            }

            #relatorio-print, #relatorio-print * {
                visibility: visible;
                color-adjust: exact !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            #relatorio-print {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                background-color: white;
            }

            /* Título e período */
            #relatorio-print h3 {
                font-size: 16px !important;
                margin-bottom: 5px !important;
            }
            
            #relatorio-print #periodo-relatorio {
                font-size: 12px !important;
                margin-bottom: 15px !important;
            }

            /* Garantir que as bordas da tabela sejam visíveis na impressão */
            #tabela-diarias {
                border: 1px solid black !important;
                border-collapse: collapse !important;
                width: 100% !important;
            }
            
            #tabela-diarias th {
                background-color: black !important;
                color: white !important;
                border: 1px solid black !important;
                text-align: left !important;
                padding: 5px 8px !important;
                font-size: 12px !important;
            }
            
            #tabela-diarias td {
                border: 1px solid black !important;
                padding: 5px 8px !important;
                text-align: left !important;
                font-size: 11px !important;
            }
            
            /* Estilos para a assinatura */
            .hash-text {
                font-size: 8px !important;
                height: 20px !important;
            }
            
            .assinatura p {
                font-size: 10px !important;
            }

            /* Esconder elementos de navegação */
            .main-footer, .main-header, .content-header, .screen-only {
                display: none !important;
            }
        }
        
        /* Estilo específico para o modo de impressão */
        body.printing {
            background: white !important;
        }
        
        body.printing .content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }
    </style>
@stop

@section('js')
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.bootstrap4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/1.7.0/js/buttons.colVis.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Definir datas padrão (mês atual)
            const hoje = new Date();
            const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            const ultimoDiaMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
            
            document.getElementById("data_inicial").valueAsDate = primeiroDiaMes;
            document.getElementById("data_final").valueAsDate = ultimoDiaMes;
            
            // Carregar lista de gerentes
            carregarGerentes();
            
            // Adicionar listener para o formulário
            document.getElementById('filtro-relatorio').addEventListener('submit', function(e) {
                e.preventDefault();
                buscarDiarias();
            });
        });
        
        // Função para carregar a lista de gerentes
        function carregarGerentes() {
            fetch('/api/gerentes-com-diarias')
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro ao carregar gerentes: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        const selectGerente = document.getElementById('gerente');
                        
                        // Limpar opções existentes, mantendo apenas a primeira
                        while (selectGerente.options.length > 1) {
                            selectGerente.remove(1);
                        }
                        
                        // Adicionar gerentes ao select
                        data.data.forEach(gerente => {
                            const option = document.createElement('option');
                            option.value = gerente;
                            option.textContent = gerente;
                            selectGerente.appendChild(option);
                        });
                    } else {
                        const selectGerente = document.getElementById('gerente');
                        
                        // Limpar opções existentes, mantendo apenas a primeira
                        while (selectGerente.options.length > 1) {
                            selectGerente.remove(1);
                        }
                        
                        // Adicionar mensagem de que não há gerentes
                        const option = document.createElement('option');
                        option.value = "";
                        option.textContent = "Nenhum gerente encontrado com diárias";
                        option.disabled = true;
                        selectGerente.appendChild(option);
                    }
                })
                .catch(error => {
                    // Erro ao carregar gerentes
                    alert("Erro ao carregar lista de gerentes: " + error.message);
                });
        }

        function buscarDiarias() {
            const gerente = document.getElementById("gerente").value;
            const dataInicial = document.getElementById("data_inicial").value;
            const dataFinal = document.getElementById("data_final").value;

            if (!gerente || !dataInicial || !dataFinal) {
                // Usar SweetAlert2 se disponível, senão usar alert padrão
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Preencha todos os campos!'
                    });
                } else {
                    alert("Preencha todos os campos!");
                }
                return;
            }

            if (new Date(dataInicial) > new Date(dataFinal)) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro',
                        text: 'A data inicial não pode ser posterior à data final!'
                    });
                } else {
                    alert("A data inicial não pode ser posterior à data final!");
                }
                return;
            }

            // Mostrar carregamento
            document.getElementById("lista-diarias").innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Carregando...</span>
                    </div>
                    <p class="mt-2 text-muted">Buscando dados, aguarde...</p>
                </div>
            `;

            fetch(`/relatorios/buscar-diarias-gerentes?gerente=${encodeURIComponent(gerente)}&data_inicial=${encodeURIComponent(dataInicial)}&data_final=${encodeURIComponent(dataFinal)}`)
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => {
                            throw new Error(err.message || 'Erro na requisição: ' + response.statusText);
                        });
                    }
                    return response.json();
                })
                .then(result => {
                    if (result.success) {
                        if (!result.data || !result.data.length) {
                            document.getElementById("lista-diarias").innerHTML = `
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                                    <p>Nenhum registro encontrado para o período selecionado.</p>
                                </div>
                            `;
                            return;
                        }
                        mostrarDiariasAgrupadas(result.data);
                    } else {
                        document.getElementById("lista-diarias").innerHTML = `
                            <div class="text-center py-5 text-muted">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <p>Erro ao buscar dados: ${result.message}</p>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById("lista-diarias").innerHTML = `
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <p>Ocorreu um erro ao buscar os dados: ${error.message}</p>
                        </div>
                    `;
                });
        }

        function mostrarDiariasAgrupadas(diarias) {
            const listaContainer = document.getElementById("lista-diarias");
            listaContainer.innerHTML = "";

            // Agrupar diárias por chave
            const grupos = diarias.reduce((acc, diaria) => {
                if (!acc[diaria.chave]) {
                    acc[diaria.chave] = {
                        diarias: [],
                        total: 0,
                        gerente: diaria.gerente,
                        assinatura: diaria.assinatura_gerente,
                        data_inclusao: diaria.data_inclusao
                    };
                }
                acc[diaria.chave].diarias.push(diaria);
                acc[diaria.chave].total += parseFloat(diaria.diaria || 0);
                return acc;
            }, {});

            // Criar elementos para cada grupo
            Object.entries(grupos).forEach(([chave, grupo], index) => {
                const div = document.createElement("div");
                div.className = "grupo-diarias";
                div.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <h5><i class="far fa-calendar-alt mr-2"></i>Diárias ${grupo.gerente} ${index + 1}</h5>
                        <span class="badge badge-primary">${grupo.diarias.length} diária(s)</span>
                    </div>
                    <div class="info d-flex justify-content-between mt-2">
                        <span><i class="fas fa-money-bill-wave mr-1"></i> Valor total: R$ ${grupo.total.toFixed(2).replace(".", ",")}</span>
                        <button class="btn btn-sm btn-outline-primary imprimir-btn" data-index="${index}">
                            <i class="fas fa-print mr-1"></i>Imprimir
                        </button>
                    </div>
                    <div class="mt-1">
                        <small>Data de Inclusão: ${formatarDataBrasileira(grupo.data_inclusao)}</small>
                    </div>
                `;
                
                listaContainer.appendChild(div);
            });
            
            // Armazenar os grupos para acesso posterior
            window.gruposDiarias = Object.values(grupos);
            
            // Adicionar listeners após criar todos os elementos
            document.querySelectorAll('.imprimir-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const index = parseInt(this.getAttribute('data-index'));
                    const grupo = window.gruposDiarias[index];
                    preencherTabela(grupo.diarias, grupo.assinatura, grupo.gerente);
                });
            });
            
            // Adicionar listeners de clique nos grupos
            document.querySelectorAll('.grupo-diarias').forEach((div, index) => {
                div.addEventListener('click', function() {
                    const grupo = window.gruposDiarias[index];
                    preencherTabela(grupo.diarias, grupo.assinatura, grupo.gerente);
                });
            });
        }
        
        function formatarData(dataString) {
            const data = new Date(dataString);
            if (isNaN(data.getTime())) {
                return "Data Inválida";
            }
            return data.toLocaleDateString('pt-BR');
        }
        
        function formatarDataBrasileira(dataISO) {
            if (!dataISO) return "";
            const data = new Date(dataISO);
            const dia = String(data.getDate()).padStart(2, '0');
            const mes = String(data.getMonth() + 1).padStart(2, '0');
            const ano = data.getFullYear();
            const horas = String(data.getHours()).padStart(2, '0');
            const minutos = String(data.getMinutes()).padStart(2, '0');
            return `${dia}/${mes}/${ano} ${horas}:${minutos}`;
        }

        function formatarDataSimples(data) {
            const [ano, mes, dia] = data.split('-');
            return `${dia}/${mes}/${ano}`;
        }
        
        function preencherTabela(diarias, assinaturaGerente, nomeGerente) {
            // Limpar o conteúdo existente
            const tabela = document.getElementById("tabela-body");
            tabela.innerHTML = '';
            
            // Preencher com os novos dados
            diarias.forEach(diaria => {
                const tr = document.createElement("tr");
                const valor = isNaN(parseFloat(diaria.diaria)) ? 0 : parseFloat(diaria.diaria);
                tr.innerHTML = `
                    <td style="border: 1px solid black; padding: 5px 8px; text-align: left; font-size: 11px;">${diaria.nome || ''}</td>
                    <td style="border: 1px solid black; padding: 5px 8px; text-align: left; font-size: 11px;">${diaria.departamento || ''}</td>
                    <td style="border: 1px solid black; padding: 5px 8px; text-align: left; font-size: 11px;">${diaria.funcao || ''}</td>
                    <td style="border: 1px solid black; padding: 5px 8px; text-align: left; font-size: 11px;">R$ ${valor.toFixed(2).replace(".", ",")}</td>
                    <td style="border: 1px solid black; padding: 5px 8px; text-align: left; font-size: 11px;">${formatarDataBrasileira(diaria.data_inclusao)}</td>
                    <td style="border: 1px solid black; padding: 5px 8px; text-align: left; font-size: 11px;"></td>
                `;
                tabela.appendChild(tr);
            });
            
            // Pegar a chave do primeiro registro para exibir como hash
            const hashText = document.querySelector('.hash-text');
            if (diarias && diarias.length > 0 && diarias[0].chave) {
                hashText.textContent = diarias[0].chave;
            } else {
                hashText.textContent = '';
            }
            
            // Atualizar assinatura do gerente
            const imgAssinatura = document.getElementById("assinatura-gerente");
            if (assinaturaGerente) {
                imgAssinatura.src = assinaturaGerente;
                imgAssinatura.style.display = "block";
            } else {
                imgAssinatura.style.display = "none";
            }

            // Atualizar nome do gerente
            document.getElementById("nome-admin").textContent = nomeGerente || "Administrator";
            
            // Atualizar o período no relatório
            const dataInicialFormatada = formatarDataSimples(document.getElementById("data_inicial").value);
            const dataFinalFormatada = formatarDataSimples(document.getElementById("data_final").value);
            document.getElementById("periodo-relatorio").textContent = 
                `Período: ${dataInicialFormatada} a ${dataFinalFormatada}`;
            
            // Exibir a área de impressão
            const printDiv = document.getElementById("relatorio-print");
            printDiv.style.display = "block";
            
            // Abrir a janela de impressão
            setTimeout(function() {
                window.print();
                // Ocultar a área de impressão após fechar a janela de impressão
                setTimeout(function() {
                    printDiv.style.display = "none";
                }, 1000);
            }, 500);
        }
    </script>
@stop