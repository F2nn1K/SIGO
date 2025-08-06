@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
<h1>Dashboard</h1>
@stop

@section('content')
<div class="container-fluid">
    @if(!empty($dadosGraficos))
        <!-- Dashboard com gráficos para usuários com permissão "Controle de Estoque" -->
        


        <!-- Gráficos -->
        <div class="row">
            <!-- Gráfico de Centros de Custo -->
            <div class="col-md-6">
                <div class="card card-modern">
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

            <!-- Gráfico de Produtos -->
            <div class="col-md-6">
                <div class="card card-modern">
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



    @else
        <!-- Dashboard padrão para usuários sem permissão -->
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="welcome-card">
                    <div class="welcome-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="welcome-content">
                        <h2 class="welcome-title">Bem-vindo ao Sistema BRS!</h2>
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
</style>
@stop

@section('js')
@if(!empty($dadosGraficos))
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
$(document).ready(function() {
    // Dados dos gráficos vindos do controller
    const dadosGraficos = @json($dadosGraficos);
    
    // Configurar Gráfico de Centros de Custo (Pizza)
    const ctxCentros = document.getElementById('chartCentros').getContext('2d');
    new Chart(ctxCentros, {
        type: 'doughnut',
        data: {
            labels: dadosGraficos.centros_mais_pedidos.map(item => item.centro_nome),
            datasets: [{
                data: dadosGraficos.centros_mais_pedidos.map(item => item.total_pedidos),
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
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                title: {
                    display: true,
                    text: 'Distribuição por Centro de Custo',
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


});
</script>
@endif
@stop 