<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\CadastroDiarias;
use App\Http\Controllers\DiariasController;
use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\UsuariosController;
use App\Http\Controllers\RHController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Livewire\GerenciarPermissoes;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use App\Http\Controllers\CronogramaController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/diarias/mecejana', function () {
    return view('diarias.mecejana');
});

Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/home', function() {
        return view('admin.dashboard-livewire');
    })->name('home');
    
    Route::get('/dashboard', function () {
        return view('admin.dashboard-livewire');
    })->middleware(['auth'])->name('dashboard');

    // Rotas de Diárias - protegidas pela permissão 'Ver Diárias'
    Route::middleware(['can:Ver Diárias'])->group(function () {
        Route::get('/diarias', [App\Http\Controllers\DiariasController::class, 'index'])->name('diarias.index');
        Route::get('/diarias/cadastro', [App\Http\Controllers\DiariasController::class, 'cadastro'])->name('diarias.cadastro');
        Route::post('/diarias', [App\Http\Controllers\DiariasController::class, 'store'])->name('diarias.store');
    });

    // Rotas de Relatórios - protegidas por suas respectivas permissões
    Route::get('/relatorios', function() {
        return view('relatorios.index');
    })->middleware('auth')->name('relatorios.index');

    // Rotas de Permissões
    Route::middleware(['auth'])->group(function () {
        // Rota para a página de permissões - usando o controller
        Route::get('/permissoes', [App\Http\Controllers\PermissoesController::class, 'index'])->name('admin.permissoes');

        // API para gerenciar permissões
        Route::prefix('api')->group(function () {
            Route::get('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'show']);
            Route::post('/permissoes', [App\Http\Controllers\PermissoesController::class, 'store']);
            Route::put('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'update']);
            Route::delete('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'destroy']);
        });

        // Rota para a página de usuários (listagem)
        Route::get('/gerenciar-perfis', function () {
            // Buscar usuários diretamente do banco de dados com join
            $usuarios = DB::table('users')
                ->select('users.*', 'profiles.name as profile_name')
                ->leftJoin('profiles', 'users.profile_id', '=', 'profiles.id')
                ->get();
            $perfis = DB::table('profiles')->get();
            return view('admin.gerenciar-perfis', compact('usuarios', 'perfis'));
        })->name('admin.usuarios');

        // TESTE DE CONEXÃO FORÇADA
        Route::get('/perfis', function () {
            // Testar conexão forçada para o banco correto
            try {
                $pdo = new PDO(
                    'mysql:host=127.0.0.1;dbname=laravel_beta2',
                    'root',
                    '',
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $stmt = $pdo->query('SELECT COUNT(*) as count FROM permissions');
                $permissionsCount = $stmt->fetch()['count'];
                
                $stmt = $pdo->query('SELECT COUNT(*) as count FROM profiles');
                $profilesCount = $stmt->fetch()['count'];
                
                $stmt = $pdo->query('SELECT * FROM permissions LIMIT 5');
                $permissions = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                dd([
                    'CONEXAO_DIRETA_PDO' => 'SUCESSO',
                    'BANCO_CONECTADO' => 'laravel_beta2',
                    'PERMISSIONS_COUNT_PDO' => $permissionsCount,
                    'PROFILES_COUNT_PDO' => $profilesCount,
                    'PERMISSIONS_DATA_PDO' => $permissions,
                    'VS_LARAVEL_DB_NAME' => DB::connection()->getDatabaseName(),
                    'VS_LARAVEL_PERMISSIONS_COUNT' => DB::table('permissions')->count(),
                    'ENV_DB_DATABASE' => env('DB_DATABASE', 'NAO_DEFINIDO'),
                ]);
                
            } catch (Exception $e) {
                dd([
                    'ERRO_CONEXAO_PDO' => $e->getMessage(),
                    'LARAVEL_DB_NAME' => DB::connection()->getDatabaseName(),
                    'ENV_DB_DATABASE' => env('DB_DATABASE', 'NAO_DEFINIDO'),
                ]);
            }
        });
        
        // Rota para exibir um perfil específico - acesso direto ao banco
        Route::get('/perfis/{id}', function ($id) {
            // Buscar perfis, perfil selecionado e permissões diretamente do banco de dados
            $perfis = DB::table('profiles')->get();
            $perfilSelecionado = DB::table('profiles')->where('id', $id)->first();
            
            if (!$perfilSelecionado) {
                return redirect('/perfis')->with('error', 'Perfil não encontrado');
            }
            
            $permissoes = DB::table('permissions')->get();
            $permissoesSelecionadas = DB::table('profile_permissions')
                ->where('profile_id', $id)
                ->pluck('permission_id')
                ->toArray();
                
            return view('admin.perfis', compact('perfis', 'perfilSelecionado', 'permissoes', 'permissoesSelecionadas'));
        });
        
        // Rota para criar um novo perfil - acesso direto ao banco
        Route::post('/perfis', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name',
                'description' => 'nullable|string',
            ]);
            
            // Inserir diretamente no banco de dados
            DB::table('profiles')->insert([
                'name' => $request->name,
                'description' => $request->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect('/perfis')->with('success', 'Perfil criado com sucesso');
        });
        
        // Rota para atualizar um perfil - acesso direto ao banco
        Route::put('/perfis/{id}', function (Request $request, $id) {
            $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name,' . $id,
                'description' => 'nullable|string',
            ]);
            
            // Atualizar diretamente no banco de dados
            DB::table('profiles')->where('id', $id)->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => now(),
            ]);
            
            // Sincronizar permissões diretamente no banco de dados
            if ($request->has('permissions')) {
                // Remover permissões existentes
                DB::table('profile_permissions')->where('profile_id', $id)->delete();
                
                // Adicionar novas permissões
                foreach ($request->permissions as $permissionId) {
                    DB::table('profile_permissions')->insert([
                        'profile_id' => $id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // Se não enviou permissões, remover todas
                DB::table('profile_permissions')->where('profile_id', $id)->delete();
            }
            
            return redirect('/perfis/' . $id)->with('success', 'Perfil atualizado com sucesso');
        });
        
        // Rota para excluir um perfil - acesso direto ao banco
        Route::delete('/perfis/{id}', function ($id) {
            // Remover relacionamentos primeiro diretamente do banco de dados
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            
            // Remover perfil diretamente do banco de dados
            DB::table('profiles')->where('id', $id)->delete();
            
            return redirect('/perfis')->with('success', 'Perfil excluído com sucesso');
        });
        
        // API para gerenciar permissões
        Route::prefix('api')->group(function () {
            // Criar permissão
            Route::post('/permissoes', function (Request $request) {
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
            
            // Atualizar permissão
            Route::put('/permissoes/{id}', function (Request $request, $id) {
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
            
            // Excluir permissão
            Route::delete('/permissoes/{id}', function ($id) {
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
            
            // Obter permissão específica
            Route::get('/permissoes/{id}', function ($id) {
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
        });
    });

    // Rotas de Diárias (apenas usuários com permissão)
    Route::middleware(['can:Ver Diárias'])->group(function () {
        Route::get('/diarias/relatorio', [DiariasController::class, 'relatorio'])->name('diarias.relatorio');
        Route::get('/diarias/relatorio-gerente', [DiariasController::class, 'relatorioGerente'])->name('diarias.relatorio-gerente');
        Route::get('/diarias/exportar', [DiariasController::class, 'exportar'])->name('diarias.exportar');
    });

    // Relatórios (com verificação de permissão específica)
    Route::middleware(['auth'])->group(function () {
        Route::get('/relatorio-1000', [DiariasController::class, 'relatorio1000'])
            ->middleware('can:Ver Relatório 1000')
            ->name('relatorio.1000');
        
        Route::get('/relatorio-1001', [DiariasController::class, 'relatorio1001'])
            ->middleware('can:Ver Relatório 1001')
            ->name('relatorio.1001');
        
        Route::get('/relatorio-1002', [DiariasController::class, 'relatorio1002'])
            ->middleware(['auth', 'can:Ver Relatório 1002'])
            ->name('relatorio.1002');
        
        Route::get('/relatorios/buscar-recursos-humanos', [DiariasController::class, 'buscarRecursosHumanos'])
            ->middleware('can:Ver Relatório 1002')
            ->name('relatorio.rh.buscar');
    });

    // Rota para buscar diárias dos gerentes
    Route::get('/relatorios/buscar-diarias-gerentes', [DiariasController::class, 'buscarDiariasGerentes'])
        ->name('relatorios.buscar-diarias-gerentes');

    // Rota para buscar lista de gerentes
    Route::get('/api/gerentes', [DiariasController::class, 'listarGerentes'])
        ->name('api.gerentes');

    // Rota para buscar lista apenas de gerentes que têm diárias registradas
    Route::get('/api/gerentes-com-diarias', [DiariasController::class, 'listarGerentesComDiarias'])
        ->name('api.gerentes-com-diarias');

    // Rota para listar todos os usuários
    Route::get('/api/usuarios/listar', function() {
        $usuarios = \App\Models\User::with('profile')->get();
        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    });
    // Rota para obter dados de usuários via API
    Route::get('/api/usuarios/{id}', [UsuariosController::class, 'show']);
    // API para atualizar usuário
    Route::put('/api/usuarios/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $usuario = \App\Models\User::findOrFail($id);
            
            $validacao = [
                'name' => 'required|string|max:255',
                'profile_id' => 'nullable|exists:profiles,id'
            ];
            
            // Adicionar validação de senha apenas se foi enviada
            if ($request->filled('password')) {
                $validacao['password'] = 'required|string|min:6|confirmed';
            }
            
            $request->validate($validacao);
            
            // Atualizar dados básicos
            $usuario->name = $request->name;
            $usuario->profile_id = $request->profile_id;
            
            // Atualizar senha apenas se foi enviada
            if ($request->filled('password')) {
                $usuario->password = bcrypt($request->password);
            }
            
            $usuario->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ], 500);
        }
    });

    // Rota para atualizar usuário
    Route::post('/atualizar-usuario', [UsuariosController::class, 'update']);

    // Rota para ativar/desativar usuário
    Route::post('/toggle-user-status', [UsuariosController::class, 'toggleStatus']);

    // Rota para atualizar o perfil do usuário
    Route::post('/atualizar-perfil-usuario', function (Request $request) {
        try {
            // Validar dados
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'profile_id' => 'required|exists:profiles,id'
            ]);
            
            // Atualizar o usuário diretamente no banco
            $resultado = DB::table('users')
                ->where('id', $validated['user_id'])
                ->update(['profile_id' => $validated['profile_id']]);
            
            if ($resultado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma alteração foi realizada'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar perfil: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    });

    // APIs para gerenciamento de perfis
    Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
        // Permissões
        Route::get('/permissoes/listar', [PermissoesController::class, 'listar']);
        Route::get('/permissoes', [PermissoesController::class, 'listar']);
        Route::get('/permissoes/{id}', [PermissoesController::class, 'obter']);
        Route::post('/permissoes', [PermissoesController::class, 'store']);
        Route::put('/permissoes/{id}', [PermissoesController::class, 'update']);
        Route::delete('/permissoes/{id}', [PermissoesController::class, 'destroy']);

        // Perfis
        Route::get('/perfis', [App\Http\Controllers\PermissoesController::class, 'listarPerfis']);
        Route::get('/perfis/{id}', [App\Http\Controllers\PermissoesController::class, 'obterPerfil']);
        Route::post('/perfis', function (Request $request) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name',
                'description' => 'nullable|string'
            ]);
            
            $perfil = \App\Models\Profile::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Perfil criado com sucesso',
                'data' => $perfil
            ]);
        });
        Route::put('/perfis/{id}', function ($id) {
            try {
                $perfil = \App\Models\Profile::findOrFail($id);
                
                request()->validate([
                    'name' => 'required|string|max:255|unique:profiles,name,' . $id,
                    'description' => 'nullable|string',
                    'permissions' => 'nullable|array',
                    'permissions.*' => 'integer'
                ]);
                
                $perfil->update([
                    'name' => request('name'),
                    'description' => request('description')
                ]);
                
                if (request()->has('permissions')) {
                    DB::table('profile_permissions')
                        ->where('profile_id', $id)
                        ->delete();
                    
                    $permissions = request('permissions');
                    
                    $data = array_map(function($permissionId) use ($id) {
                        return [
                            'profile_id' => $id,
                            'permission_id' => (int)$permissionId
                        ];
                    }, $permissions);
                    
                    DB::table('profile_permissions')->insert($data);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso',
                    'data' => $perfil->load('permissions')
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
                ], 500);
            }
        });
        Route::delete('/perfis/{id}', function ($id) {
            $perfil = \App\Models\Profile::findOrFail($id);
            DB::table('users')->where('profile_id', $id)->update(['profile_id' => null]);
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            $perfil->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Perfil excluído com sucesso'
            ]);
        });
    });

    // Rotas do RH - com middleware de permissão personalizado
    Route::middleware(['auth'])->group(function () {
        Route::prefix('rh')->group(function () {
            // Rota para Administrador - sem middleware de permissão
            Route::get('/administrador', function() {
                try {
                    // Verificar se a coluna existe na tabela
                    if (!Schema::hasColumn('rh_problemas', 'prioridade')) {
                        // Adicionar a coluna se não existir
                        Schema::table('rh_problemas', function ($table) {
                            $table->enum('prioridade', ['baixa', 'media', 'alta'])->default('media');
                        });
                        DB::statement('UPDATE rh_problemas SET prioridade = "media" WHERE prioridade IS NULL');
                    }

                    // VERIFICAÇÃO CRÍTICA: Buscar TODOS os registros diretamente
                    $problemasBrutos = DB::select('SELECT * FROM rh_problemas ORDER BY created_at DESC');
                    
                    // Verificar status encontrados
                    $statusEncontrados = [];
                    foreach ($problemasBrutos as $p) {
                        $status = $p->status ?? 'null';
                        if (!isset($statusEncontrados[$status])) {
                            $statusEncontrados[$status] = 0;
                        }
                        $statusEncontrados[$status]++;
                    }
                    
                    // Corrigir todos os registros com status inválido
                    foreach ($statusEncontrados as $status => $count) {
                        if (!in_array($status, ['Pendente', 'Em andamento', 'Concluído', 'Concluida'])) {
                            DB::table('rh_problemas')
                                ->where('status', $status)
                                ->update(['status' => 'Pendente']);
                            \Log::info("Corrigido status inválido: '{$status}' para 'Pendente'");
                        }
                    }
                    
                    // Corrigir valores de status específicos
                    DB::statement("UPDATE rh_problemas SET status = 'Concluído' WHERE status = 'Concluida'");
                    
                    // Recarregar todos com join
                    $problemas = DB::select('
                        SELECT 
                            rp.*,
                            COALESCE(u.name, "Não atribuído") as usuario_nome
                        FROM 
                            rh_problemas rp
                        LEFT JOIN 
                            users u ON rp.usuario_id = u.id
                        ORDER BY 
                            rp.created_at DESC
                    ');
                    
                    // Converter para coleção
                    $problemas = collect($problemas);
                    
                    \Log::info("Consulta SQL direta encontrou: " . count($problemas) . " problemas");
                    \Log::info("Status encontrados: " . json_encode($statusEncontrados));
                    
                    return view('rh.administrador', compact('problemas'));
                } catch (\Exception $e) {
                    \Log::error("Erro ao carregar administrador RH: " . $e->getMessage());
                    \Log::error("Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")");
                    // Em caso de erro, ainda tentamos retornar a view
                    $problemas = collect([]);
                    return view('rh.administrador', compact('problemas'));
                }
            })->name('rh.administrador');
            
            Route::middleware(['can:RH'])->group(function () {
                Route::get('/create', [RHController::class, 'create'])->name('rh.create');
                Route::post('/store', [RHController::class, 'store'])->name('rh.store');
                Route::put('/update-status/{problema}', [RHController::class, 'updateStatus'])->name('rh.update-status');
                Route::delete('/destroy/{problema}', [RHController::class, 'destroy'])->name('rh.destroy');
                Route::get('/problemas/{problema}/anotacoes', [RHController::class, 'getAnotacoes'])->name('rh.anotacoes');
                Route::get('/get-anotacoes/{problema}', [RHController::class, 'getAnotacoes'])->name('rh.get-anotacoes');
            });
            
            // Rotas acessíveis a todos usuários autenticados
            Route::middleware(['auth'])->group(function () {
                Route::get('/edit/{problema}', [RHController::class, 'edit'])->name('rh.edit');
                Route::put('/update/{problema}', [RHController::class, 'update'])->name('rh.update');
            });
            
            // Rotas de Tarefas - requer permissão 'Tarefas'
            Route::middleware(['can:Tarefas'])->group(function () {
                Route::get('/tarefas', [RHController::class, 'tarefas'])->name('rh.tarefas');
                Route::put('/problemas/{problema}/iniciar', [RHController::class, 'iniciar'])->name('rh.iniciar');
            });
            
            // Rota para Documentos RH - requer permissão 'Documentos RH'
            Route::middleware(['can:Documentos RH'])->group(function () {
                Route::get('/documentos', function() {
                    return view('rh.documentos');
                })->name('rh.documentos');
                
                // Nova rota para processar o envio de documentos
                Route::post('/documentos/store', function(Request $request) {
                    try {
                        // Validar os dados
                        $request->validate([
                            'nome' => 'required|string|max:255',
                            'foto' => 'nullable|image|max:2048', // Máximo 2MB
                            'doc_fotos' => 'nullable|mimes:pdf|max:5120', // Máximo 5MB
                            'doc_carteira_saude' => 'nullable|mimes:pdf|max:5120',
                            'doc_exame' => 'required|mimes:pdf|max:5120',
                            'doc_antecedente' => 'required|mimes:pdf|max:5120',
                        ]);
                        
                        // Criar diretório para armazenar os arquivos se não existir
                        $diretorio = 'documentos_rh/' . date('Y-m');
                        if (!Storage::exists($diretorio)) {
                            Storage::makeDirectory($diretorio);
                        }
                        
                        // Gerar um código único para este conjunto de documentos
                        $codigo = 'DOC-' . date('YmdHis') . '-' . rand(1000, 9999);
                        
                        // Armazenar os arquivos
                        $caminhos = [];
                        
                        // Processar a foto
                        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
                            $caminho = $request->file('foto')->store($diretorio);
                            $caminhos['foto'] = $caminho;
                        }
                        
                        // Processar os documentos em PDF
                        $campos_pdf = ['doc_fotos', 'doc_carteira_saude', 'doc_exame', 'doc_antecedente'];
                        foreach ($campos_pdf as $campo) {
                            if ($request->hasFile($campo) && $request->file($campo)->isValid()) {
                                $caminho = $request->file($campo)->store($diretorio);
                                $caminhos[$campo] = $caminho;
                            }
                        }
                        
                        // Salvar os dados no banco
                        $documento = DB::table('documentos_rh')->insert([
                            'nome' => $request->nome,
                            'codigo' => $codigo,
                            'foto' => $caminhos['foto'] ?? null,
                            'doc_fotos' => $caminhos['doc_fotos'] ?? null,
                            'doc_carteira_saude' => $caminhos['doc_carteira_saude'] ?? null,
                            'doc_exame' => $caminhos['doc_exame'] ?? null,
                            'doc_antecedente' => $caminhos['doc_antecedente'] ?? null,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                        
                        return redirect()->route('rh.documentos')
                            ->with('success', 'Documentos enviados com sucesso! Código: ' . $codigo);
                            
                    } catch (\Exception $e) {
                        \Log::error('Erro ao salvar documentos: ' . $e->getMessage());
                        return redirect()->route('rh.documentos')
                            ->with('error', 'Erro ao enviar documentos: ' . $e->getMessage());
                    }
                })->name('rh.documentos.store');
            });
            
            // Rotas de Tarefas por Usuários - requer permissão 'Tarefas Usuarios'
            Route::middleware(['can:Tarefas Usuarios'])->group(function () {
                Route::get('/tarefas-por-usuarios', [RHController::class, 'tarefasPorUsuarios'])->name('rh.tarefas-por-usuarios');
                Route::put('/problemas/{problema}/concluir', [RHController::class, 'concluir'])->name('rh.concluir');
            });

            // Rotas do Cronograma
            Route::prefix('cronograma')->middleware(['can:Cronograma'])->group(function () {
                Route::get('/', [CronogramaController::class, 'index'])->name('rh.cronograma.index');
                Route::get('/eventos', [CronogramaController::class, 'eventos'])->name('rh.cronograma.eventos');
                Route::post('/sincronizar', [CronogramaController::class, 'sincronizar'])->name('rh.cronograma.sincronizar');
                Route::post('/store', [CronogramaController::class, 'store'])->name('rh.cronograma.store');
                Route::put('/update/{id}', [CronogramaController::class, 'update'])->name('rh.cronograma.update');
                Route::delete('/destroy/{id}', [CronogramaController::class, 'destroy'])->name('rh.cronograma.destroy');
                Route::get('/datas/{id}', [CronogramaController::class, 'carregarDatas'])->name('rh.cronograma.datas');
            });
        });
    });
});

