@extends('adminlte::page')

@section('title', '1002 - Relatório Recursos Humanos')

@section('content_header')
<div class="header-highlight"></div>
<h1 class="m-0 text-dark"><i class="fas fa-users mr-2"></i>1002 - Relatório Recursos Humanos</h1>
@stop

@section('content')
    <!-- Área de Filtro -->
    <div class="container-fluid">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-light section-header d-flex justify-content-between align-items-center py-2">
                <h5 class="card-title mb-0"><i class="fas fa-filter mr-2"></i>Filtro de Relatórios</h5>
            </div>
            <div class="card-body">
                <form id="filtro-relatorio">
                    <div class="row">
                        <div class="col-md-3">
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
                        <div class="col-md-3">
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="gerente"><strong>Gerente:</strong></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="fas fa-user-tie"></i>
                                        </span>
                                    </div>
                                    <select class="form-control" id="gerente" name="gerente">
                                        <option value="">Todos os gerentes</option>
                                        <!-- As opções serão carregadas dinamicamente via JavaScript -->
                                    </select>
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

        <!-- Área dos Resultados -->
        <div class="card shadow-sm">
            <div class="card-header bg-light section-header py-2">
                <h5 class="card-title mb-0"><i class="fas fa-list mr-2"></i>Resultados</h5>
            </div>
            <div class="card-body p-0">
                <div id="cartoes-container" class="p-3">
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-search fa-3x mb-3"></i>
                        <p>Utilize os filtros acima para buscar os dados.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        /* Esconder o aviso específico */
        div:contains("Este relatório mostra apenas as diárias do usuário Administrador"),
        .alert-info:contains("Este relatório mostra apenas"),
        .bg-info:contains("Este relatório mostra apenas"),
        [class*="info"]:contains("Este relatório mostra apenas") {
            display: none !important;
        }
        
        /* Esconde especificamente o aviso azul com ícone de informação */
        .alert-info, 
        .bg-info, 
        div[class*="bg-info"], 
        [style*="background-color: #17a2b8"],
        [style*="background-color: rgb(23, 162, 184)"] {
            display: none !important;
        }
        
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

        /* Cartões na Tela */
        #cartoes-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10mm;
            padding: 5mm;
            width: 100%;
            margin: 0 auto;
        }

        .diaria-card {
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: auto;
            min-height: 220px;
        }
        
        .diaria-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .diaria-card .cartao-conteudo {
            display: flex;
            flex-direction: column;
            height: 100%;
            justify-content: space-between;
        }
        
        .diaria-card .cartao-info {
            flex: 1;
        }
        
        .diaria-card .cartao-rodape {
            margin-top: auto;
            width: 100%;
        }

        .diaria-card p {
            margin: 5px 0;
            line-height: 1.4;
            font-size: 14px;
        }

        .diaria-card p.info-text {
            font-size: 14px;
            font-weight: bold;
            margin: 5px 0;
        }

        .diaria-card p strong {
            font-size: 15px;
            color: #333;
            font-weight: bold;
        }

        .diaria-card p.assinatura {
            margin-top: 15px;
            font-size: 14px;
            font-weight: bold;
            border-top: 1px dashed #ccc;
            padding-top: 10px;
            margin-bottom: 0;
        }
        
        /* Configuração do container de impressão (invisível na tela) */
        #print-container {
            display: none;
            visibility: hidden;
        }

        /* Ajustes para Impressão */
        @media print {
            @page {
                size: A4 portrait;
                margin: 10mm 8mm; /* Margens reduzidas */
            }

            /* Remover elementos desnecessários */
            .main-sidebar, 
            .main-header,
            .main-footer,
            nav, 
            .card, 
            .content-header,
            .btn, 
            header, 
            footer, 
            .screen-only,
            .no-print,
            form,
            .content-wrapper,
            .container-fluid,
            .content {
                display: none !important;
            }
            
            /* Exibir apenas o container de impressão */
            body * {
                visibility: hidden !important;
                display: none !important;
            }
            
            body {
                margin: 0 !important;
                padding: 0 !important;
                background: white !important;
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
            }
            
            #print-container,
            #print-container *,
            #cartoes-impressao,
            #cartoes-impressao * {
                visibility: visible !important;
                display: block !important;
            }
            
            /* Tornar o container de impressão visível */
            #print-container {
                display: block !important;
                visibility: visible !important;
                position: relative !important;
                top: 0 !important;
                left: 0 !important;
                width: 100% !important;
                height: auto !important;
                background: white !important;
                z-index: 9999 !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
                page-break-inside: auto !important;
            }
            
            #cartoes-impressao {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                grid-gap: 5mm !important;
                padding: 0 !important;
                margin: 0 !important;
                visibility: visible !important;
                width: 100% !important;
                background: white !important;
                page-break-after: always !important;
                grid-auto-flow: row !important;
            }

            /* Ocultar o container original de cartões na impressão */
            #cartoes-container {
                display: none !important;
                visibility: hidden !important;
            }

            /* Esconder todos os elementos que não fazem parte do layout de impressão */
            body > *:not(#print-container) {
                display: none !important;
                visibility: hidden !important;
            }

            /* Ajuste para tamanho fixo de cartão para formato A4 com 2 colunas */
            .diaria-card {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
                height: 75mm !important;
                min-height: 75mm !important;
                max-height: 75mm !important;
                width: 100% !important;
                display: block !important;
                overflow: visible !important;
                box-sizing: border-box !important;
                border: 0.5px solid #000 !important;
                padding: 2.5mm !important;
                margin: 0 0 2.5mm 0 !important;
                visibility: visible !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                background: white !important;
            }
            
            .diaria-card .cartao-conteudo {
                display: flex !important;
                flex-direction: column !important;
                justify-content: space-between !important;
                height: 100% !important;
                visibility: visible !important;
            }
            
            .diaria-card .cartao-info {
                flex-grow: 1 !important;
                visibility: visible !important;
                display: block !important;
                margin-bottom: 8mm !important;
            }
            
            .diaria-card .cartao-rodape {
                visibility: visible !important;
                display: block !important;
                margin-top: auto !important;
                width: 100% !important;
            }
            
            .diaria-card p {
                margin: 1mm 0 !important; /* Reduzido a margem */
                line-height: 1.3 !important; /* Espaçamento entre linhas um pouco maior */
                font-size: 11px !important; /* Fonte um pouco maior */
                visibility: visible !important;
                display: block !important;
                white-space: normal !important;
                overflow: visible !important;
                word-wrap: break-word !important;
            }
            
            .diaria-card p.info-text {
                font-size: 11px !important; /* Fonte um pouco maior */
                margin: 1mm 0 !important; /* Reduzido a margem */
                visibility: visible !important;
                display: block !important;
            }
            
            .diaria-card p strong {
                font-size: 12px !important;
            }
            
            .diaria-card p.assinatura {
                font-size: 11px !important;
                margin-top: 3mm !important;
                padding-top: 2mm !important;
                border-top: 0.5px dashed #999 !important;
            }
        }
    </style>
