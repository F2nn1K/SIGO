@extends('adminlte::page')

@section('title', 'Relatório DP')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-file-alt text-primary mr-3"></i>
            Relatório DP
        </h1>
        <p class="text-muted mt-1 mb-0">Consulte e baixe dossiês completos de funcionários</p>
    </div>
</div>
@stop

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="container-fluid">
    <!-- Card de Busca -->
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-search mr-2"></i>
                Buscar Funcionário
            </h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group mb-0">
                        <label for="busca_funcionario" class="font-weight-bold text-dark">
                            <i class="fas fa-user mr-1"></i>
                            Nome ou CPF do Funcionário
                        </label>
                        <input type="text" id="busca_funcionario" name="busca_funcionario" 
                               class="form-control form-control-lg" 
                               placeholder="Digite o nome ou CPF do funcionário..." 
                               autocomplete="off">
                        <small class="text-muted">
                            <i class="fas fa-info-circle mr-1"></i>
                            Digite pelo menos 3 caracteres para buscar
                        </small>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" id="btn-limpar" class="btn btn-outline-secondary btn-block mb-4">
                        <i class="fas fa-eraser mr-1"></i>
                        Limpar
                    </button>
                </div>
            </div>
            
            <!-- Lista de Funcionários Encontrados -->
            <div id="lista-funcionarios" style="display: none;">
                <hr class="my-4">
                <h5 class="mb-3 font-weight-bold text-dark">
                    <i class="fas fa-users mr-2 text-primary"></i>
                    Funcionários Encontrados
                </h5>
                <div id="funcionarios-container"></div>
            </div>
        </div>
    </div>

    <!-- Detalhes do Funcionário Selecionado -->
    <div id="area-funcionario" style="display: none;">
        <div class="card card-success card-outline">
            <div class="card-header bg-primary text-white">
                <h3 class="card-title">
                    <i class="fas fa-user-circle mr-2"></i>
                    Detalhes do Funcionário
                </h3>
                <div class="card-tools">
                    <button type="button" id="btn-voltar" class="btn btn-sm btn-light">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Voltar à Busca
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div id="funcionario-detalhes"></div>
            </div>
        </div>
    </div>

    <!-- Loading -->
    <div id="loading" style="display: none;">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                <h4 class="text-dark">Buscando funcionário...</h4>
                <p class="text-muted mb-0">Por favor, aguarde.</p>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
.funcionario-card {
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}

.funcionario-card:hover {
    background-color: #f8fafc;
    border-color: #3b82f6;
    transform: translateY(-3px);
    box-shadow: 0 8px 16px rgba(59,130,246,0.15);
}

.funcionario-card.selected {
    background-color: #eff6ff;
    border-color: #3b82f6;
    box-shadow: 0 8px 16px rgba(59,130,246,0.2);
}

.funcionario-card h5 {
    color: #1e293b;
    font-weight: 600;
    font-size: 1.1rem;
}

.funcionario-card p {
    color: #64748b;
    margin-bottom: 0.5rem;
}

.badge-status {
    font-size: 0.8rem;
    padding: 0.4em 0.8em;
    border-radius: 20px;
    font-weight: 600;
}

.status-trabalhando {
    background-color: #10b981;
    color: white;
}

.status-afastado {
    background-color: #f59e0b;
    color: #1e293b;
}

.status-ferias {
    background-color: #06b6d4;
    color: white;
}

.status-demitido {
    background-color: #ef4444;
    color: white;
}

