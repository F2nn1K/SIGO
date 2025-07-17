@extends('adminlte::page')

@section('title', '1001 - Relatório de Diárias Gerentes')

@section('content_header')
    <h1>1001 - Relatório de Diárias Gerentes</h1>
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
    <div class="screen-only">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-filter"></i> Filtro de Relatórios</h3>
            </div>
            <div class="card-body">
                <form id="filtro-relatorio">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="gerente"><strong>Gerente:</strong></label>
                                <select class="form-control" id="gerente" name="gerente" required>
                                    <option value="">Selecione um gerente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="data_inicial"><strong>Data Inicial:</strong></label>
                                <input type="date" class="form-control" id="data_inicial" name="data_inicial" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="data_final"><strong>Data Final:</strong></label>
                                <input type="date" class="form-control" id="data_final" name="data_final" required>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- DataTable para visualização na tela -->
        <div class="card mt-4">
            <div class="card-header">
                <h3 class="card-title">Diárias Encontradas</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-primary" id="btn-imprimir">
                        <i class="fas fa-print"></i> Imprimir Relatório
                    </button>
                </div>
            </div>
            <div class="card-body">
                <table id="tabela-diarias-datatable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Gerente</th>
                            <th>Departamento</th>
                            <th>Função</th>
                            <th>Valor Total</th>
                            <th>Data</th>
                            <th>Chave</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Área da Tabela (Apenas para Impressão) -->
    <div id="relatorio-print">
        <div class="relatorio-container">
            <div class="relatorio-content">
                <div class="header-print">
                    <p class="text-center" id="periodo-relatorio"></p>
                </div>
                <table class="table" id="tabela-diarias">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Departamento</th>
                            <th>Função</th>
                            <th>Valor</th>
                            <th>Referente</th>
                            <th>Observação</th>
                            <th>Assinatura</th>
                        </tr>
                    </thead>
                    <tbody id="tabela-body">
                    </tbody>
                </table>
            </div>
            <div class="relatorio-footer">
                <div class="assinaturas-container">
                    <div class="assinatura">
                        <p class="chave-assinatura" style="font-family: monospace; font-size: 10px; margin-bottom: 5px; display: block; min-height: 15px;"></p>
                        <div class="linha"></div>
                        <p>Responsável Gerente</p>
                    </div>
                    <div class="assinatura">
                        <p style="visibility: hidden; font-size: 10px; margin-bottom: 5px;">.</p>
                        <div class="linha"></div>
                        <p>Responsável Departamento Pessoal</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap4.min.css">
    <style>
        /* Estilos para Tela */
        .screen-only {
            display: block;
        }

        #relatorio-print {
            display: none;
        }

        /* Estilos para Impressão */
        @media print {
            @page {
                size: A4 landscape;
                margin: 5mm;
            }

            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .screen-only,
            .main-sidebar,
            .main-header,
            .content-header,
            [class*="livewire-"],
            #livewireStyles,
            #livewireScripts,
            style[data-livewire],
            script[data-livewire],
            [id*="livewire"],
            [class*="livewire"] {
                display: none !important;
                visibility: hidden !important;
                width: 0 !important;
                height: 0 !important;
                position: absolute !important;
                overflow: hidden !important;
                clip: rect(0 0 0 0) !important;
                -webkit-transform: scale(0) !important;
                transform: scale(0) !important;
            }

            #relatorio-print {
                display: block !important;
                position: relative !important;
                width: 100%;
                background: white;
                padding: 5mm;
                height: calc(100% - 10mm);
            }

            #periodo-relatorio {
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 12px;
                color: black;
                text-align: center;
            }

            .table {
                width: 100%;
                border-collapse: collapse;
                font-size: 11px;
                margin-bottom: 15px;
                background: white !important;
                table-layout: fixed;
            }

            .table th {
                background-color: #000 !important;
                color: #fff !important;
                font-weight: bold;
                padding: 6px;
                border: 1px solid #000;
                text-align: center;
                vertical-align: middle;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .table td {
                padding: 6px;
                border: 1px solid #000;
                vertical-align: middle;
                text-align: center;
                background-color: white !important;
                color: black !important;
                word-wrap: break-word;
                overflow-wrap: break-word;
                hyphens: auto;
                line-height: 1.3;
            }

            /* Ajustando larguras das colunas */
            .table th:nth-child(1),
            .table td:nth-child(1) {
                width: 15%;
                text-align: left;
                white-space: normal;
                word-break: break-word;
            }

            .table th:nth-child(2),
            .table td:nth-child(2) {
                width: 10%;
                white-space: normal;
                word-break: break-word;
            }

            .table th:nth-child(3),
            .table td:nth-child(3) {
                width: 15%;
                white-space: normal;
                word-break: break-word;
            }

            .table th:nth-child(4),
            .table td:nth-child(4) {
                width: 8%;
                white-space: nowrap;
            }

            .table th:nth-child(5),
            .table td:nth-child(5) {
                width: 20%;
                white-space: normal;
                word-break: break-word;
            }

            .table th:nth-child(6),
            .table td:nth-child(6) {
                width: 20%;
                white-space: normal;
                word-break: break-word;
            }

            .table th:nth-child(7),
            .table td:nth-child(7) {
                width: 12%;
            }

            .relatorio-container {
                display: flex;
                flex-direction: column;
                height: 100%;
                max-height: calc(100vh - 10mm);
            }

            .relatorio-content {
                flex: 1;
                overflow: hidden;
            }

            .relatorio-footer {
                margin-top: 30px;
            }

            .assinaturas-container {
                display: flex;
                justify-content: space-between;
                padding: 20px 50px 0;
                page-break-inside: avoid;
            }

            .assinatura {
                text-align: center;
                width: 250px;
                display: flex;
                flex-direction: column;
                align-items: center;
                margin-top: 10px;
            }

            .linha {
                border-bottom: 1px solid #000;
                margin-bottom: 8px;
                margin-top: 15px;
                width: 100%;
            }

            .assinatura p {
                margin: 4px 0 0 0;
                font-size: 11px;
                color: black;
                text-align: center;
                line-height: 1.3;
            }

            .chave-assinatura {
                font-family: monospace !important;
                font-size: 10px !important;
                margin-bottom: 4px !important;
                min-height: 12px;
                line-height: 1.2;
            }

            .content-wrapper {
                margin-left: 0 !important;
                padding-top: 0 !important;
            }

            /* Evita quebra de página */
            #tabela-diarias, .assinaturas-container {
                page-break-inside: avoid;
            }
        }
        
        .btn-eye {
            color: #17a2b8;
        }
        .btn-eye:hover {
            color: #0f7a8a;
        }
    </style>
