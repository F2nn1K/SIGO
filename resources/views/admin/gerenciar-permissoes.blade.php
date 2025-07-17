@extends('adminlte::page')

@section('title', 'Gerenciar Usuários')

@section('plugins.Sweetalert2', true)

@section('content_header')
<div class="header-highlight"></div>
<h1 class="m-0 text-dark">Gerenciar Usuários</h1>
@stop

@section('content')
    <div class="container-fluid">
        <livewire:gerenciar-permissoes />
    </div>
@stop

@push('css')
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
    
    /* Estilos modernos para tabelas */
    .table {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    }
    
    .table th {
        background-color: #f8fafc;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 1rem;
        border-bottom: 2px solid #eaedf2;
        color: #5a6473;
    }
    
    .table td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid #f0f2f7;
        color: #4a5568;
    }
    
    .table tbody tr {
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.04);
        transform: translateY(-1px);
    }
    
    .table tbody tr.selected {
        background-color: rgba(59, 130, 246, 0.08);
        box-shadow: 0 0 0 1px rgba(59, 130, 246, 0.2);
    }
    
    .table tbody tr:last-child td {
        border-bottom: none;
    }
    
    /* Melhorias em badges e botões */
    .badge {
        font-size: 0.7rem;
        font-weight: 500;
        padding: 0.35em 0.9em;
        border-radius: 30px;
        letter-spacing: 0.3px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    }
    
    .badge-success {
        background-color: #10b981;
        border: 1px solid #059669;
    }
    
    .badge-danger {
        background-color: #ef4444;
        border: 1px solid #dc2626;
    }
    
    .btn-group {
        display: flex;
        gap: 5px;
    }
    
    .btn-group .btn {
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
    }
    
    .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    
    /* Perfil e permissões */
    .perfil-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.75rem;
        border-radius: 6px;
        background-color: #ebf5ff;
        color: #3b82f6;
        font-size: 0.8rem;
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);
        border: 1px solid rgba(59, 130, 246, 0.2);
    }
    
    .permissoes-container {
        max-height: 500px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.02);
        background-color: #fafafa;
    }
    
    .permissao-item {
        margin-bottom: 12px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f2f7;
    }
    
    .permissao-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    /* Avatar do usuário */
    .user-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, #4f46e5, #3b82f6);
        color: white;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 2.5rem;
        margin: 0 auto;
        box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
        border: 4px solid #fff;
        position: relative;
        z-index: 10;
    }
    
    /* Cards modernizados */
    .perfil-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
        transition: all 0.3s ease;
        height: calc(100% - 1.5rem);
    }
    
    .perfil-card:hover {
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    }
    
    .perfil-card .card-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #f0f2f7;
        padding: 1.25rem;
    }
    
    /* Sombra azul para cabeçalhos de seção */
    .section-header {
        position: relative;
        background: #ffffff;
        border-bottom: 1px solid rgba(59, 130, 246, 0.15);
        box-shadow: 0 4px 15px -5px rgba(59, 130, 246, 0.25);
        z-index: 10;
    }
    
    .section-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        border-radius: 3px 3px 0 0;
    }
    
    .section-header h5 {
        font-weight: 600;
        color: #334155;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.8);
    }
    
    .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
    
    /* Estilo para texto info */
    .info-text {
        font-size: 0.85rem;
        background-color: #f8fafc;
        border-left: 3px solid #3b82f6;
        padding: 10px 15px;
        border-radius: 0 8px 8px 0;
        color: #4b5563;
    }
    
    /* Animações de carregamento */
    .loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(255, 255, 255, 0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
        border-radius: 12px;
        backdrop-filter: blur(2px);
    }
    
    /* Animações para componentes */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card, .table {
        animation: fadeIn 0.3s ease-out;
    }
    
    /* Form controls */
    .form-control {
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        padding: 0.65rem 1rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        transition: all 0.2s;
    }
    
    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }
    
    /* Modais modernizados */
    .modal-content {
        border: none;
        border-radius: 12px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }
    
    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #f0f2f7;
    }
    
    .modal-header.bg-primary {
        background: linear-gradient(135deg, #4f46e5, #3b82f6) !important;
    }
    
    .modal-body {
        padding: 1.5rem;
    }
    
    .modal-footer {
        padding: 1.25rem 1.5rem;
        border-top: 1px solid #f0f2f7;
    }
</style>
@endpush

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar Livewire com segurança
        function inicializarLivewire() {
            // Se Livewire não estiver disponível, tentar novamente após 500ms
            if (typeof Livewire === 'undefined') {
                // Livewire não disponível, tentando novamente
                setTimeout(inicializarLivewire, 500);
                return;
            }
            
            // Livewire detectado, configurando eventos
            
            try {
                Livewire.on('mensagem', function(dados) {
                    const tipo = dados.tipo || 'success';
                    
                    // Mostrar mensagem usando toastr
                    if (typeof toastr !== 'undefined') {
                        toastr[tipo](dados.mensagem);
                    } else {
                        alert(dados.mensagem);
                    }
                    
                    // Mensagem
                    
                });
                
                Livewire.on('componenteAtualizado', function() {
                    // Componente atualizado
                });
                
                // Mais eventos podem ser configurados aqui...
                
            } catch (e) {
                // Erro ao configurar eventos Livewire
                setTimeout(inicializarLivewire, 1000);
            }
        }
        
        // Iniciar o processo
        inicializarLivewire();
    });
</script>
@endpush 