.status-sem-status {
    background-color: #6b7280;
    color: white;
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    let funcionarioSelecionado = null;
    let timeoutBusca = null;
    
    // Busca de funcionários
    $('#busca_funcionario').on('input', function() {
        const termo = $(this).val().trim();
        
        // Limpar timeout anterior
        if (timeoutBusca) {
            clearTimeout(timeoutBusca);
        }
        
        // Se menos de 3 caracteres, limpar resultados
        if (termo.length < 3) {
            $('#lista-funcionarios').hide();
            $('#funcionarios-container').empty();
            return;
        }
        
        // Aguardar 500ms antes de buscar
        timeoutBusca = setTimeout(() => {
            buscarFuncionarios(termo);
        }, 500);
    });
    
    // Limpar busca
    $('#btn-limpar').on('click', function() {
        $('#busca_funcionario').val('');
        $('#lista-funcionarios').hide();
        $('#funcionarios-container').empty();
        $('#area-funcionario').hide();
        funcionarioSelecionado = null;
    });
    
    // Voltar à busca
    $('#btn-voltar').on('click', function() {
        $('#area-funcionario').hide();
        $('#lista-funcionarios').show();
        funcionarioSelecionado = null;
    });
    
    function buscarFuncionarios(termo) {
        $('#loading').show();
        
        // Buscar diretamente na tabela funcionarios
        $.ajax({
            url: '/api/funcionarios/buscar',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            data: { termo: termo },
            success: function(data) {
                $('#loading').hide();
                exibirFuncionarios(data);
            },
            error: function() {
                $('#loading').hide();
                Swal.fire('Erro!', 'Erro ao buscar funcionários', 'error');
            }
        });
    }
    
    function exibirFuncionarios(funcionarios) {
        let html = '';
        
        if (funcionarios.length === 0) {
            html = `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle mr-2"></i>
                    Nenhum funcionário encontrado com este nome ou CPF.
                </div>
            `;
        } else {
            funcionarios.forEach(funcionario => {
                const statusClass = `status-${funcionario.status ? funcionario.status.toLowerCase().replace(' ', '-') : 'sem-status'}`;
                const cpfFormatado = funcionario.cpf ? formatarCPF(funcionario.cpf) : 'Não informado';
                
                html += `
                    <div class="funcionario-card" onclick="selecionarFuncionario(${funcionario.id})">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="mb-1">
                                    <i class="fas fa-user mr-2 text-primary"></i>
                                    ${funcionario.nome}
                                </h5>
                                <p class="mb-1">
                                    <strong>CPF:</strong> ${cpfFormatado} &nbsp;|&nbsp;
                                    <strong>Função:</strong> ${funcionario.funcao || 'Não informado'}
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-calendar mr-1"></i>
                                    Cadastrado em: ${formatarDataHora(funcionario.created_at)}
                                </small>
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-status ${statusClass}">
                                    ${funcionario.status || 'Sem status'}
                                </span>
                                <br><br>
                                <button class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye mr-1"></i>
                                    Ver Detalhes
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
        }
        
        $('#funcionarios-container').html(html);
        $('#lista-funcionarios').show();
    }
    
    window.selecionarFuncionario = function(funcionarioId) {
        // Buscar dados do funcionário selecionado
        $('#loading').show();
        funcionarioSelecionado = { id: funcionarioId };
        
        $.ajax({
            url: `/api/funcionarios/${funcionarioId}`,
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json'
            },
            success: function(data) {
                $('#loading').hide();
                exibirDetalhesFuncionario(data);
                $('#lista-funcionarios').hide();
                $('#area-funcionario').show();
            },
            error: function() {
                $('#loading').hide();
                Swal.fire('Erro!', 'Erro ao carregar detalhes do funcionário', 'error');
            }
        });
    };
    
    function exibirDetalhesFuncionario(funcionario) {
        const statusClass = `status-${funcionario.status ? funcionario.status.toLowerCase().replace(' ', '-') : 'sem-status'}`;
        const cpfFormatado = funcionario.cpf ? formatarCPF(funcionario.cpf) : 'Não informado';
        
        let html = `
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <div class="mb-3">
                                <i class="fas fa-user-circle fa-5x text-primary" style="opacity: 0.8;"></i>
                            </div>
                            <h4 class="mb-3 font-weight-bold text-dark">${funcionario.nome}</h4>
                            <span class="badge badge-status ${statusClass}">
                                ${funcionario.status || 'Sem status'}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-8 mb-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="mb-4 font-weight-bold text-dark">
                                <i class="fas fa-info-circle text-primary mr-2"></i>
                                Informações do Funcionário
                            </h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <p class="mb-2">
                                        <strong class="text-muted">
                                            <i class="fas fa-id-card mr-2"></i>CPF
                                        </strong>
                                    </p>
                                    <p class="text-dark font-weight-500">${cpfFormatado}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p class="mb-2">
                                        <strong class="text-muted">
                                            <i class="fas fa-briefcase mr-2"></i>Função
                                        </strong>
                                    </p>
                                    <p class="text-dark font-weight-500">${funcionario.funcao || 'Não informado'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p class="mb-2">
                                        <strong class="text-muted">
                                            <i class="fas fa-venus-mars mr-2"></i>Sexo
                                        </strong>
                                    </p>
                                    <p class="text-dark font-weight-500">${funcionario.sexo || 'Não informado'}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <p class="mb-2">
                                        <strong class="text-muted">
                                            <i class="fas fa-calendar mr-2"></i>Data Cadastro
                                        </strong>
                                    </p>
                                    <p class="text-dark font-weight-500">${formatarDataHora(funcionario.created_at)}</p>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <button type="button" class="btn btn-primary btn-lg px-5" onclick="baixarDossie(${funcionario.id})">
                                    <i class="fas fa-download mr-2"></i>
                                    Baixar Dossiê Completo (ZIP)
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        if (funcionario.observacoes) {
            html += `
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="alert alert-info border-0 shadow-sm">
                            <h6 class="alert-heading font-weight-bold">
                                <i class="fas fa-info-circle mr-2"></i>
                                Observações
                            </h6>
                            <p class="mb-0">${funcionario.observacoes}</p>
                        </div>
                    </div>
                </div>
            `;
        }
        
        $('#funcionario-detalhes').html(html);
    }
    
    function formatarCPF(cpf) {
        if (!cpf) return '';
        return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    }
    
    function formatarDataHora(dataHora) {
        if (!dataHora) return '-';
        return new Date(dataHora).toLocaleString('pt-BR');
    }
    
    // Acionador do download do dossiê (ZIP) no mesmo aba
    window.baixarDossie = function(funcionarioId) {
        if (!funcionarioId) return;
        Swal.fire({
            title: 'Gerando arquivo...',
            html: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br><br>Coletando todos os documentos do funcionário...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });
        const url = `/documentos-dp/funcionario/${funcionarioId}/arquivo-completo`;
        window.location.href = url;
        setTimeout(function(){ try { Swal.close(); } catch(e){} }, 4000);
    }

});
</script>
@stop
