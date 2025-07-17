@extends('adminlte::page')

@section('title', 'Cadastro de Diárias')

@section('content_header')
<div class="header-highlight"></div>
<h1 class="m-0 text-dark">Cadastro de Diárias</h1>
@stop

@section('content')
    <livewire:cadastro-diarias :departamentos="$departamentos" />
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
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

        /* Estilo para o Toast do SweetAlert2 */
        .swal2-toast {
            max-width: 350px;
            margin-top: 60px !important;
        }
        
        /* Personalização do SweetAlert2 */
        .swal2-popup {
            border-radius: 12px;
            font-family: inherit;
        }
        
        .swal2-title {
            font-weight: 600;
        }
        
        .swal2-confirm {
            background: linear-gradient(90deg, #3b82f6, #4f46e5) !important;
            box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3) !important;
        }
        
        .swal2-deny {
            background: #ef4444 !important;
        }
        
        .swal2-cancel {
            background: #64748b !important;
        }
    </style>
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Verificar se Livewire está disponível de forma segura
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Livewire === 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro de Carregamento',
                    text: 'Não foi possível carregar os componentes necessários. Por favor, recarregue a página.',
                    confirmButtonText: 'Recarregar',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.reload();
                    }
                });
                
                // Se o elemento existir, muda seu conteúdo
                const container = document.querySelector('[wire\\:id]');
                if (container) {
                    container.innerHTML = '<div class="alert alert-danger">Erro ao carregar o componente. Por favor, recarregue a página.</div>';
                }
            }
        });
        
        // Eventos do Livewire - usando try/catch para evitar erros
        try {
            document.addEventListener('livewire:init', () => {
                // Livewire inicializado
                
                // Diária adicionada com sucesso
                Livewire.on('diaria-adicionada', () => {
                    // Usando SweetAlert2 no estilo toast para a notificação
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });
                    
                    Toast.fire({
                        icon: 'success',
                        title: 'Diária adicionada à lista'
                    });
                });
                
                // Diária removida da lista
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
                
                // Todas as diárias salvas com sucesso
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
                
                // Erro ao salvar diárias
                Livewire.on('erro-salvar-diarias', (event) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao Salvar',
                        text: event.mensagem || 'Ocorreu um erro ao salvar as diárias. Tente novamente.',
                        confirmButtonText: 'Entendi',
                        confirmButtonColor: '#3085d6',
                        showClass: {
                            popup: 'animate__animated animate__fadeIn'
                        }
                    });
                });
                
                // Evento de erro ao remover diária
                Livewire.on('erro-remover-diaria', (event) => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro ao Remover',
                        text: event.mensagem || 'Ocorreu um erro ao remover a diária.',
                        confirmButtonText: 'Entendi',
                        confirmButtonColor: '#3085d6',
                        showClass: {
                            popup: 'animate__animated animate__fadeIn'
                        }
                    });
                });
                
                // Confirmação antes de remover uma diária
                window.confirmarRemoverDiaria = function(id, nome) {
                    if (!id && id !== 0 && id !== '0') {
                        // Remove os console logs mas mantém a verificação
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
                        cancelButtonText: 'Cancelar',
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
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
                            
                            try {
                                // Verificar se o Livewire está disponível
                                if (typeof Livewire === 'undefined') {
                                    throw new Error('Livewire não está disponível');
                                }
                                
                                // Tentar obter o componente diretamente e chamar o método nele
                                const componentId = document.querySelector('[wire\\:id]')?.getAttribute('wire:id');
                                if (componentId) {
                                    // Primeiro, tentar chamar o método diretamente no componente
                                    try {
                                        // Chamar o método diretamente no componente com o ID como parâmetro
                                        Livewire.find(componentId).call('removerDiaria', id);
                                        return; // Se bem-sucedido, não executar o fallback
                                    } catch (directError) {
                                        // Se falhar, continuamos para o fallback abaixo
                                    }
                                }
                                
                                // Fallback: usar o dispatch global
                                Livewire.dispatch('remover-diaria', { id: id });
                                
                            } catch (e) {
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
                };
                
                // Confirmação antes de salvar todas as diárias
                window.confirmarSalvarDiarias = function() {
                    Swal.fire({
                        title: 'Salvar todas as diárias?',
                        text: 'Isso irá salvar todas as diárias da lista no banco de dados.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sim, salvar tudo!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Exibir loading
                            Swal.fire({
                                title: 'Salvando...',
                                html: 'Por favor, aguarde enquanto salvamos as diárias.',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Chamar o método do Livewire
                            Livewire.dispatch('salvar-todas-diarias');
                        }
                    });
                };
                
                // Funcionário selecionado - adicionar notificação visual
                Livewire.on('funcionario-selecionado', (event) => {
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true
                    });
                    
                    Toast.fire({
                        icon: 'info',
                        title: `Funcionário selecionado: ${event.nome || 'Nome não informado'}`                    });
                });
            });
        } catch (e) {
            // Erro silencioso - evita quebrar a página se Livewire não estiver disponível
            console.error('Erro ao inicializar eventos Livewire:', e);
        }
    </script>
@endsection 
