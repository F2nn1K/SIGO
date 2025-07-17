<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['web', 'auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rota de gerentes
    Route::get('/gerentes', function () {
        $gerentes = DB::table('funcionarios')
            ->where('departamento', 'Gerência')
            ->select('nome as gerente')
            ->distinct()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $gerentes
        ]);
    });

    // Perfis
    Route::middleware(['verifica.permissao.api:Configurar Permissões'])->group(function () {
        Route::get('/perfis', [PermissoesController::class, 'listarPerfis']);
        Route::get('/perfis/{id}', [PermissoesController::class, 'obterPerfil']);
        Route::post('/perfis', [PermissoesController::class, 'criarPerfil']);
        Route::post('/perfis/{id}', [PermissoesController::class, 'atualizarPerfil']);
        Route::delete('/perfis/{id}', [PermissoesController::class, 'excluirPerfil']);

        // Permissões
        Route::get('/permissoes/listar', [PermissoesController::class, 'listar']);
        Route::get('/permissoes/{id}', [PermissoesController::class, 'obter']);
        Route::post('/permissoes', [PermissoesController::class, 'store']);
        Route::put('/permissoes/{id}', [PermissoesController::class, 'update']);
        Route::delete('/permissoes/{id}', [PermissoesController::class, 'destroy']);

        // Usuários (Controller principal)
        Route::get('/usuarios', [UsuariosController::class, 'listar']);
        Route::post('/usuarios/criar', [UsuariosController::class, 'criar']);
        Route::post('/usuarios/atualizar', [UsuariosController::class, 'atualizar']);
        Route::post('/usuarios/toggle-status', [UsuariosController::class, 'toggleStatus']);
        Route::post('/usuarios/atualizar-perfil', [UsuariosController::class, 'atualizarPerfil']);
        Route::put('/usuarios/{id}/perfil', function (Request $request, $id) {
            try {
                $perfilId = $request->input('perfil_id');
                
                if (!$perfilId) {
                    return response()->json([
                        'success' => false,
                        'mensagem' => 'ID do perfil não fornecido',
                        'error_code' => 'missing_profile_id'
                    ], 400);
                }
                
                // Verificar se o usuário existe
                $usuario = \App\Models\User::findOrFail($id);
                
                // Verificar se o perfil existe
                $perfil = \App\Models\Profile::findOrFail($perfilId);
                
                // Atualizar o perfil do usuário
                $usuario->profile_id = $perfilId;
                $usuario->save();
                
                return response()->json([
                    'success' => true,
                    'mensagem' => 'Perfil atualizado com sucesso'
                ]);
                
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $tipo = strpos($e->getMessage(), 'Profile') !== false ? 'Perfil' : 'Usuário';
                return response()->json([
                    'success' => false,
                    'mensagem' => $tipo . ' não encontrado',
                    'error_code' => strtolower($tipo) . '_not_found'
                ], 404);
            } catch (\Exception $e) {
                \Log::error('Erro ao atualizar perfil do usuário via API: ' . $e->getMessage(), [
                    'user_id' => $id,
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                return response()->json([
                    'success' => false,
                    'mensagem' => 'Erro ao atualizar perfil: ' . $e->getMessage(),
                    'error_code' => 'update_error'
                ], 500);
            }
        });
        
        // Rota para ativar/desativar usuário
        Route::put('/usuarios/{id}/status', function (Request $request, $id) {
            try {
                // Encontrar o usuário
                $usuario = \App\Models\User::findOrFail($id);
                
                // Validar os dados recebidos
                $validatedData = $request->validate([
                    'active' => 'required|boolean',
                ]);
                
                // Atualizar o status do usuário
                $usuario->active = $validatedData['active'];
                $usuario->save();
                
                // Retornar resposta de sucesso
                return response()->json([
                    'success' => true,
                    'message' => $validatedData['active'] ? 'Usuário ativado com sucesso' : 'Usuário desativado com sucesso'
                ]);
                
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'error_code' => 'user_not_found'
                ], 404);
            } catch (\Illuminate\Validation\ValidationException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $e->errors(),
                    'error_code' => 'validation_error'
                ], 422);
            } catch (\Exception $e) {
                \Log::error('Erro ao alterar status do usuário via API:', [
                    'id' => $id,
                    'erro' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao alterar status do usuário: ' . $e->getMessage(),
                    'error_code' => 'update_error'
                ], 500);
            }
        });
        
        // Usuários (API Controller)
        Route::post('/users/criar', [UserController::class, 'store']);
        Route::delete('/users/{id}', [UserController::class, 'destroy']);
    });
    
    // Rota de diagnóstico
    Route::get('/diagnostico', function() {
        return response()->json([
            'success' => true,
            'message' => 'API funcionando corretamente',
            'timestamp' => now(),
            'ambiente' => app()->environment(),
            'versao_laravel' => app()->version()
        ]);
    });
});