Auth::routes();

// Redirecionamentos para URLs sem o prefixo /admin
Route::get('/gerenciar-permissoes', function () {
    return redirect('/permissoes');
});

// Remover este redirecionamento que causa erro
// Route::get('/permissoes', function () {
//    return redirect()->route('admin.permissoes');
// });

// Remover redirecionamento que causa loop
Route::get('/perfis', function () {
    $perfis = \App\Models\Profile::all();
    $permissoes = \App\Models\Permission::all();
    return view('admin.perfis', compact('perfis', 'permissoes'));
});

// Rotas de administração
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/gerenciar-permissoes', function () {
        $users = \App\Models\User::with('profile')->get();
        $profiles = \App\Models\Profile::all();
        return view('admin.gerenciar-permissoes', compact('users', 'profiles'));
    })->name('gerenciar-permissoes');
    
    // Remover esta rota que está causando conflito
    // Route::get('/permissoes', function () {
    //     return app(\App\Http\Controllers\PermissoesController::class)->index();
    // })->name('admin.permissoes');
    
    // Remover a rota admin.perfis que está causando conflito
    // Route::get('/perfis', function () {
    //    $perfis = \App\Models\Profile::all();
    //    $usuarios = \App\Models\User::with('profile')->get();
    //    return view('admin.gerenciar-perfis', compact('perfis', 'usuarios'));
    // })->name('admin.perfis');
});

