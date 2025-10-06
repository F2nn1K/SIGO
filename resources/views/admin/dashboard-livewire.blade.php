@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(!empty($alertasLicenciamento))
    <!-- Aviso de licenciamento a vencer -->
    <div class="lic-alert-wrapper">
        <div class="lic-alert-card">
            <div class="lic-header">
                <i class="fas fa-id-card-alt mr-2"></i>
                Licenciamentos a vencer
                <span class="badge badge-danger ml-2">{{ count($alertasLicenciamento) }}</span>
            </div>
            <div class="lic-list">
                @foreach($alertasLicenciamento as $al)
                    <div class="lic-item lic-{{ $al['status'] }}">
                        <div class="lic-main">
                            <span class="lic-placa">{{ $al['placa'] }}</span>
                            <span class="lic-modelo">{{ $al['modelo'] }}</span>
                        </div>
                        <div class="lic-meta">
                            <span class="lic-date">{{ \Carbon\Carbon::parse($al['proximo'])->format('d/m/Y') }}</span>
                            <span class="lic-dias">{{ $al['dias'] < 0 ? abs($al['dias']).'d venc.' : $al['dias'].'d' }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    @if(!empty($dadosFrota))
        <!-- Cards de Estatísticas da Frota -->
        <div class="row mb-4">
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $dadosFrota['cards']['total_veiculos'] }}</h3>
                        <p>Total de Veículos</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-car"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $dadosFrota['cards']['veiculos_ativos'] }}</h3>
                        <p>Disponíveis</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $dadosFrota['cards']['veiculos_em_uso'] }}</h3>
                        <p>Em Uso</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-road"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="small-box bg-primary">
                    <div class="inner">
                        <h3>{{ $dadosFrota['cards']['viagens_mes'] }}</h3>
                        <p>Viagens (Mês)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-route"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="small-box bg-secondary">
                    <div class="inner">
                        <h3>{{ $dadosFrota['cards']['km_percorrido_mes'] }}</h3>
                        <p>KM (Mês)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-tachometer-alt"></i>
                    </div>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-3">
                <div class="small-box bg-danger">
                    <div class="inner">
                        <h3>R$ {{ $dadosFrota['cards']['custo_total_mes'] }}</h3>
                        <p>Custos (Mês)</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>

        

        

        <!-- Removidos: cards de Manutenções/Utilização e gráfico Top KM por Veículo -->
    @endif

    @if(!empty($dadosGraficos))
        <!-- Estoque: Centros + Produtos -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-building mr-2"></i>
                            Centros com Mais Pedidos
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-info">{{ $dadosGraficos['periodo']['inicio'] }} - {{ $dadosGraficos['periodo']['fim'] }}</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartCentros" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cube mr-2"></i>
                            Produtos Mais Retirados
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-success">Top 8</span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartProdutos" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linha adicional: gráficos de Pedidos de Compras (somente Admin) -->
        @if(optional(Auth::user()->profile)->name === 'Admin')
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Status de Pedidos (Mês)</h3>
                        <div class="card-tools"><span class="badge badge-success">Mês atual</span></div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartStatusPedidos" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-sitemap mr-2"></i>Top 5 CC por Valor Aprovado</h3>
                        <div class="card-tools"><span class="badge badge-info">Mês atual</span></div>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="chartCcValorAprovado" width="400" height="400"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    @endif

    @if(!empty($licenciamentoCompleto))
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-id-card-alt mr-2"></i>
                        Licenciamento - Todos os veículos
                    </h3>
                    <span class="badge badge-primary">Ano {{ date('Y') }}</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th style="width: 140px;">Placa</th>
                                    <th>Modelo</th>
                                    <th style="width: 160px;">Status</th>
                                    <th style="width: 140px;">Próximo pagamento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($licenciamentoCompleto as $row)
                                    <tr>
                                        <td><strong>{{ $row['placa'] }}</strong></td>
                                        <td class="text-muted">{{ $row['modelo'] }}</td>
                                        <td>
                                            <span class="badge {{ $row['status']==='Pendente' ? 'badge-warning' : 'badge-success' }}">{{ $row['status'] }}</span>
                                        </td>
                                        <td>{{ $row['proximo'] ? \Carbon\Carbon::parse($row['proximo'])->format('d/m/Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if(empty($dadosFrota) && empty($dadosGraficos))
        <!-- Dashboard padrão para usuários sem permissão -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="welcome-card">
                    <div class="welcome-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="welcome-content">
                        <h2 class="welcome-title">Bem-vindo ao SIGO!</h2>
                        <p class="welcome-subtitle">Olá, <strong>{{ Auth::user()->name }}</strong></p>
                        <p class="welcome-message">
                            É um prazer tê-lo conosco. Use o menu lateral para navegar pelas funcionalidades do sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
    /* Widget flutuante: Licenciamento a vencer */
    .lic-alert-wrapper { position: fixed; right: 18px; bottom: 18px; z-index: 1040; max-width: 360px; width: calc(100% - 36px); }
    .lic-alert-card { background: #fff; border: 1px solid #e9ecef; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); overflow: hidden; }
    .lic-header { background: linear-gradient(45deg,#ffeaea,#fff5f5); color: #b00020; font-weight: 700; padding: 10px 14px; border-bottom: 1px solid #f3dcdc; }
    .lic-list { max-height: 280px; overflow-y: auto; }
    .lic-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; border-bottom: 1px solid #f1f3f5; }
    .lic-item:last-child { border-bottom: 0; }
    .lic-main { display: flex; flex-direction: column; }
    .lic-placa { font-weight: 700; color: #2b2d42; }
    .lic-modelo { font-size: 12px; color: #6c757d; }
    .lic-meta { display: flex; gap: 8px; align-items: center; }
    .lic-date { font-size: 12px; background: #edf2ff; color: #2c7be5; padding: 2px 6px; border-radius: 6px; }
    .lic-dias { font-size: 12px; padding: 2px 6px; border-radius: 6px; }
    .lic-critico .lic-dias { background: #fff3cd; color: #8a6d3b; }
    .lic-aviso .lic-dias { background: #e7f5ff; color: #1c7ed6; }
    .lic-vencido .lic-dias { background: #ffe3e3; color: #c92a2a; }
    .welcome-subtitle {
        font-size: 20px;
        color: #007bff;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .card-modern {
        border: none;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }

    .card-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .chart-container {
        position: relative;
        height: 400px;
        width: 100%;
    }





    .card-header {
        background: linear-gradient(45deg, #f8f9fa, #e9ecef);
        border-bottom: 1px solid #dee2e6;
        border-radius: 12px 12px 0 0;
    }

    .badge {
        font-size: 0.75rem;
    }

    /* Animações suaves */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .card-modern {
        animation: fadeInUp 0.6s ease forwards;
    }

    /* Dashboard limpo e moderno */
    .card {
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: #fff;
        border-bottom: 1px solid #e9ecef;
        border-radius: 8px 8px 0 0;
        padding: 1rem 1.25rem;
    }

    .small-box {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: transform 0.2s ease;
    }

    .small-box:hover {
        transform: translateY(-2px);
    }

    .chart-container {
        position: relative;
        height: 350px;
        width: 100%;
    }

    .table-sm th, .table-sm td { padding: .4rem .6rem; }

    .summary-tile {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 0.75rem 1rem;
        height: 100%;
    }
    .summary-label {
        font-size: 0.85rem;
        color: #6c757d;
        margin-bottom: 0.25rem;
    }
    .summary-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #343a40;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        border-top: none;
        font-weight: 600;
        color: #495057;
        background-color: #f8f9fa;
    }

    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(0,0,0,.02);
    }

    /* Estilo compacto para KM por veículo */
    .km-card {
        box-shadow: 0 1px 6px rgba(0,0,0,0.06);
    }

    .km-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #e9ecef;
        padding: 0.5rem 1rem;
    }

    .km-item {
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 0.75rem;
        text-align: center;
        transition: all 0.2s ease;
        height: 100%;
    }

    .km-item:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.15);
    }

    .km-placa {
        font-weight: bold;
        font-size: 0.9rem;
        color: #495057;
        margin-bottom: 0.25rem;
    }

    .km-modelo {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.4rem;
    }

    .km-valor {
        font-size: 0.85rem;
        font-weight: 600;
        color: #007bff;
        background: #e7f3ff;
        padding: 0.2rem 0.5rem;
        border-radius: 4px;
    }

    /* Responsividade melhorada */
    @media (max-width: 768px) {
        .small-box .inner h3 {
            font-size: 1.8rem;
        }
        
        .chart-container {
            height: 300px;
        }
        
        .card-body {
            padding: 1rem;
        }

        .km-item {
            padding: 0.5rem;
        }

        .km-placa {
            font-size: 0.85rem;
        }

        .km-modelo {
            font-size: 0.75rem;
        }

        .km-valor {
            font-size: 0.8rem;
        }
    }

    @media (max-width: 576px) {
        .small-box .inner h3 {
            font-size: 1.5rem;
        }
        
        .small-box .inner p {
            font-size: 0.85rem;
        }
        
        .chart-container {
            height: 250px;
        }

        .km-item {
            padding: 0.4rem;
        }
    }
</style>
@stop

@section('js')
@if(!empty($dadosGraficos) || !empty($dadosFrota))
<script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
<script>
$(document).ready(function() {
    @if(!empty($dadosFrota))
    // Dados da frota vindos do controller
    const dadosFrota = @json($dadosFrota);
    


    /* removido: Gráfico de Consumo Mensal */
    /* new Chart(ctxConsumo, {
        type: 'line',
        data: {
            labels: dadosFrota.graficos.consumo_mensal.map(item => item.mes),
            datasets: [{
                label: 'Valor (R$)',
                data: dadosFrota.graficos.consumo_mensal.map(item => item.valor),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'R$ ' + value.toLocaleString('pt-BR');
                        }
                    }
                }
            }
        }
    }); */

    /* removido: Gráfico de Veículos Mais Utilizados */
    /* new Chart(ctxUtilizados, {
        type: 'bar',
        data: {
            labels: dadosFrota.graficos.veiculos_mais_utilizados.map(item => item.veiculo),
            datasets: [{
                label: 'Viagens',
                data: dadosFrota.graficos.veiculos_mais_utilizados.map(item => item.viagens),
                backgroundColor: dadosFrota.graficos.veiculos_mais_utilizados.map(item => item.cor),
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                },
                x: {
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45
                    }
                }
            }
        }
    }); */
    @endif

    @if(!empty($dadosGraficos))
    // Dados dos gráficos vindos do controller
    const dadosGraficos = @json($dadosGraficos);
    
    // Configurar Gráfico de Centros (Rosca)
    const ctxCentros = document.getElementById('chartCentros').getContext('2d');
    new Chart(ctxCentros, {
        type: 'doughnut',
        data: {
            labels: dadosGraficos.centros_mais_pedidos.map(item => item.centro_nome),
            datasets: [{
                data: dadosGraficos.centros_mais_pedidos.map(item => item.total_quantidade),
                backgroundColor: dadosGraficos.centros_mais_pedidos.map(item => item.cor),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { padding: 20, usePointStyle: true }
                },
                title: { display: true, text: 'Distribuição por Centro de Custo' }
            }
        }
    });

    // Configurar Gráfico de Produtos (Pizza)
    const ctxProdutos = document.getElementById('chartProdutos').getContext('2d');
    new Chart(ctxProdutos, {
        type: 'doughnut',
        data: {
            labels: dadosGraficos.produtos_mais_retirados.map(item => item.produto_nome),
            datasets: [{
                data: dadosGraficos.produtos_mais_retirados.map(item => item.total_quantidade),
                backgroundColor: dadosGraficos.produtos_mais_retirados.map(item => item.cor),
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                title: {
                    display: true,
                    text: 'Produtos por Quantidade',
                    font: {
                        size: 16,
                        weight: 'bold'
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    });

    // Gráfico Status de Pedidos (Mês) - Pizza
    (async () => {
        try {
            const hoje = new Date();
            const ini = new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().slice(0,10);
            const fim = new Date(hoje.getFullYear(), hoje.getMonth()+1, 0).toISOString().slice(0,10);
            const qs = `?data_ini=${ini}&data_fim=${fim}`;
            const pendentes = await fetch('/api/pedidos-pendentes-agrupados'+qs).then(r=>r.json()).then(j => (Array.isArray(j?.data) ? j.data.length : 0)).catch(()=>0);
            const aprovados = await fetch('/api/pedidos-aprovados-agrupados'+qs).then(r=>r.json()).then(j => (Array.isArray(j?.data) ? j.data.length : 0)).catch(()=>0);
            const rejeitados = await fetch('/api/pedidos-rejeitados-agrupados'+qs).then(r=>r.json()).then(j => (Array.isArray(j?.data) ? j.data.length : 0)).catch(()=>0);
            const ctxStatusPedidos = document.getElementById('chartStatusPedidos').getContext('2d');
            new Chart(ctxStatusPedidos, {
                type: 'doughnut',
                data: {
                    labels: ['Pendentes','Aprovados','Rejeitados'],
                    datasets: [{ data: [pendentes, aprovados, rejeitados], backgroundColor: ['#ffc107','#28a745','#dc3545'], borderWidth:2, borderColor:'#fff' }]
                },
                options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ position:'bottom', labels:{ padding:20, usePointStyle:true } }, title:{ display:true, text:'Status de Pedidos (mês)', font:{ size:16, weight:'bold' } } } }
            });
        } catch(e) {}
    })();

    // Gráfico Top 5 CC por Valor Aprovado - Barras
    (async () => {
        try {
            const hoje = new Date();
            const ini = new Date(hoje.getFullYear(), hoje.getMonth(), 1).toISOString().slice(0,10);
            const fim = new Date(hoje.getFullYear(), hoje.getMonth()+1, 0).toISOString().slice(0,10);
            // Usa o mesmo endpoint do relatório, que já retorna por pedido com valor_total agregado
            const r = await fetch(`/api/relatorio-pedido-cc?data_ini=${ini}&data_fim=${fim}`);
            let json;
            try { json = await r.json(); } catch(e) { json = {}; }
            const rows = Array.isArray(json?.dados) ? json.dados : [];
            const somaPorCC = {};
            rows.forEach(p => {
                const nome = p.centro_custo_nome || p.centro_custo || '—';
                const valor = Number(p.valor_total || 0);
                somaPorCC[nome] = (somaPorCC[nome] || 0) + valor;
            });
            const pares = Object.entries(somaPorCC).sort((a,b)=>b[1]-a[1]).slice(0,5);
            const labels = pares.map(p=>p[0]);
            const valores = pares.map(p=>p[1]);
            const ctxCc = document.getElementById('chartCcValorAprovado').getContext('2d');
            new Chart(ctxCc, {
                type: 'bar',
                data: { labels, datasets: [{ label:'Valor Aprovado (R$)', data: valores, backgroundColor: '#17a2b8' }] },
                options: { responsive:true, maintainAspectRatio:false, plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:(ctx)=>'R$ '+Number(ctx.parsed.y).toLocaleString('pt-BR',{minimumFractionDigits:2}) } }, title:{ display:true, text:'Top 5 por Valor Aprovado' } }, scales:{ y:{ beginAtZero:true, ticks:{ callback:(v)=>'R$ '+Number(v).toLocaleString('pt-BR') } } } }
            });
        } catch(e) {}
    })();
    @endif

    // removido: top km por veículo
});
</script>
@endif
@stop 