@stop

@section('js')
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Remover aviso sobre "Este relatório mostra apenas as diárias do usuário Administrador"
            document.querySelectorAll('.alert, .bg-info, [class*="info"]').forEach(el => {
                if (el.textContent.includes('Este relatório mostra apenas as diárias do usuário') || 
                    el.textContent.includes('Administrador')) {
                    el.style.display = 'none';
                    // Tenta remover o elemento completamente
                    try { el.remove(); } catch(e) {}
                }
            });

            document.getElementById("filtro-relatorio").addEventListener("submit", function(event) {
                event.preventDefault();
                buscarRecursosHumanos();
            });
            
            // Definir datas padrão (mês atual)
            const hoje = new Date();
            const primeiroDiaMes = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
            const ultimoDiaMes = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
            
            document.getElementById("data_inicial").valueAsDate = primeiroDiaMes;
            document.getElementById("data_final").valueAsDate = ultimoDiaMes;
            
            // Carregar lista de gerentes
            carregarGerentes();
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
                        
                        // Manter apenas a primeira opção "Todos os gerentes"
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
                        
                        // Manter apenas a primeira opção "Todos os gerentes"
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
                    console.error("Erro ao carregar lista de gerentes:", error);
                });
        }

        function buscarRecursosHumanos() {
            const dataInicial = document.getElementById("data_inicial").value;
            const dataFinal = document.getElementById("data_final").value;
            const gerente = document.getElementById("gerente").value;

            if (!dataInicial || !dataFinal) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    text: 'Selecione as datas para continuar!'
                });
                return;
            }

            if (new Date(dataInicial) > new Date(dataFinal)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'A data inicial não pode ser posterior à data final!'
                });
                return;
            }
            
            // Mostrar carregamento
            document.getElementById("cartoes-container").innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Carregando...</span>
                    </div>
                    <p class="mt-2 text-muted">Buscando dados, aguarde...</p>
                </div>
            `;
            
            // Construir URL com parâmetros
            let url = `/relatorios/buscar-recursos-humanos?data_inicial=${encodeURIComponent(dataInicial)}&data_final=${encodeURIComponent(dataFinal)}`;
            
            // Adicionar gerente à URL se estiver selecionado
            if (gerente) {
                url += `&gerente=${encodeURIComponent(gerente)}`;
            }

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data && data.data.length > 0) {
                        gerarCartoesDiarias(data.data);
                    } else {
                        document.getElementById("cartoes-container").innerHTML = `
                            <div class="text-center py-5">
                                <i class="fas fa-exclamation-circle fa-3x text-warning mb-3"></i>
                                <p class="text-muted">${data.message || 'Nenhum registro encontrado para o período selecionado.'}</p>
                            </div>`;
                    }
                })
                .catch(error => {
                    document.getElementById("cartoes-container").innerHTML = `
                        <div class="text-center py-5">
                            <i class="fas fa-exclamation-triangle fa-3x text-danger mb-3"></i>
                            <p class="text-danger">Erro ao carregar dados: ${error.message}</p>
                        </div>`;
                });
        }

        function gerarCartoesDiarias(diarias) {
            // Limpar o container existente de cartões
            document.getElementById('cartoes-container').innerHTML = '';
            
            // Criar ou limpar o container de impressão
            let printContainer = document.getElementById('print-container');
            if (!printContainer) {
                printContainer = document.createElement('div');
                printContainer.id = 'print-container';
                printContainer.style.display = 'none'; // Oculto por padrão, visível apenas na impressão
                document.body.appendChild(printContainer);
            } else {
                printContainer.innerHTML = '';
            }
            
            // Criar o container específico para os cartões de impressão
            let cartoesImpressao = document.createElement('div');
            cartoesImpressao.id = 'cartoes-impressao';
            printContainer.appendChild(cartoesImpressao);
            
            // Container para visualização em tela
            const containerTela = document.getElementById('cartoes-container');
            containerTela.style.display = 'grid';
            
            // Gerar o HTML para cada diária
            diarias.forEach(function(diaria) {
                // Template HTML do cartão com estrutura otimizada
                const templateCartao = `
                    <div class="cartao-conteudo">
                        <div class="cartao-info">
                            <p><strong>Nome:</strong> ${diaria.nome || 'N/A'}</p>
                            <p><strong>Setor:</strong> ${diaria.departamento || 'N/A'}</p>
                            <p><strong>Função:</strong> ${diaria.funcao || 'N/A'}</p>
                            <p><strong>Diária:</strong> R$ ${diaria.diaria ? parseFloat(diaria.diaria).toFixed(2).replace('.', ',') : '0,00'}</p>
                            <p><strong>Ref.:</strong> ${diaria.referencia || 'N/A'}</p>
                            <p><strong>Observações:</strong> ${diaria.observacao || 'N/A'}</p>
                        </div>
                        <div class="cartao-rodape">
                            <p class="assinatura">Assinatura: _____________________________</p>
                        </div>
                    </div>
                `;
                
                // Criar o cartão para visualização na tela
                let cartao = document.createElement('div');
                cartao.className = 'diaria-card';
                cartao.innerHTML = templateCartao;
                containerTela.appendChild(cartao);
                
                // Criar um cartão específico para impressão
                let cartaoImpressao = document.createElement('div');
                cartaoImpressao.className = 'diaria-card';
                cartaoImpressao.innerHTML = templateCartao;
                cartoesImpressao.appendChild(cartaoImpressao);
            });
            
            // Remover botões de impressão existentes antes de adicionar um novo
            const botoesAntigos = document.querySelectorAll('.btn-imprimir-cartoes');
            botoesAntigos.forEach(btn => btn.remove());
            
            // Adicionar apenas um botão de impressão no final dos resultados
            const divBotao = document.createElement('div');
            divBotao.className = 'text-center mt-3 mb-3';
            
            const btnImprimir = document.createElement('button');
            btnImprimir.className = 'btn btn-primary mt-3 btn-imprimir-cartoes';
            btnImprimir.id = 'btn-imprimir-cartoes';
            btnImprimir.innerHTML = '<i class="fas fa-print mr-2"></i>Imprimir Cartões';
            btnImprimir.onclick = function() {
                // Preparar a página para impressão
                const printStyle = document.createElement('style');
                printStyle.id = 'print-style-helper';
                printStyle.textContent = `
                    @media print {
                        @page {
                            size: A4 portrait;
                            margin: 10mm 8mm; /* Margens reduzidas */
                        }
                        
                        .diaria-card {
                            page-break-inside: avoid !important;
                            break-inside: avoid !important;
                            height: 75mm !important;
                            min-height: 75mm !important;
                            max-height: 75mm !important;
                            box-sizing: border-box !important;
                            padding: 2.5mm !important;
                        }
                        
                        #cartoes-impressao {
                            display: grid !important;
                            grid-template-columns: repeat(2, 1fr) !important;
                            grid-gap: 5mm !important;
                            grid-auto-flow: row !important;
                        }
                        
                        .diaria-card .cartao-conteudo {
                            display: flex !important;
                            flex-direction: column !important;
                            justify-content: space-between !important;
                            height: 100% !important;
                        }
                        
                        .diaria-card .cartao-rodape {
                            margin-top: auto !important;
                        }
                        
                        .diaria-card p {
                            margin: 1mm 0 !important;
                            line-height: 1.3 !important;
                            font-size: 11px !important;
                        }
                        
                        .diaria-card p strong {
                            font-size: 12px !important;
                        }
                        
                        .diaria-card p.assinatura {
                            font-size: 11px !important;
                            margin-top: 3mm !important;
                            padding-top: 2mm !important;
                            border-top: 0.5px dashed #999 !important;
                        }
                    }
                `;
                document.head.appendChild(printStyle);
                
                // Usar timeout para garantir que tudo está carregado antes de imprimir
                setTimeout(function() {
                    window.print();
                    // Remover o estilo auxiliar após a impressão
                    document.getElementById('print-style-helper')?.remove();
                }, 300);
            };
            
            divBotao.appendChild(btnImprimir);
            
            // Verificar se o botão já existe antes de adicionar
            if (!document.getElementById('btn-imprimir-cartoes')) {
                containerTela.parentNode.appendChild(divBotao);
            }
        }

        // Função para formatar a data no formato brasileiro
        function formatarData(data) {
            if (!data) return 'N/A';
            
            // Verifica se é uma data no formato YYYY-MM-DD
            if (data.includes('-')) {
                const partes = data.split('-');
                if (partes.length === 3) {
                    return `${partes[2]}/${partes[1]}/${partes[0]}`;
                }
            }
            
            // Verifica se é um timestamp ou data ISO
            const dataObj = new Date(data);
            if (!isNaN(dataObj.getTime())) {
                const dia = String(dataObj.getDate()).padStart(2, '0');
                const mes = String(dataObj.getMonth() + 1).padStart(2, '0');
                const ano = dataObj.getFullYear();
                return `${dia}/${mes}/${ano}`;
            }
            
            // Se não conseguir formatar, retorna o valor original
            return data;
        }
        
        // Mantendo a função formatarDataBrasileira para compatibilidade
        function formatarDataBrasileira(data) {
            return formatarData(data);
        }
    </script>
@stop 