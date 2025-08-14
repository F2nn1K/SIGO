@extends('adminlte::page')

@section('title', 'Inclusão de Documentos DP')

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0 text-dark font-weight-bold">
            <i class="fas fa-file-alt text-primary mr-3"></i>
            Inclusão de Documentos DP
        </h1>
        <p class="text-muted mt-1 mb-0">Cadastre os documentos necessários para admissão</p>
    </div>
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

    @if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show modern-alert">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        <strong>Erro!</strong> Verifique os dados informados.
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert">
            <i class="fas fa-times"></i>
        </button>
    </div>
    @endif

    <!-- Card Principal -->
    <div class="modern-card">
        <div class="card-header-modern">
            <h3 class="card-title-modern">
                <i class="fas fa-plus-circle mr-2 text-primary"></i>
                Cadastro de Documentos Necessários
            </h3>
        </div>
        <div class="card-body-modern">
            <form action="{{ route('documentos-dp.store') }}" method="POST" enctype="multipart/form-data" id="form-documentos">
                @csrf
                
                <!-- Seção de Informações do Funcionário -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h5 class="mb-4">
                            <i class="fas fa-user-circle mr-2 text-primary"></i>
                            Informações do Funcionário
                        </h5>
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="nome_funcionario" class="font-weight-bold text-muted mb-2">
                            <i class="fas fa-user mr-1"></i>
                            Nome do Funcionário <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control modern-input" id="nome_funcionario" name="nome_funcionario" 
                               value="{{ old('nome_funcionario') }}" required placeholder="Digite o nome completo">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="funcao" class="font-weight-bold text-muted mb-2">
                            <i class="fas fa-briefcase mr-1"></i>
                            Função <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control modern-input" id="funcao" name="funcao" 
                               value="{{ old('funcao') }}" required placeholder="Digite a função/cargo">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="cpf" class="font-weight-bold text-muted mb-2">
                            <i class="fas fa-id-card mr-1"></i>
                            CPF <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control modern-input" id="cpf" name="cpf" 
                               value="{{ old('cpf') }}" required placeholder="000.000.000-00" maxlength="14">
                    </div>
                    <div class="col-lg-6 mb-3">
                        <label for="sexo" class="font-weight-bold text-muted mb-2">
                            <i class="fas fa-venus-mars mr-1"></i>
                            Sexo <span class="text-danger">*</span>
                        </label>
                        <select class="form-control modern-input" id="sexo" name="sexo" required>
                            <option value="">Selecione...</option>
                            <option value="M" {{ old('sexo') == 'M' ? 'selected' : '' }}>Masculino</option>
                            <option value="F" {{ old('sexo') == 'F' ? 'selected' : '' }}>Feminino</option>
                        </select>
                    </div>
                </div>

                <!-- Seção de Documentos -->
                <div class="row mb-5">
                    <div class="col-12">
                        <h5 class="mb-4">
                            <i class="fas fa-file-alt mr-2 text-primary"></i>
                            Documentos Necessários
                            <span class="text-muted ml-2">(Selecione conforme necessário)</span>
                        </h5>
                        <p class="text-center text-muted mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Anexe os documentos conforme a necessidade de cada funcionário
                        </p>
                    </div>
                </div>

                <!-- Grid de Documentos -->
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <div class="document-card">
                            <div class="document-header">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="doc_fotos" name="documentos[]" value="02 fotos 3x4">
                                    <label class="custom-control-label document-label" for="doc_fotos">
                                        <i class="fas fa-camera mr-2 text-primary"></i>
                                        02 fotos 3x4
                                    </label>
                                </div>
                            </div>
                            <div class="document-actions">
                                <button type="button" class="btn btn-outline-primary btn-sm anexar-btn" data-target="arquivo_fotos">
                                    <i class="fas fa-paperclip mr-1"></i> Anexar
                                </button>
                                <input type="file" id="arquivo_fotos" name="arquivo_fotos" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                <small class="file-selected d-none text-success mt-2"></small>
                            </div>
                        </div>
                    </div>

                    <!-- Cards de Documentos -->
                    @php
                    $documentos = [
                        ['id' => 'carteira_saude', 'icon' => 'fas fa-clinic-medical', 'label' => 'Carteira de saúde atualizada com foto 3x4', 'desc' => 'Posto de saúde ou clínica'],
                        ['id' => 'encaminhamento', 'icon' => 'fas fa-file-prescription', 'label' => 'Encaminhamento para exame admissional', 'desc' => 'Fornecido pela empresa'],
                        ['id' => 'antecedente', 'icon' => 'fas fa-shield-alt', 'label' => 'Antecedente cível e criminal', 'desc' => 'Certidão negativa'],
                        ['id' => 'rg', 'icon' => 'fas fa-id-card', 'label' => 'R.G. (identidade)', 'desc' => 'Documento de identidade'],
                        ['id' => 'cpf', 'icon' => 'fas fa-hashtag', 'label' => 'CPF', 'desc' => 'Cadastro de Pessoa Física'],
                        ['id' => 'cnh', 'icon' => 'fas fa-car', 'label' => 'CNH (carteira nacional de habilitação)', 'desc' => 'Se houver'],
                        ['id' => 'titulo', 'icon' => 'fas fa-vote-yea', 'label' => 'Título Eleitoral', 'desc' => 'Documento eleitoral'],
                        ['id' => 'endereco', 'icon' => 'fas fa-home', 'label' => 'Comprovante de endereço (com CEP)', 'desc' => 'Conta ou documento oficial'],
                        ['id' => 'carteira_trabalho', 'icon' => 'fas fa-briefcase', 'label' => 'Carteira de trabalho, frente e verso', 'desc' => 'Física ou digital']
                    ];
                    @endphp

                    @foreach($documentos as $index => $doc)
                        @if($index % 2 == 1)
                            <div class="col-lg-6 mb-4">
                        @endif
                        @if($index % 2 == 0)
                            <div class="col-lg-6 mb-4">
                        @endif
                            <div class="document-card">
                                <div class="document-header">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="doc_{{ $doc['id'] }}" name="documentos[]" value="{{ $doc['label'] }}">
                                        <label class="custom-control-label document-label" for="doc_{{ $doc['id'] }}">
                                            <i class="{{ $doc['icon'] }} mr-2 text-primary"></i>
                                            {{ $doc['label'] }}
                                        </label>
                                    </div>
                                    @if(isset($doc['desc']))
                                        <small class="text-muted d-block mt-1">{{ $doc['desc'] }}</small>
                                    @endif
                                </div>
                                <div class="document-actions">
                                    <button type="button" class="btn btn-outline-primary btn-sm anexar-btn" data-target="arquivo_{{ $doc['id'] }}">
                                        <i class="fas fa-paperclip mr-1"></i> Anexar
                                    </button>
                                    <input type="file" id="arquivo_{{ $doc['id'] }}" name="arquivo_{{ $doc['id'] }}" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                    <small class="file-selected d-none text-success mt-2"></small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Seção de Documentos dos Filhos -->
                <div class="modern-card mt-4">
                    <div class="card-header-modern bg-warning">
                        <h5 class="card-title-modern mb-0">
                            <i class="fas fa-child mr-2"></i>
                            Documentos dos Filhos (se menores de 14 anos)
                        </h5>
                    </div>
                    <div class="card-body-modern">
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Anexe os documentos abaixo apenas se o funcionário possuir filhos menores de 14 anos
                        </p>

                        @php
                        $documentosFilhos = [
                            ['id' => 'certidao_nascimento', 'icon' => 'fas fa-certificate', 'label' => 'Certidão de nascimento', 'desc' => 'Do(s) filho(s)'],
                            ['id' => 'cpf_filho', 'icon' => 'fas fa-hashtag', 'label' => 'CPF filho', 'desc' => 'Se já possuir'],
                            ['id' => 'vacinacao', 'icon' => 'fas fa-syringe', 'label' => 'Carteira de vacinação (menor 07 anos)', 'desc' => 'Atualizada'],
                            ['id' => 'frequencia', 'icon' => 'fas fa-school', 'label' => 'Comprovante de frequência escolar (maior 07 anos)', 'desc' => 'Declaração da escola']
                        ];
                        @endphp

                        <div class="row">
                            @foreach($documentosFilhos as $index => $doc)
                                <div class="col-lg-6 mb-4">
                                    <div class="document-card document-card-child">
                                        <div class="document-header">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="doc_{{ $doc['id'] }}" name="documentos[]" value="{{ $doc['label'] }}">
                                                <label class="custom-control-label document-label" for="doc_{{ $doc['id'] }}">
                                                    <i class="{{ $doc['icon'] }} mr-2 text-warning"></i>
                                                    {{ $doc['label'] }}
                                                </label>
                                            </div>
                                            @if(isset($doc['desc']))
                                                <small class="text-muted d-block mt-1">{{ $doc['desc'] }}</small>
                                            @endif
                                        </div>
                                        <div class="document-actions">
                                            <button type="button" class="btn btn-outline-warning btn-sm anexar-btn" data-target="arquivo_{{ $doc['id'] }}">
                                                <i class="fas fa-paperclip mr-1"></i> Anexar
                                            </button>
                                            <input type="file" id="arquivo_{{ $doc['id'] }}" name="arquivo_{{ $doc['id'] }}" class="d-none" accept=".pdf,.jpg,.jpeg,.png">
                                            <small class="file-selected d-none text-success mt-2"></small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="text-center mt-5 pt-4 border-top">
                    <button type="submit" class="btn btn-success btn-lg px-5" id="btn-salvar">
                        <i class="fas fa-save mr-2"></i>
                        Salvar Documentos
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<link rel="stylesheet" href="{{ asset('css/modern-design.css') }}">
<style>
/* Inputs modernos */
.modern-input {
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    padding: 12px 16px;
    font-size: 14px;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.modern-input:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    background: #fff;
    outline: none;
}