// APIs para gerenciamento de permissões
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Obter todas as permissões
    Route::get('/permissoes/listar', function() {
        $permissoes = \App\Models\Permission::all();
        return response()->json([
            'success' => true,
            'data' => $permissoes
        ]);
    });
    
    // Obter uma permissão específica
    Route::get('/permissoes/{id}', function($id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $permissao
        ]);
    });
    
    // Criar uma nova permissão
    Route::post('/permissoes', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'code' => 'nullable|string',
            'description' => 'nullable|string'
        ]);
        
        $permissao = \App\Models\Permission::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso',
            'data' => $permissao
        ]);
    });
    
    // Atualizar uma permissão
    Route::put('/permissoes/{id}', function(\Illuminate\Http\Request $request, $id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'code' => 'nullable|string',
            'description' => 'nullable|string'
        ]);
        
        $permissao->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso',
            'data' => $permissao
        ]);
    });
    
    // Excluir uma permissão
    Route::delete('/permissoes/{id}', function($id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        $permissao->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso'
        ]);
    });
});

// API para usuários (usado no select do formulário)
Route::get('/api/usuarios', function() {
    return App\Models\User::select('id', 'name')->get();
})->name('api.usuarios');

// API para obter dados de usuário
Route::get('/api/usuarios/{id}', function($id) {
    try {
        $usuario = \App\Models\User::with('profile')->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'data' => $usuario
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erro ao obter dados do usuário: ' . $e->getMessage()
        ], 500);
    }
});

