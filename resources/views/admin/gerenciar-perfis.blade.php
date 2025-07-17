@extends('adminlte::page')

@section('title', 'Gerenciar Usuários')

@section('plugins.Sweetalert2', true)

@section('content_header')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="header-highlight"></div>
<h1 class="m-0 text-dark">Gerenciar Usuários</h1>
@stop

@section('content')
<div class="container-fluid">
    <!-- Menu de navegação simples -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="{{ route('admin.usuarios') }}">
                <i class="fas fa-users mr-1"></i> Usuários
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="/perfis">
                <i class="fas fa-user-tag mr-1"></i> Perfis
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{ route('admin.permissoes') }}">
                <i class="fas fa-key mr-1"></i> Permissões
            </a>
        </li>
    </ul>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light section-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Usuários do Sistema</h5>
                        <div class="input-group search-container" style="width: 300px;">
                            <input type="text" class="form-control form-control-sm" 
                                    id="buscar-usuario"
                                    placeholder="Buscar usuários...">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Nome</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th class="text-center" style="width: 150px;">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->name }}</td>
                                <td>{{ $usuario->profile_name ?? 'Sem perfil' }}</td>
                                <td>
                                    @if($usuario->active)
                                        <span class="badge badge-success">Ativo</span>
                                    @else
                                        <span class="badge badge-danger">Inativo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-info" 
                                                onclick="editarUsuario({{ $usuario->id }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-{{ $usuario->active ? 'warning' : 'success' }}" 
                                                onclick="alterarStatus({{ $usuario->id }}, {{ $usuario->active ? 'false' : 'true' }})">
                                            <i class="fas fa-{{ $usuario->active ? 'ban' : 'check' }}"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar usuário -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-edit mr-2"></i> Editar Usuário
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form-editar-usuario" onsubmit="salvarUsuario(event)">
                <div class="modal-body">
                    <input type="hidden" id="usuario-id">
                    <div class="form-group">
                        <label for="usuario-nome" class="font-weight-bold">Nome</label>
                        <input type="text" class="form-control" id="usuario-nome" required>
                    </div>
                    <div class="form-group">
                        <label for="usuario-senha" class="font-weight-bold">Nova Senha</label>
                        <input type="password" class="form-control" id="usuario-senha" placeholder="Deixe em branco para manter a senha atual">
                        <small class="text-muted">Preencha apenas se desejar alterar a senha atual</small>
                    </div>
                    <div class="form-group">
                        <label for="usuario-confirmar-senha" class="font-weight-bold">Confirmar Senha</label>
                        <input type="password" class="form-control" id="usuario-confirmar-senha" placeholder="Confirme a nova senha">
                    </div>
                    <div class="form-group">
                        <label for="usuario-perfil" class="font-weight-bold">Perfil</label>
                        <select class="form-control" id="usuario-perfil">
                            <option value="">Sem perfil</option>
                            @foreach($perfis as $perfil)
                                <option value="{{ $perfil->id }}">{{ $perfil->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalConfirmacaoExclusao" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle mr-2"></i> Confirmar Exclusão
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir o perfil <strong id="perfil-excluir-nome"></strong>?</p>
                <p class="text-danger">
                    <i class="fas fa-exclamation-circle mr-1"></i> Esta ação não poderá ser desfeita e todos os usuários 
                    associados a este perfil ficarão sem perfil definido.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn-confirmar-exclusao">
                    <i class="fas fa-trash mr-1"></i> Excluir Definitivamente
                </button>
            </div>
        </div>
    </div>
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
    
    .section-header {
        background-color: #f8fafc;
        border-bottom: 1px solid #edf2f7;
        padding: 1rem;
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
    
    /* Estilos para a lista de perfis */
    .perfis-list {
        max-height: 500px;
        overflow-y: auto;
    }
    
    .perfis-list .nav-link {
        border-radius: 0;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-left: 4px solid transparent;
        color: #4b5563;
    }
    
    .perfis-list .nav-link.active {
        background-color: #3b82f6;
        color: white;
        border-left: 4px solid #2563eb;
    }
    
    .perfis-list .nav-link:not(.active):hover {
        background-color: #f8fafc;
        border-left: 4px solid #e2e8f0;
        transform: translateX(2px);
    }

    /* Tabela moderna */
    .table {
        margin-bottom: 0;
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
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: rgba(59, 130, 246, 0.04);
        transform: translateY(-1px);
    }
    
    /* Campo de busca personalizado */
    .search-container {
        position: relative;
    }
    
    .search-container input {
        border-radius: 20px;
        padding-left: 30px;
        border: 1px solid #d0d0d0;
        transition: all 0.3s;
    }
    
    .search-container input:focus {
        border-color: #2a93d5;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.15);
    }
    
    .search-container .input-group-text {
        background: transparent;
        border: none;
        color: #888;
        position: absolute;
        right: 0;
        z-index: 4;
    }
    
    /* Estilos para as abas */
    .nav-tabs .nav-link {
        border: none;
        border-bottom: 3px solid transparent;
        color: #64748b;
        font-weight: 500;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }
    
    .nav-tabs .nav-link:hover {
        color: #3b82f6;
        border-bottom-color: #e2e8f0;
    }
    
    .nav-tabs .nav-link.active {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        background-color: transparent;
    }
</style>
@endpush

@push('js')
<script>
    // Função básica para busca na tabela
    $(document).ready(function() {
        $("#buscar-usuario").on("keyup", function() {
            var value = $(this).val().toLowerCase();
            $("tbody tr").filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
        });
    });

    // Funções para editar e alterar status
    function editarUsuario(id) {
        try {
            // Mostrar loading
            Swal.fire({
                title: 'Carregando...',
                text: 'Obtendo dados do usuário',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch(`/api/usuarios/${id}`)
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    
                    if (data.success) {
                        const usuario = data.data;
                        document.getElementById('usuario-id').value = usuario.id;
                        document.getElementById('usuario-nome').value = usuario.name;
                        document.getElementById('usuario-senha').value = '';
                        document.getElementById('usuario-confirmar-senha').value = '';
                        document.getElementById('usuario-perfil').value = usuario.profile_id || '';
                        
                        $('#modalEditarUsuario').modal('show');
                    } else {
                        throw new Error(data.message || 'Erro ao obter dados do usuário');
                    }
                })
                .catch(error => {
                    console.error('Erro ao editar usuário:', error);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message || 'Erro ao obter dados do usuário. Tente novamente.'
                    });
                });
        } catch (error) {
            console.error('Erro ao editar usuário:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao processar a solicitação. Tente novamente.'
            });
        }
    }
    
    function alterarStatus(id, status) {
        // Confirmação
        Swal.fire({
            icon: 'question',
            title: status ? 'Ativar Usuário?' : 'Desativar Usuário?',
            text: status 
                ? 'Deseja reativar este usuário? Ele poderá acessar o sistema novamente.' 
                : 'Deseja desativar este usuário? Ele não poderá mais acessar o sistema.',
            showCancelButton: true,
            confirmButtonText: status ? 'Sim, Ativar' : 'Sim, Desativar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: status ? 'Ativando...' : 'Desativando...',
                text: 'Processando alteração',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            fetch('/toggle-user-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    user_id: id,
                    active: status
                })
            })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: status 
                                ? 'Usuário ativado com sucesso!' 
                                : 'Usuário desativado com sucesso!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Erro ao alterar status do usuário');
                    }
                })
                .catch(error => {
                    console.error('Erro ao alterar status:', error);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message || 'Erro ao alterar status do usuário. Tente novamente.'
                    });
                });
        });
    }

    function salvarUsuario(event) {
        event.preventDefault();
        
        try {
            const id = document.getElementById('usuario-id').value;
            const nome = document.getElementById('usuario-nome').value;
            const senha = document.getElementById('usuario-senha').value;
            const confirmarSenha = document.getElementById('usuario-confirmar-senha').value;
            const perfilId = document.getElementById('usuario-perfil').value;
            
            // Validar senha
            if (senha && senha !== confirmarSenha) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro!',
                    text: 'As senhas não coincidem. Por favor, verifique.'
                });
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: 'Salvando...',
                text: 'Atualizando dados do usuário',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Preparar dados para envio
            const dados = {
                name: nome,
                profile_id: perfilId
            };
            
            // Adicionar senha apenas se foi preenchida
            if (senha) {
                dados.password = senha;
                dados.password_confirmation = confirmarSenha;
            }
            
            fetch(`/api/usuarios/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(dados)
            })
                .then(response => response.json())
                .then(data => {
                    Swal.close();
                    
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sucesso!',
                            text: 'Usuário atualizado com sucesso!',
                            timer: 1500,
                            showConfirmButton: false
                        });
                        
                        $('#modalEditarUsuario').modal('hide');
                        window.location.reload();
                    } else {
                        throw new Error(data.message || 'Erro ao atualizar usuário');
                    }
                })
                .catch(error => {
                    console.error('Erro ao salvar usuário:', error);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Erro!',
                        text: error.message || 'Erro ao atualizar usuário. Tente novamente.'
                    });
                });
        } catch (error) {
            console.error('Erro ao salvar usuário:', error);
            
            Swal.fire({
                icon: 'error',
                title: 'Erro!',
                text: 'Erro ao processar a solicitação. Tente novamente.'
            });
        }
    }
</script>
@endpush 