/* Cards de documentos */
.document-card {
    background: #fff;
    border-radius: 12px;
    border: 2px solid #f1f5f9;
    padding: 20px;
    transition: all 0.3s ease;
    height: 100%;
    position: relative;
    animation: fadeInUp 0.5s ease-out;
}

.document-card-child {
    border-color: #fbbf24;
}

.document-header {
    margin-bottom: 16px;
}

.document-label {
    font-weight: 600;
    font-size: 15px;
    color: #1e293b;
    cursor: pointer;
    user-select: none;
}

.document-actions {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
}

.file-selected {
    font-weight: 600;
    font-size: 13px;
}

/* Remover estilos de checkbox padrão do Bootstrap */
.custom-control-label::before,
.custom-control-label::after {
    position: absolute;
    top: 0.25rem;
    left: -1.5rem;
    display: block;
    width: 1rem;
    height: 1rem;
    content: "";
}

.custom-control-label::before {
    background-color: #fff;
    border: 2px solid #3b82f6;
    border-radius: 0.25rem;
}

.custom-control-input:checked ~ .custom-control-label::before {
    background-color: #3b82f6;
    border-color: #3b82f6;
}

.custom-control-input:checked ~ .custom-control-label::after {
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8' viewBox='0 0 8 8'%3e%3cpath fill='%23fff' d='m6.564.75-3.59 3.612-1.538-1.55L0 4.26l2.974 2.99L8 2.193z'/%3e%3c/svg%3e");
}