@stop

@section('js')
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap4.min.js"></script>
    <script>
        $(document).ready(function() {
            // Função para obter o valor correto da diária
            function obterValorDiaria(item) {
                // Verifica os possíveis campos de valor em ordem de prioridade
                if (item.valor_diaria) return parseFloat(item.valor_diaria) || 0;
                if (item.diaria) return parseFloat(item.diaria) || 0;
                if (item.valor_total) return parseFloat(item.valor_total) || 0;
                if (item.valor) return parseFloat(item.valor) || 0;
                return 0;
            }

            // Função para formatar valor monetário
            function formatarValor(valor) {
                if (!valor) return 'R$ 0,00';
                
                // Remove formatação existente se houver
                if (typeof valor === 'string') {
                    // Remove R$, pontos e espaços, troca vírgula por ponto
                    valor = valor.replace(/[R$\s.]/g, '').replace(',', '.');
                }
                
                // Converte para número e formata
                const valorNumerico = parseFloat(valor);
                if (isNaN(valorNumerico)) return 'R$ 0,00';
                
                return 'R$ ' + valorNumerico.toFixed(2).replace('.', ',');
            }

            // Função para agrupar diárias por chave
            function agruparDiariasPorChave(diarias) {
                const grupos = {};
                
                diarias.forEach(function(diaria) {
                    if (!grupos[diaria.chave]) {
                        grupos[diaria.chave] = {
                            gerente: diaria.gerente,
                            departamento: diaria.departamento,
                            funcao: diaria.funcao,
                            valor_total: 0,
                            data_inclusao: diaria.data_inclusao,
                            chave: diaria.chave,
                            quantidade_funcionarios: 0,
                            diarias: []
                        };
                    }
                    
                    grupos[diaria.chave].diarias.push(diaria);
                    grupos[diaria.chave].valor_total += obterValorDiaria(diaria);
                    grupos[diaria.chave].quantidade_funcionarios++;
                });
                
                return Object.values(grupos);
            }

            // Função para carregar os gerentes
            function carregarGerentes() {
                $.ajax({
                    url: '/api/gerentes',
                    method: 'GET',
                    success: function(response) {
                        if (response.success) {
                            var select = $('#gerente');
                            select.empty();
                            select.append('<option value="">Selecione um gerente</option>');
                            
                            response.data.forEach(function(gerente) {
                                select.append(`<option value="${gerente.gerente}">${gerente.gerente}</option>`);
                            });
                        } else {
                            alert('Erro ao carregar lista de gerentes: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao carregar gerentes: ' + error);
                    }
                });
            }

            // Variável global para armazenar todos os dados
            var dadosCompletos = [];

            // Carrega os gerentes quando a página iniciar
            carregarGerentes();

            // Inicializa o DataTable
            var dataTable = $('#tabela-diarias-datatable').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
                },
                columns: [
                    { data: 'gerente' },
                    { data: 'departamento' },
                    { data: 'funcao' },
                    { 
                        data: 'valor_total',
                        render: function(data, type, row) {
                            return formatarValor(data);
                        }
                    },
                    { 
                        data: 'data_inclusao',
                        render: function(data) {
                            return new Date(data).toLocaleDateString('pt-BR');
                        }
                    },
                    { data: 'chave' },
                    { 
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return `<button class="btn btn-xs btn-default text-teal mx-1 shadow btn-detalhes" title="Visualizar Diárias">
                                        <i class="fa fa-lg fa-fw fa-eye"></i>
                                    </button>`;
                        }
                    }
                ],
                order: [[4, 'desc']], // Ordena por data decrescente
                responsive: true,
                autoWidth: false
            });

            // Manipulador do formulário de busca
            $('#filtro-relatorio').on('submit', function(e) {
                e.preventDefault();
                
                var gerente = $('#gerente').val();
                var dataInicial = $('#data_inicial').val();
                var dataFinal = $('#data_final').val();

                // Faz a requisição AJAX
                $.ajax({
                    url: '/relatorios/buscar-diarias-gerentes',
                    method: 'GET',
                    data: {
                        gerente: gerente,
                        data_inicial: dataInicial,
                        data_final: dataFinal
                    },
                    success: function(response) {
                        if (response.success) {
                            // Armazena os dados completos
                            dadosCompletos = response.data;
                            
                            // Limpa e recarrega o DataTable
                            dataTable.clear();
                            
                            // Agrupa os dados por chave
                            var dadosAgrupados = agruparDiariasPorChave(response.data.map(function(item) {
                                return {
                                    ...item,
                                    gerente: gerente
                                };
                            }));

                            // Adiciona os dados agrupados ao DataTable
                            dataTable.rows.add(dadosAgrupados).draw();

                            // Atualiza também a tabela de impressão
                            atualizarTabelaImpressao(dadosAgrupados);

                            // Manipula os dados retornados
                            renderizarTabelaDiarias(response.data);
                            // Salva dados para reutilização na impressão
                            window.diariasData = response.data;
                            // Atualiza o título com o período
                            document.getElementById('periodo-relatorio').textContent = 
                                `Período: ${formatarData(dataInicial)} a ${formatarData(dataFinal)}`;
                            // Atualiza o total de diárias
                            calcularEExibirTotais(response.data);
                        } else {
                            alert('Nenhuma diária encontrada para o período selecionado.');
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('Erro ao buscar diárias: ' + error);
                    }
                });
            });

            // Manipulador do botão de detalhes
            $('#tabela-diarias-datatable').on('click', '.btn-detalhes', function() {
                var data = dataTable.row($(this).closest('tr')).data();
                
                // Encontra todas as diárias com a mesma chave
                var diariasMesmaChave = dadosCompletos.filter(function(item) {
                    return item.chave === data.chave;
                });
                
                // Atualiza a chave de assinatura
                $('.chave-assinatura').text(data.chave);
                
                // Atualiza o período no cabeçalho da impressão
                $('#periodo-relatorio').text(
                    'Período: ' + $('#data_inicial').val() + ' a ' + $('#data_final').val()
                );

                // Preenche a tabela de impressão com os detalhes
                var tbody = $('#tabela-body');
                tbody.empty();

                // Adiciona todas as diárias com a mesma chave
                diariasMesmaChave.forEach(function(diaria) {
                    tbody.append(`
                        <tr>
                            <td>${diaria.nome || diaria.gerente}</td>
                            <td>${diaria.departamento}</td>
                            <td>${diaria.funcao}</td>
                            <td>${formatarValor(obterValorDiaria(diaria))}</td>
                            <td>${diaria.referencia || ''}</td>
                            <td>${diaria.observacao || ''}</td>
                            <td></td>
                        </tr>
                    `);
                });
                
                window.print();
            });

            // Função para atualizar a tabela de impressão
            function atualizarTabelaImpressao(dados) {
                var tbody = $('#tabela-body');
                tbody.empty();

                dados.forEach(function(grupo) {
                    grupo.diarias.forEach(function(diaria) {
                        tbody.append(`
                            <tr>
                                <td>${diaria.nome || diaria.gerente}</td>
                                <td>${diaria.departamento}</td>
                                <td>${diaria.funcao}</td>
                                <td>${formatarValor(obterValorDiaria(diaria))}</td>
                                <td>${diaria.referencia || ''}</td>
                                <td>${diaria.observacao || ''}</td>
                                <td>${diaria.chave}</td>
                            </tr>
                        `);
                    });
                });

                // Atualiza o período no cabeçalho da impressão
                $('#periodo-relatorio').text(
                    'Período: ' + $('#data_inicial').val() + ' a ' + $('#data_final').val()
                );
            }

            // Manipulador do botão de impressão
            $('#btn-imprimir').click(function() {
                window.print();
            });
        });
    </script>
@stop 