// Rota de teste para depuração de usuários
Route::get('/usuarios/debug/{id}', function ($id) {
    try {
        // Buscar usuário diretamente do banco de dados
        $usuario = DB::table('users')
            ->select('id', 'name', 'login', 'empresa', 'active', 'profile_id')
            ->where('id', $id)
            ->first();
        
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado no banco de dados'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'method' => 'debug',
            'data' => $usuario
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao buscar usuário: ' . $e->getMessage()
        ], 500);
    }
});

// Route::get('relatorio/recursos-humanos', 'App\Http\Controllers\Api\RelatorioController@recursosHumanos');

// Rotas de acesso geral (apenas autenticadas)
Route::get('/usuarios/{id}', [UsuariosController::class, 'obter']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}', [UserController::class, 'update']);

// Rota específica para atualizar usuários pela nova API
Route::put('/usuarios/{id}', function (Request $request, $id) {
    try {
        // Encontrar o usuário
        $usuario = \App\Models\User::findOrFail($id);
        
        // Apenas o próprio usuário ou usuários com permissão podem atualizar
        if (Auth::id() != $id && !Auth::user()->temPermissao('Configurar Permissões')) {
            return response()->json([
                'success' => false,
                'message' => 'Você não tem permissão para atualizar este usuário',
                'error_code' => 'permission_denied'
            ], 403);
        }
        
        // Validar os dados recebidos
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'empresa' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);
        
        // Atualizar os dados do usuário
        $usuario->name = $validatedData['name'];
        $usuario->empresa = $validatedData['empresa'];
        
        // Atualizar senha apenas se foi fornecida
        if (isset($validatedData['password'])) {
            $usuario->password = \Illuminate\Support\Facades\Hash::make($validatedData['password']);
        }
        
        // Salvar alterações
        $usuario->save();
        
        // Retornar resposta de sucesso
        return response()->json([
            'success' => true,
            'message' => 'Usuário atualizado com sucesso'
        ]);
        
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Usuário não encontrado',
            'error_code' => 'user_not_found'
        ], 404);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Dados inválidos',
            'errors' => $e->errors(),
            'error_code' => 'validation_error'
        ], 422);
    } catch (\Exception $e) {
        \Log::error('Erro ao atualizar usuário via API:', [
            'id' => $id,
            'erro' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Erro ao atualizar usuário: ' . $e->getMessage(),
            'error_code' => 'update_error'
        ], 500);
    }
});

// API para permissões
Route::middleware('auth')->group(function() {
    // Listar permissões
    Route::get('/permissoes', function() {
        $permissoes = DB::table('permissions')->get();
        return response()->json([
            'success' => true,
            'data' => $permissoes
        ]);
    });
    
    // Obter uma permissão específica
    Route::get('/permissoes/{id}', function($id) {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $permissao
        ]);
    });
    
    // Criar uma nova permissão
    Route::post('/permissoes', function(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'code' => 'nullable|string|max:255|unique:permissions,code',
            'description' => 'nullable|string'
        ]);
        
        $id = DB::table('permissions')->insertGetId([
            'name' => $request->name,
            'code' => $request->code ?? strtolower(str_replace(' ', '_', $request->name)),
            'description' => $request->description,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso',
            'data' => $permissao
        ], 201);
    });
    
    // Atualizar uma permissão existente
    Route::put('/permissoes/{id}', function(Request $request, $id) {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'code' => 'nullable|string|max:255|unique:permissions,code,' . $id,
            'description' => 'nullable|string'
        ]);
        
        DB::table('permissions')->where('id', $id)->update([
            'name' => $request->name,
            'code' => $request->code ?? $permissao->code,
            'description' => $request->description,
            'updated_at' => now()
        ]);
        
        $permissaoAtualizada = DB::table('permissions')->where('id', $id)->first();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso',
            'data' => $permissaoAtualizada
        ]);
    });
    
    // Excluir uma permissão
    Route::delete('/permissoes/{id}', function($id) {
        $permissao = DB::table('permissions')->where('id', $id)->first();
        
        if (!$permissao) {
            return response()->json([
                'success' => false,
                'message' => 'Permissão não encontrada'
            ], 404);
        }
        
        // Remover relacionamentos primeiro
        DB::table('profile_permissions')->where('permission_id', $id)->delete();
        
        // Remover permissão
        DB::table('permissions')->where('id', $id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso'
        ]);
    });
});