/* Botões modernos */
.btn-lg {
    padding: 14px 28px;
    font-size: 16px;
    font-weight: 600;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
}

.btn-outline-secondary {
    border: 2px solid #64748b;
    color: #64748b;
    background: transparent;
}

/* Alertas modernos já incluídos no modern-design.css */

/* Header com background amarelo para filhos */
.bg-warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
    color: white !important;
}

.bg-warning .card-title-modern {
    color: white !important;
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

.document-card:nth-child(1) { animation-delay: 0.1s; }
.document-card:nth-child(2) { animation-delay: 0.2s; }
.document-card:nth-child(3) { animation-delay: 0.3s; }
.document-card:nth-child(4) { animation-delay: 0.4s; }
.document-card:nth-child(5) { animation-delay: 0.5s; }
.document-card:nth-child(6) { animation-delay: 0.6s; }

/* Loading state para o botão */
.btn-loading {
    position: relative;
    color: transparent;
}

.btn-loading::after {
    content: "";
    position: absolute;
    width: 16px;
    height: 16px;
    top: 50%;
    left: 50%;
    margin-left: -8px;
    margin-top: -8px;
    border: 2px solid #ffffff;
    border-radius: 50%;
    border-top-color: transparent;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Remover overlays de acessibilidade forçadamente */
[role="tooltip"],
.tooltip,
.accessibility-overlay,
.a11y-tooltip,
.popover,
.bs-tooltip-top,
.bs-tooltip-bottom,
.bs-tooltip-left,
.bs-tooltip-right,
.label.custom-control-label.document-label:after,
.label.custom-control-label.document-label:before {
    display: none !important;
    visibility: hidden !important;
    opacity: 0 !important;
    pointer-events: none !important;
    position: absolute !important;
    top: -9999px !important;
    left: -9999px !important;
}

/* Forçar remoção de pseudo-elementos problemáticos */
.document-label::before,
.document-label::after {
    display: none !important;
    content: none !important;
    visibility: hidden !important;
}

/* Desabilitar data attributes que causam tooltips */
*[data-toggle],
*[data-placement],
*[title]:not([title=""]) {
    pointer-events: auto !important;
}

/* Responsividade */
@media (max-width: 768px) {
    .document-card {
        padding: 16px;
        margin-bottom: 16px;
    }
    
    .btn-lg {
        padding: 12px 20px;
        font-size: 14px;
        width: 100%;
        margin-bottom: 10px;
    }
    
    .card-body-modern {
        padding: 20px;
    }
}
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Garantir que o CSRF token esteja configurado corretamente
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Função para anexar arquivos
    $('.anexar-btn').click(function() {
        const targetId = $(this).data('target');
        $('#' + targetId).click();
    });

    // Mostrar nome do arquivo selecionado com animação
    $('input[type="file"]').change(function() {
        const file = this.files[0];
        const $button = $(this).siblings('.anexar-btn');
        const $small = $(this).siblings('.file-selected');
        const $card = $(this).closest('.document-card');
        
        if (file) {
            // Animação de sucesso
            $card.addClass('border-success');
            $button.removeClass('btn-outline-primary btn-outline-warning')
                   .addClass('btn-success')
                   .html('<i class="fas fa-check mr-1"></i> Anexado');
            
            $small.text(`📎 ${file.name}`)
                  .removeClass('d-none')
                  .addClass('text-success')
                  .hide()
                  .fadeIn(300);
                  
            // Marcar checkbox automaticamente
            $card.find('input[type="checkbox"]').prop('checked', true);
            
        } else {
            // Resetar estado
            $card.removeClass('border-success');
            $button.removeClass('btn-success')
                   .addClass($card.hasClass('document-card-child') ? 'btn-outline-warning' : 'btn-outline-primary')
                   .html('<i class="fas fa-paperclip mr-1"></i> Anexar');
            $small.addClass('d-none');
            
            // Desmarcar checkbox
            $card.find('input[type="checkbox"]').prop('checked', false);
        }
    });

    // Loading state no formulário
    $('#form-documentos').submit(function(e) {
        const $btnSalvar = $('#btn-salvar');
        
        // Verificar se pelo menos um arquivo foi anexado
        const arquivosAnexados = $('input[type="file"]').filter(function() {
            return this.files.length > 0;
        }).length;
        
        if (arquivosAnexados === 0) {
            e.preventDefault();
            alert('Por favor, anexe pelo menos um documento antes de enviar.');
            return false;
        }
        
        // Loading state
        $btnSalvar.prop('disabled', true)
                  .html('<i class="fas fa-spinner fa-spin mr-2"></i>Salvando...');
    });

    // Validação em tempo real dos campos obrigatórios
    // Máscara para CPF
    $('#cpf').on('input', function() {
        let cpf = $(this).val().replace(/\D/g, ''); // Remove tudo que não é dígito
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após o terceiro dígito
        cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2'); // Adiciona ponto após o sexto dígito
        cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2'); // Adiciona hífen antes dos dois últimos dígitos
        $(this).val(cpf);
    });

    $('#nome_funcionario, #funcao, #cpf, #sexo').on('input change', function() {
        const $input = $(this);
        const value = $input.val().trim();
        
        if (value.length >= 2) {
            $input.removeClass('border-danger').addClass('border-success');
        } else {
            $input.removeClass('border-success').addClass('border-danger');
        }
    });

    // Animação de entrada dos cards
    $('.document-card').each(function(index) {
        $(this).delay(index * 100).queue(function() {
            $(this).addClass('animate__animated animate__fadeInUp').dequeue();
        });
    });

    // Contador de documentos selecionados
    function atualizarContador() {
        const totalSelecionados = $('input[type="checkbox"]:checked').length;
        const totalArquivos = $('input[type="file"]').filter(function() {
            return this.files.length > 0;
        }).length;
        
        // Atualizar título do card principal
        const titulo = totalArquivos > 0 ? 
            `Cadastro de Documentos Necessários (${totalArquivos} anexado${totalArquivos > 1 ? 's' : ''})` :
            'Cadastro de Documentos Necessários';
            
        $('.card-title-modern').first().html(`
            <i class="fas fa-plus-circle mr-2 text-primary"></i>
            ${titulo}
        `);
    }

    // Monitorar mudanças nos checkboxes e arquivos
    $('input[type="checkbox"], input[type="file"]').change(atualizarContador);

    // Validação de tamanho de arquivo
    $('input[type="file"]').change(function() {
        const file = this.files[0];
        if (file && file.size > 15 * 1024 * 1024) { // 15MB
            $(this).val(''); // Limpar
            Swal.fire({
                icon: 'error',
                title: 'Arquivo muito grande',
                text: 'O arquivo deve ter no máximo 15MB.',
                confirmButtonColor: '#ef4444'
            });
        }
    });
});
</script>

<!-- SweetAlert2 para alertas modernos -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@stop