// Rota temporária para corrigir status
Route::get('/corrigir-status-rh', function () {
    // Buscar registros com status "Concluída" (antigo)
    $registros = App\Models\RHProblema::where('status', 'Concluída')->get();
    
    $count = $registros->count();
    echo "Encontrados {$count} registros com status 'Concluída'<br>";
    
    if ($count > 0) {
        // Atualizar todos para "Concluído" (novo)
        foreach ($registros as $registro) {
            echo "Atualizando ID {$registro->id}<br>";
            $registro->status = 'Concluído';
            $registro->save();
        }
        
        echo 'Correção concluída com sucesso!<br>';
    } else {
        echo 'Nenhum registro precisava ser corrigido.<br>';
    }
    
    // Verificar os status existentes
    $status = DB::table('rh_problemas')
        ->select('status')
        ->distinct()
        ->orderBy('status')
        ->get()
        ->pluck('status');
        
    echo 'Status disponíveis no banco:<br>';
    foreach ($status as $st) {
        echo " - {$st}<br>";
    }
    
    return "Processo concluído!";
})->middleware('auth');

// Rota para atualizar diretamente o status
Route::get('/atualizar-status/{id}/{status}', function ($id, $status) {
    try {
        // Verificar se o status é válido
        if (!in_array($status, ['Pendente', 'Em andamento', 'Concluído', 'No prazo'])) {
            return "Status inválido. Use: Pendente, Em andamento, Concluído ou No prazo";
        }
        
        // Buscar o registro
        $problema = \App\Models\RHProblema::findOrFail($id);
        $statusAntigo = $problema->status;
        
        // Atualizar o status
        $problema->status = $status;
        $resultado = $problema->save();
        
        if ($resultado) {
            // Verificar se a atualização foi realmente aplicada
            $problemaAtualizado = \App\Models\RHProblema::findOrFail($id);
            
            if ($problemaAtualizado->status === $status) {
                return "Status atualizado com sucesso de '{$statusAntigo}' para '{$status}'!";
            } else {
                return "Falha na verificação. Status no banco: '{$problemaAtualizado->status}'";
            }
        } else {
            return "Erro ao salvar: operação de save() retornou false";
        }
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
})->middleware('auth');

// Rota de emergência para corrigir o registro ID 70
Route::get('/corrigir-registro-70', function() {
    try {
        // Verificar status atual
        $statusAtual = DB::table('rh_problemas')->where('id', 70)->value('status');
        echo "Status atual do registro #70: " . $statusAtual . "<br>";
        
        // Forçar atualização direta no banco
        $atualizado = DB::table('rh_problemas')
            ->where('id', 70)
            ->update(['status' => 'Pendente']);
        
        echo "Atualização forçada: " . ($atualizado ? "Sim" : "Não") . "<br>";
        
        // Verificar status após atualização
        $novoStatus = DB::table('rh_problemas')->where('id', 70)->value('status');
        echo "Novo status do registro #70: " . $novoStatus . "<br>";
        
        // Limpar cache do registro
        Cache::forget('rh_problema_70');
        echo "Cache limpo para o registro #70";
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
});

// Nova rota para atualização de status via AJAX
Route::post('/rh/problemas/{id}/status', [App\Http\Controllers\RHController::class, 'updateStatus'])->name('rh.problema.status');

// Rotas de Debug para depuração
Route::get('/debug/enable-sql-log', function() {
    \DB::enableQueryLog();
    \Log::info('Logging SQL ativado via AJAX');
    return response()->json(['status' => 'success', 'message' => 'SQL logging enabled']);
});

// Rota de teste específica para diagnóstico de problemas com datas
Route::get('/debug/teste-data/{data?}', function($data = null) {
    // Se não for fornecida uma data, usar um valor padrão
    if (!$data) {
        $data = "31/03/2025 11:54";
    }
    
    \Log::info("Iniciando teste de data com: " . $data);
    
    // Ativar log de consultas
    \DB::enableQueryLog();
    
    // Array para armazenar os resultados dos testes
    $resultados = [];
    
    try {
        // 1. Teste direto com Carbon
        try {
            $carbon = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $data);
            $resultados['carbon_direto'] = [
                'status' => 'sucesso',
                'resultado' => $carbon->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $resultados['carbon_direto'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // 2. Teste com regex e construção manual
        try {
            // Sanitizar a data
            $dataSanitizada = preg_replace('/[^\d\/: ]/', '', trim($data));
            
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/', $dataSanitizada, $matches)) {
                $dia = (int)$matches[1];
                $mes = (int)$matches[2];
                $ano = (int)$matches[3];
                $hora = (int)$matches[4];
                $minuto = (int)$matches[5];
                
                // Formatação direta para SQL
                $dataSQL = sprintf('%04d-%02d-%02d %02d:%02d:00', $ano, $mes, $dia, $hora, $minuto);
                
                $resultados['regex_manual'] = [
                    'status' => 'sucesso',
                    'dados_extraidos' => [
                        'dia' => $dia,
                        'mes' => $mes,
                        'ano' => $ano,
                        'hora' => $hora,
                        'minuto' => $minuto
                    ],
                    'sql_formatado' => $dataSQL
                ];
                
                // Verificar se o Carbon aceita esta string
                try {
                    $carbonTeste = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataSQL);
                    $resultados['regex_manual']['verificacao_carbon'] = [
                        'status' => 'sucesso',
                        'resultado' => $carbonTeste->format('Y-m-d H:i:s')
                    ];
                } catch (\Exception $e) {
                    $resultados['regex_manual']['verificacao_carbon'] = [
                        'status' => 'erro',
                        'mensagem' => $e->getMessage()
                    ];
                }
            } else {
                $resultados['regex_manual'] = [
                    'status' => 'erro',
                    'mensagem' => 'A data não corresponde ao formato esperado',
                    'data_sanitizada' => $dataSanitizada
                ];
            }
        } catch (\Exception $e) {
            $resultados['regex_manual'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // 3. Teste com o banco de dados
        try {
            // Criar um problema de teste
            $problema = new \App\Models\RHProblema();
            $problema->descricao = 'Teste de data - ' . now();
            $problema->status = 'Pendente';
            $problema->prioridade = 'media';
            $problema->id_usuario = 1;
            
            // Definir a data diretamente como string no formato SQL
            $dataSQL = $resultados['regex_manual']['sql_formatado'] ?? null;
            
            if ($dataSQL) {
                $problema->prazo_entrega = $dataSQL;
                $resultado = $problema->save();
                
                $resultados['teste_bd'] = [
                    'status' => $resultado ? 'sucesso' : 'erro',
                    'id_problema' => $problema->id,
                    'data_salva' => $problema->prazo_entrega
                ];
                
                // Limpar o teste (excluir o registro)
                if ($resultado) {
                    $problema->delete();
                }
            } else {
                $resultados['teste_bd'] = [
                    'status' => 'pulado',
                    'motivo' => 'Não foi possível obter uma data SQL válida'
                ];
            }
        } catch (\Exception $e) {
            $resultados['teste_bd'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // Log das consultas SQL
        $resultados['consultas_sql'] = \DB::getQueryLog();
        
        return response()->json([
            'status' => 'sucesso',
            'data_original' => $data,
            'resultados' => $resultados
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'erro',
            'mensagem' => $e->getMessage(),
            'data_original' => $data,
            'resultados' => $resultados ?? []
        ], 500);
    }
});

// Nova rota para mostrar todas as tarefas, independente do status
Route::get('/mostrar-todas-tarefas', function() {
    try {
        // Verificar todos os registros existentes
        $todosRegistros = DB::table('rh_problemas')->get();
        
        echo "<h2>Todos os registros encontrados: " . $todosRegistros->count() . "</h2>";
        
        // Agrupar por status
        $porStatus = [];
        foreach ($todosRegistros as $registro) {
            $status = $registro->status ?? 'null';
            if (!isset($porStatus[$status])) {
                $porStatus[$status] = 0;
            }
            $porStatus[$status]++;
        }
        
        echo "<h3>Agrupados por status:</h3>";
        echo "<ul>";
        foreach ($porStatus as $status => $count) {
            echo "<li>Status '{$status}': {$count} registros</li>";
        }
        echo "</ul>";
        
        // Tentar resolver a inconsistência
        echo "<h3>Tentando corrigir os registros:</h3>";
        
        // 1. Correção de registros nulls
        $nullsCorrigidos = DB::table('rh_problemas')
            ->whereNull('status')
            ->update(['status' => 'Pendente']);
        
        echo "<p>Registros com status NULL corrigidos: {$nullsCorrigidos}</p>";
        
        // 2. Padronização de status
        $concluidos = DB::table('rh_problemas')
            ->whereRaw("LOWER(status) LIKE '%conclu%'")
            ->update(['status' => 'Concluído']);
        
        echo "<p>Registros de concluídos padronizados: {$concluidos}</p>";
        
        $emAndamento = DB::table('rh_problemas')
            ->whereRaw("LOWER(status) LIKE '%andamento%'")
            ->update(['status' => 'Em andamento']);
        
        echo "<p>Registros em andamento padronizados: {$emAndamento}</p>";
        
        $pendentes = DB::table('rh_problemas')
            ->whereRaw("LOWER(status) LIKE '%pend%'")
            ->update(['status' => 'Pendente']);
        
        echo "<p>Registros pendentes padronizados: {$pendentes}</p>";
        
        // 3. Forçar atualização do status para todos os itens na view de administrador
        // Isso vai garantir que todos os registros sejam atualizados com valores válidos
        $atualizadosTotal = DB::statement('
            UPDATE rh_problemas 
            SET status = CASE 
                WHEN LOWER(status) LIKE "%conclu%" THEN "Concluído"
                WHEN LOWER(status) LIKE "%andamento%" THEN "Em andamento"
                ELSE "Pendente"
            END
        ');
        
        echo "<p>Força geral de atualização executada</p>";
        
        // 4. Verificar novamente os registros após as correções
        $statusCorrigidos = DB::table('rh_problemas')
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();
            
        echo "<h3>Status após correção:</h3>";
        echo "<ul>";
        foreach ($statusCorrigidos as $status) {
            echo "<li>Status '{$status->status}': {$status->total} registros</li>";
        }
        echo "</ul>";
        
        // 5. Para garantir consistência, vamos retornar todos os registros na view
        $registros = DB::table('rh_problemas as rp')
            ->select(
                'rp.id',
                'rp.descricao',
                'rp.status',
                'rp.prioridade',
                'rp.detalhes',
                'rp.resposta',
                'rp.data_resposta',
                'rp.inicio_contagem',
                'rp.prazo_entrega',
                'rp.finalizado_em',
                'rp.created_at',
                'rp.updated_at',
                DB::raw('COALESCE(u.name, "Não atribuído") as usuario_nome')
            )
            ->leftJoin('users as u', 'rp.usuario_id', '=', 'u.id')
            ->orderBy('rp.created_at', 'desc')
            ->get();
            
        // Exibir todos os registros independente de status
        echo "<h2>Todos os registros (independente de status):</h2>";
        echo "<table border='1' cellpadding='3' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Descrição</th><th>Status</th><th>Usuário</th><th>Criado em</th></tr>";
        
        foreach ($registros as $registro) {
            echo "<tr>";
            echo "<td>{$registro->id}</td>";
            echo "<td>{$registro->descricao}</td>";
            echo "<td><strong>{$registro->status}</strong></td>";
            echo "<td>{$registro->usuario_nome}</td>";
            echo "<td>{$registro->created_at}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        echo "<p><a href='/rh/administrador' style='padding: 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px;'>Voltar para Administrador RH</a></p>";
        
        return "";
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
})->middleware('auth');

// Rota para listar explicitamente as tarefas concluídas
Route::get('/tarefas-concluidas', function() {
    try {
        $concluidas = DB::table('rh_problemas')
            ->where('status', 'Concluído')
            ->orWhere('status', 'Concluido')
            ->orWhere('status', 'concluído')
            ->orWhere('status', 'concluido')
            ->orWhereRaw("LOWER(status) LIKE '%conclu%'")
            ->get();
            
        echo "<h2>Tarefas Concluídas: " . $concluidas->count() . "</h2>";
        
        if ($concluidas->count() > 0) {
            echo "<table border='1' cellpadding='3' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Descrição</th><th>Status</th><th>Criado em</th></tr>";
            
            foreach ($concluidas as $tarefa) {
                echo "<tr>";
                echo "<td>{$tarefa->id}</td>";
                echo "<td>{$tarefa->descricao}</td>";
                echo "<td><strong>{$tarefa->status}</strong></td>";
                echo "<td>{$tarefa->created_at}</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>Nenhuma tarefa concluída encontrada.</p>";
        }
        
        // Para cada tarefa, verificar se é mostrada na view de administrador
        if ($concluidas->count() > 0) {
            echo "<h3>Verificando se estas tarefas aparecem na view de administrador:</h3>";
            
            // Buscar exatamente como a view de administrador busca
            $problemasAdm = DB::table('rh_problemas as rp')
                ->select(
                    'rp.id',
                    'rp.descricao',
                    'rp.status'
                )
                ->whereRaw('1=1')
                ->orderBy('rp.created_at', 'desc')
                ->get();
                
            // Criar um array de IDs dos problemas na view de administrador
            $idsAdm = $problemasAdm->pluck('id')->toArray();
            
            echo "<table border='1' cellpadding='3' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Descrição</th><th>Status</th><th>Aparece na View</th></tr>";
            
            foreach ($concluidas as $tarefa) {
                $aparece = in_array($tarefa->id, $idsAdm) ? "SIM" : "NÃO";
                $cor = $aparece == "SIM" ? "green" : "red";
                
                echo "<tr>";
                echo "<td>{$tarefa->id}</td>";
                echo "<td>{$tarefa->descricao}</td>";
                echo "<td><strong>{$tarefa->status}</strong></td>";
                echo "<td style='color: {$cor}'><strong>{$aparece}</strong></td>";
                echo "</tr>";
            }
            
            echo "</table>";
        }
        
        echo "<p><a href='/rh/administrador' style='padding: 10px; background-color: #007bff; color: white; text-decoration: none; border-radius: 4px; margin-right: 10px;'>Voltar para Administrador RH</a>";
        echo "<a href='/mostrar-todas-tarefas' style='padding: 10px; background-color: #28a745; color: white; text-decoration: none; border-radius: 4px;'>Ver Todas as Tarefas</a></p>";
        
        return "";
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
})->middleware('auth');

// APIs para gerenciamento de perfis
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Obter todos os perfis
    Route::get('/perfis/listar', function() {
        $perfis = \App\Models\Profile::all();
        return response()->json([
            'success' => true,
            'data' => $perfis
        ]);
    });
    
    // Obter um perfil específico
    Route::get('/perfis/{id}', function($id) {
        $perfil = \App\Models\Profile::with('permissions')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $perfil
        ]);
    });
});
