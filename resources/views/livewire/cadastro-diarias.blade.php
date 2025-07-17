<div>
    @section('plugins.Sweetalert2', true)
    
    <!-- Link para o CSS do Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Remover completamente o indicador de progresso global -->
            
            <div class="row">
                <div class="col-12">
                    <div class="card card-primary shadow-sm mb-3">
                        <div class="card-header bg-light section-header py-2">
                            <h5 class="card-title mb-0"><i class="fas fa-edit mr-2"></i>Informações da Diária</h5>
                        </div>
                        
                        <form wire:submit.prevent="salvar">
                            <div class="card-body">
                                <!-- Informações do Funcionário -->
                                <div class="mb-3">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="fas fa-user-tie mr-2"></i>Dados do Funcionário
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group position-relative required-field">
                                            <label>Nome do Funcionário</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-user"></i>
                                                    </span>
                                                </div>
                                                <input type="text" 
                                                       class="form-control" 
                                                       wire:model.live.debounce.200ms="pesquisaFuncionario" 
                                                       placeholder="Digite para pesquisar..." 
                                                       wire:click="$set('mostrarSugestoes', true)"
                                                       autocomplete="off">
                                                @if(!$nome)
                                                <div class="input-group-append">
                                                    <span class="input-group-text">
                                                        <span wire:loading wire:target="updatedPesquisaFuncionario">
                                                            <i class="fas fa-spinner fa-spin text-primary"></i>
                                                        </span>
                                                        <span wire:loading.remove wire:target="updatedPesquisaFuncionario">
                                                            @if($sugestoesFuncionarios->isEmpty() && strlen($pesquisaFuncionario) >= 2)
                                                                <i class="fas fa-search text-warning"></i>
                                                            @elseif($sugestoesFuncionarios->isNotEmpty())
                                                                <i class="fas fa-check text-success"></i>
                                                            @elseif(strlen($pesquisaFuncionario) > 0 && strlen($pesquisaFuncionario) < 2)
                                                                <i class="fas fa-keyboard text-warning" title="Digite pelo menos 2 caracteres"></i>
                                                            @else
                                                                <i class="fas fa-search text-primary"></i>
                                                            @endif
                                                        </span>
                                                    </span>
                                                </div>
                                                @endif
                                                @if($nome)
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-danger" wire:click="limparFuncionario" title="Limpar funcionário">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>
                                                @endif
                                            </div>
                                            
                                            @if($sugestoesFuncionarios->isNotEmpty() && $mostrarSugestoes)
                                            <div class="list-group position-absolute w-100" style="z-index: 1050; max-height: 250px; overflow-y: auto; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                                                @foreach($sugestoesFuncionarios as $funcionario)
                                                <button type="button" 
                                                        class="list-group-item list-group-item-action" 
                                                        wire:key="func-{{ $funcionario->id }}"
                                                        wire:click="selecionarFuncionario({{ $funcionario->id }})">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <strong>{{ $funcionario->nome }}</strong>
                                                        <span class="badge badge-primary">{{ $funcionario->departamento }}</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                                        <small class="text-muted">
                                                            <i class="fas fa-briefcase mr-1"></i>{{ $funcionario->funcao }}
                                                        </small>
                                                        <small class="text-success font-weight-bold">
                                                            <i class="fas fa-money-bill mr-1"></i>R$ {{ number_format($funcionario->valor, 2, ',', '.') }}
                                                        </small>
                                                    </div>
                                                </button>
                                                @endforeach
                                            </div>
                                            @elseif(strlen($pesquisaFuncionario) >= 2 && $mostrarSugestoes && $sugestoesFuncionarios->isEmpty())
                                            <div class="alert alert-warning mt-2">
                                                Nenhum funcionário encontrado com esse nome.
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group required-field">
                                            <label>Departamento</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-building"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" wire:model="departamento" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group required-field">
                                            <label>Função</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-briefcase"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" wire:model="funcao" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3 mt-4">
                                    <h6 class="text-primary border-bottom pb-2 mb-3">
                                        <i class="fas fa-calculator mr-2"></i>Dados da Diária
                                    </h6>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group required-field">
                                            <label>Valor Base</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">R$</span>
                                                </div>
                                                <input type="number" class="form-control" wire:model="valor" readonly>
                                            </div>
                                        </div>
                                        <div class="form-group required-field">
                                            <label>Quantidade</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-calculator"></i>
                                                    </span>
                                                </div>
                                                <input type="number" 
                                                       class="form-control" 
                                                       wire:model.live="horasExtras" 
                                                       min="1"
                                                       placeholder="Digite a quantidade"
                                                       pattern="[0-9]*"
                                                       inputmode="numeric"
                                                       onkeydown="return event.key === 'Backspace' || event.key === 'Delete' || event.key === 'ArrowLeft' || event.key === 'ArrowRight' || event.key === 'Tab' || (event.key >= '0' && event.key <= '9')"
                                                       required>
                                            </div>
                                            @error('horasExtras') <div class="text-danger">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Valor da Diária</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">R$</span>
                                                </div>
                                                <input type="number" 
                                                       class="form-control" 
                                                       wire:model="qtdDiaria" 
                                                       readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Empresa</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-building"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control" value="{{ $empresa == '2' ? 'Novo Tempo Asa Branca' : $empresa }}" readonly>
                                                <input type="hidden" wire:model="empresa">
                                            </div>
                                        </div>
                                        <div class="form-group required-field">
                                            <label>Referência</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-tag"></i>
                                                    </span>
                                                </div>
                                                <select class="form-control" wire:model="referencia" required>
                                                    <option value="">Selecione...</option>
                                                    @foreach($referencias as $ref)
                                                    <option value="{{ $ref }}">{{ $ref }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('referencia') <div class="text-danger">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="form-group">
                                            <label>Observação</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="fas fa-comment"></i>
                                                    </span>
                                                </div>
                                                <input type="text" 
                                                       class="form-control" 
                                                       wire:model="observacao"
                                                       placeholder="Observações adicionais">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted"><i class="fas fa-info-circle mr-1"></i> Preencha todos os campos obrigatórios</span>
                                    </div>
                                    <button type="submit" 
                                            class="btn btn-primary"
                                            @if(!$nome) disabled @endif>
                                        <span wire:loading.remove wire:target="salvar">
                                            <i class="fas fa-save mr-2"></i>
                                            Inserir
                                        </span>
                                        <span wire:loading wire:target="salvar">
                                            <i class="fas fa-spinner fa-spin mr-2"></i>
                                            Processando...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Lista de Diárias -->
                    @if(count($diariasSalvas) > 0)
                    <div class="card card-success shadow-sm mb-3">
                        <div class="card-header bg-light section-header py-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list mr-2"></i>
                                Diárias a Serem Salvas
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Departamento</th>
                                            <th>Empresa</th>
                                            <th>Quantidade</th>
                                            <th>Valor</th>
                                            <th>Gerente</th>
                                            <th>Referência</th>
                                            <th width="100">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($diariasSalvas as $diaria)
                                        <tr>
                                            <td>{{ $diaria['nome'] }}</td>
                                            <td><span class="badge badge-light">{{ $diaria['departamento'] }}</span></td>
                                            <td>{{ $diaria['empresa'] == '2' ? 'Novo Tempo Asa Branca' : ($diaria['empresa'] ?? '') }}</td>
                                            <td><span class="badge badge-info">{{ $diaria['horasExtras'] }}</span></td>
                                            <td><span class="badge badge-success">R$ {{ number_format($diaria['qtdDiaria'], 2, ',', '.') }}</span></td>
                                            <td><span class="badge badge-warning">{{ $diaria['gerente'] }}</span></td>
                                            <td><span class="badge badge-secondary">{{ $diaria['referencia'] }}</span></td>
                                            <td>
                                                <button class="btn btn-danger btn-sm" 
                                                        type="button"
                                                        title="Excluir diária #{{ $diaria['id'] }}"
                                                        data-diaria-id="{{ $diaria['id'] }}"
                                                        onclick="confirmarRemoverDiaria('{{ $diaria['id'] }}', '{{ $diaria['nome'] }}')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="bg-light">
                                            <td colspan="7" class="text-center">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle mr-1"></i> 
                                                    Os itens acima serão salvos ao clicar em "Salvar Diárias"
                                                </small>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-center">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="badge badge-light">
                                        <i class="fas fa-users mr-1"></i>
                                        Total de Funcionários: {{ count($diariasSalvas) }}
                                    </span>
                                </div>
                                <button class="btn btn-success" 
                                        type="button"
                                        onclick="confirmarSalvarDiarias()"
                                        id="btn-salvar-diarias">
                                    <i class="fas fa-check mr-2"></i>
                                    Salvar Diárias
                                </button>
                                <div>
                                    <span class="badge badge-light">
                                        <i class="fas fa-money-bill mr-1"></i>
                                        Total: R$ {{ number_format(collect($diariasSalvas)->sum('qtdDiaria'), 2, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @push('js')
    <script>
        // Configuração do componente
        document.addEventListener('livewire:initialized', () => {
            // Evitar a entrada da letra 'e' ou 'E' no campo de quantidade
            document.querySelectorAll('input[type="number"]').forEach(function(input) {
                input.addEventListener('keypress', function(e) {
                    // Bloquear 'e', 'E', '+', '-' e outros caracteres não numéricos
                    if (e.key === 'e' || e.key === 'E' || e.key === '+' || e.key === '-' || e.key === '.' || e.key === ',') {
                        e.preventDefault();
                    }
                });
                
                // Impedir também a colagem de valores com 'e' ou 'E'
                input.addEventListener('paste', function(e) {
                    // Obter texto da área de transferência
                    let pasteData = (e.clipboardData || window.clipboardData).getData('text');
                    // Verificar se contém caracteres inválidos
                    if (/[^\d]/.test(pasteData)) {
                        e.preventDefault();
                    }
                });
            });
        
            // Eventos específicos do componente
            Livewire.on('diaria-adicionada', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Sucesso',
                    text: 'Diária adicionada à lista.',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });

            Livewire.on('diaria-removida', (event) => {
                Swal.fire({
                    icon: 'success',
                    title: 'Removido!',
                    text: `A diária de ${event.nome || 'funcionário'} foi removida com sucesso.`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    showClass: {
                        popup: 'animate__animated animate__fadeIn'
                    }
                });
            });

            Livewire.on('diarias-salvas', () => {
                Swal.fire({
                    icon: 'success',
                    title: 'Diárias Salvas!',
                    text: 'Todas as diárias foram registradas com sucesso.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6',
                    showClass: {
                        popup: 'animate__animated animate__fadeIn'
                    }
                });
            });
            
            // Handler para erros ao salvar diárias
            Livewire.on('erro-salvar-diarias', (data) => {
                // Reativar o botão para permitir nova tentativa
                const btnSalvarDiarias = document.getElementById('btn-salvar-diarias');
                if (btnSalvarDiarias) {
                    btnSalvarDiarias.disabled = false;
                    btnSalvarDiarias.classList.remove('disabled');
                    btnSalvarDiarias.style.opacity = '';
                    btnSalvarDiarias.style.cursor = '';
                }
                
                // Mostrar mensagem de erro
                Swal.fire({
                    icon: 'error',
                    title: 'Erro ao Salvar',
                    text: data.mensagem || 'Ocorreu um erro ao salvar as diárias.',
                    confirmButtonText: 'Entendi',
                    confirmButtonColor: '#3085d6',
                    showClass: {
                        popup: 'animate__animated animate__fadeIn'
                    }
                });
            });
            
            // Impedir múltiplos cliques no botão Salvar Diárias
            const btnSalvarDiarias = document.getElementById('btn-salvar-diarias');
            if (btnSalvarDiarias) {
                btnSalvarDiarias.addEventListener('click', function() {
                    // Desabilitar o botão imediatamente após o clique
                    this.disabled = true;
                    
                    // Alterar visuais do botão para indicar estado desabilitado
                    this.classList.add('disabled');
                    this.style.opacity = '0.65';
                    this.style.cursor = 'not-allowed';
                    
                    // Tempo máximo de espera para reativar em caso de erro (20 segundos)
                    setTimeout(() => {
                        // Verificar se ainda existem diárias para salvar
                        if (document.querySelector('table tbody tr')) {
                            this.disabled = false;
                            this.classList.remove('disabled');
                            this.style.opacity = '';
                            this.style.cursor = '';
                        }
                    }, 20000);
                });
            }

            // Tratamento do evento de seleção de funcionário
            Livewire.on('funcionario-selecionado', (data) => {
                // Ocultar qualquer lista de sugestões que ainda possa estar visível
                document.querySelectorAll('.list-group.position-absolute').forEach(el => {
                    el.style.display = 'none';
                });
                
                // Forçar atualização visual dos campos relacionados ao funcionário
                setTimeout(() => {
                    const camposAtualizados = ['departamento', 'funcao', 'valor', 'empresa'];
                    camposAtualizados.forEach(campo => {
                        const input = document.querySelector(`input[wire\\:model="${campo}"]`);
                        if (input) {
                            // Adicionar classe para destacar brevemente o campo
                            input.classList.add('border-success');
                            setTimeout(() => input.classList.remove('border-success'), 1000);
                        }
                    });
                }, 100);
            });
            
            // Adicionar um tratamento para o cálculo de diária
            Livewire.on('diaria-calculada', (valor) => {
                // Destacar o campo do valor calculado
                const inputDiaria = document.querySelector('input[wire\\:model="qtdDiaria"]');
                if (inputDiaria) {
                    inputDiaria.classList.add('border-success');
                    setTimeout(() => inputDiaria.classList.remove('border-success'), 1000);
                }
            });
            
            // Destacar campos obrigatórios
            const camposObrigatorios = document.querySelectorAll('.required-field');
            camposObrigatorios.forEach(campo => {
                const label = campo.querySelector('label');
                if (label) {
                    label.innerHTML += ' <span class="text-danger">*</span>';
                }
            });

            // Adicionar um tratamento para validação dos campos na submissão
            document.querySelector('form[wire\\:submit\\.prevent="salvar"]').addEventListener('submit', function(e) {
                // Verificar referência
                const referenciaSelect = document.querySelector('select[wire\\:model="referencia"]');
                if (referenciaSelect && referenciaSelect.value === '') {
                    referenciaSelect.classList.add('is-invalid');
                    // Adicionar borda vermelha
                    referenciaSelect.style.borderColor = '#dc3545';
                    // Adicionar efeito de shake para chamar atenção
                    referenciaSelect.closest('.form-group').classList.add('animate__animated', 'animate__shakeX');
                    // Remover classes de animação após a conclusão
                    setTimeout(() => {
                        referenciaSelect.closest('.form-group').classList.remove('animate__animated', 'animate__shakeX');
                    }, 1000);
                    
                    // Notificar o usuário
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atenção',
                        text: 'Por favor, selecione uma referência para a diária.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 4000,
                        timerProgressBar: true
                    });
                } else if (referenciaSelect) {
                    referenciaSelect.classList.remove('is-invalid');
                    referenciaSelect.style.borderColor = '';
                }
            });
            
            // Adicionar evento para limpar a validação visual quando o select de referência for alterado
            const referenciaSelect = document.querySelector('select[wire\\:model="referencia"]');
            if (referenciaSelect) {
                referenciaSelect.addEventListener('change', function() {
                    if (this.value !== '') {
                        this.classList.remove('is-invalid');
                        this.style.borderColor = '';
                    }
                });
            }
        });

        // Confirmação antes de remover uma diária
        /* Comentando esta função, pois já está definida no arquivo resources/views/diarias/cadastro.blade.php
        window.confirmarRemoverDiaria = function(id, nome) {
            if (!id) {
                console.error('ID não fornecido para exclusão');
                
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: 'Não foi possível identificar o item para exclusão',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#3085d6'
                });
                
                return;
            }
            
            Swal.fire({
                title: 'Tem certeza?',
                text: `Deseja remover a diária de ${nome || 'este funcionário'}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sim, remover!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Exibir loading
                    Swal.fire({
                        title: 'Removendo...',
                        html: 'Processando sua solicitação',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    console.log('Enviando comando para remover diária ID:', id);
                    
                    try {
                        // Verificar se o Livewire está disponível
                        if (typeof Livewire === 'undefined') {
                            throw new Error('Livewire não está disponível');
                        }
                        
                        // Chamar o método do Livewire
                        Livewire.dispatch('remover-diaria', { id: id });
                    } catch (e) {
                        console.error('Erro ao enviar comando:', e);
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Erro',
                            text: 'Ocorreu um erro ao tentar remover o item. Tente novamente.',
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                }
            });
        }
        */
    </script>
    
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
            animation: fadeIn 0.3s ease-out;
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
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Estilo para destacar campos atualizados */
        .border-success {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        /* Estilos da tabela */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }
        
        .table thead th {
            background-color: #f8fafc;
            font-weight: 600;
            border-bottom: 2px solid #edf2f7;
        }
        
        .table tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.05);
        }
        
        .table tbody td {
            vertical-align: middle;
        }
        
        /* Botões animados */
        .btn {
            border-radius: 6px;
            transition: all 0.2s;
        }
        
        .btn:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        .btn-sm {
            border-radius: 4px;
        }
        
        /* Campos obrigatórios e efeitos de foco */
        .required-field label {
            font-weight: 600;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 0.2rem rgba(79, 70, 229, 0.25);
        }
        
        .input-group:hover .input-group-text {
            background-color: #f8f9ff;
            border-color: #d1d5fb;
            transition: all 0.3s ease;
        }
        
        .list-group-item-action:hover {
            background-color: #f8f9ff;
            border-left: 3px solid #4f46e5;
        }
    </style>
    @endpush
</div>