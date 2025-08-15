<?php

use Illuminate\Support\Facades\Route;
// use App\Livewire\CadastroDiarias; // Módulo Diárias removido do menu/rotas
// use App\Http\Controllers\DiariasController; // Removido
use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\UsuariosController;
// use App\Http\Controllers\RHController; // Removido
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Livewire\GerenciarPermissoes;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
// use App\Http\Controllers\CronogramaController; // Removido
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
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

// Removido acesso direto à view/mecejana de Diárias

Route::middleware(['auth'])->group(function () {
    // Dashboard - acessível para todos os usuários autenticados
    Route::get('/home', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

    // Rotas de Diárias removidas

    // Rotas de Documentos DP - protegidas pela permissão 'doc_dp'
    Route::middleware(['can:doc_dp','throttle:60,1'])->group(function () {
        Route::get('/documentos-dp/inclusao', [App\Http\Controllers\DocumentosDPController::class, 'inclusao'])->name('documentos-dp.inclusao');
        Route::post('/documentos-dp/inclusao', [App\Http\Controllers\DocumentosDPController::class, 'store'])->name('documentos-dp.store');
        Route::get('/documentos-dp/arquivo/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadBLOB'])->name('documentos-dp.arquivo');
        Route::get('/documentos-dp/buscar', [App\Http\Controllers\DocumentosDPController::class, 'buscarFuncionario'])->name('documentos-dp.buscar');
        Route::get('/documentos-dp/funcionario/{id}/documentos', [App\Http\Controllers\DocumentosDPController::class, 'listarDocumentos'])->name('documentos-dp.documentos');
    });

    // Página de visualização de funcionários (somente quem tem a permissão específica)
    Route::middleware(['can:vis_func','throttle:60,1'])->group(function () {
        Route::view('/documentos-dp/funcionarios', 'documentos-dp.funcionarios')->name('documentos-dp.funcionarios');
        // Endpoint para anexar documentos faltantes na página de funcionários
        Route::post('/documentos-dp/funcionario/{id}/anexar', [App\Http\Controllers\DocumentosDPController::class, 'anexarFaltantes'])->name('documentos-dp.anexar');
                    // Endpoint para demitir funcionário
            Route::post('/documentos-dp/funcionario/{id}/demitir', [App\Http\Controllers\DocumentosDPController::class, 'demitirFuncionario'])->name('documentos-dp.demitir');
            // Endpoint para alterar status do funcionário
            Route::post('/documentos-dp/funcionario/{id}/alterar-status', [App\Http\Controllers\DocumentosDPController::class, 'alterarStatusFuncionario'])->name('documentos-dp.alterar-status');
        
        // Endpoints para atestados
        Route::get('/documentos-dp/funcionario/{id}/atestados', [App\Http\Controllers\DocumentosDPController::class, 'listarAtestados'])->name('documentos-dp.atestados');
        Route::post('/documentos-dp/funcionario/{id}/atestados', [App\Http\Controllers\DocumentosDPController::class, 'anexarAtestado'])->name('documentos-dp.anexar-atestado');
        Route::get('/documentos-dp/atestado/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadAtestado'])->name('documentos-dp.atestado');
        
        // Endpoints para advertências
        Route::get('/documentos-dp/funcionario/{id}/advertencias', [App\Http\Controllers\DocumentosDPController::class, 'listarAdvertencias'])->name('documentos-dp.advertencias');
        Route::post('/documentos-dp/funcionario/{id}/advertencias', [App\Http\Controllers\DocumentosDPController::class, 'aplicarAdvertencia'])->name('documentos-dp.aplicar-advertencia');
        Route::get('/documentos-dp/advertencia/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadAdvertencia'])->name('documentos-dp.advertencia');
        
        // Endpoints para EPIs (materiais retirados)
        Route::get('/documentos-dp/funcionario/{id}/epis', [App\Http\Controllers\DocumentosDPController::class, 'listarEpis'])->name('documentos-dp.epis');
    });

    // Rotas de BRS - Controle de Estoque - protegidas pela permissão 'Controle de Estoque'
    Route::middleware(['can:controle-estoque','throttle:120,1'])->group(function () {
    Route::get('/brs/controle-estoque', [App\Http\Controllers\ControleEstoqueController::class, 'index'])->name('brs.controle-estoque');
    Route::get('/api/funcionarios', [App\Http\Controllers\ControleEstoqueController::class, 'buscarFuncionarios']);
    Route::get('/api/centro-custos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarCentroCustos']);
    Route::get('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutos']);
    Route::get('/api/produtos-em-falta', [App\Http\Controllers\ControleEstoqueController::class, 'produtosEmFalta']);
    // Endpoint específico do módulo de estoque para evitar conflito com pedidos de compras
    Route::get('/api/estoque/produtos/buscar', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutosPorNome']);
    Route::post('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'criarProduto']);
    Route::put('/api/produtos/{id}', [App\Http\Controllers\ControleEstoqueController::class, 'atualizarProduto']);
    Route::post('/api/entradas', [App\Http\Controllers\ControleEstoqueController::class, 'registrarEntrada']);
    Route::post('/api/baixas', [App\Http\Controllers\ControleEstoqueController::class, 'registrarBaixa']);
});

    // Rotas de Relatórios - protegidas por suas respectivas permissões
    Route::get('/relatorios', function() {
        return view('relatorios.index');
    })->middleware('auth')->name('relatorios.index');
    
    Route::middleware(['can:relatorio-estoque','throttle:60,1'])->group(function () {
        Route::get('/relatorios/estoque', [App\Http\Controllers\RelatorioEstoqueController::class, 'index'])->name('relatorios.estoque');
        Route::post('/api/relatorio-estoque', [App\Http\Controllers\RelatorioEstoqueController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-estoque/exportar', [App\Http\Controllers\RelatorioEstoqueController::class, 'exportarExcel']);
        Route::get('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutos']);
    });

    Route::middleware(['can:relatorio-centro-custo','throttle:60,1'])->group(function () {
        Route::get('/relatorios/centro-custo', [App\Http\Controllers\RelatorioCentroCustoController::class, 'index'])->name('relatorios.centro-custo');
        Route::post('/api/relatorio-centro-custo', [App\Http\Controllers\RelatorioCentroCustoController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-centro-custo/exportar', [App\Http\Controllers\RelatorioCentroCustoController::class, 'exportarExcel']);
    });

    Route::middleware(['can:relatorio-funcionario','throttle:60,1'])->group(function () {
        Route::get('/relatorios/funcionario', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'index'])->name('relatorios.funcionario');
        Route::post('/api/relatorio-funcionario', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-funcionario/exportar', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'exportarExcel']);
    });

    // Relatório: Pedido de Compras (somente para quem tem a permissão específica criada pelo usuário)
    Route::middleware(['can:rel_pc','throttle:60,1'])->group(function () {
        // Página do relatório
        Route::get('/relatorios/pedidos-compra', function () {
            return view('relatorios.relatorio-pedido-compras');
        })->name('relatorios.pedidos-compra');

        // Endpoints de leitura usados pela página do relatório
        Route::get('/api/relatorio-pc/aprovados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosAprovadosAgrupados']);
        Route::get('/api/relatorio-pc/rejeitados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosRejeitadosAgrupados']);
        Route::get('/api/relatorio-pc/detalhes/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'detalhesPedidoAgrupado']);
        Route::get('/relatorio-pc/imprimir/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'imprimirPedido']);
    });

    // Rotas de Pedidos de Compras - protegidas por suas respectivas permissões
    Route::middleware(['can:solicitacao-pedidos','throttle:120,1'])->group(function () {
        Route::get('/pedidos/solicitacao', [App\Http\Controllers\PedidoComprasController::class, 'solicitacao'])->name('pedidos.solicitacao');
        Route::post('/api/pedidos', [App\Http\Controllers\PedidoComprasController::class, 'store']);
        Route::get('/api/minhas-solicitacoes', [App\Http\Controllers\PedidoComprasController::class, 'minhasSolicitacoes']);
        Route::get('/api/produtos/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarProdutos']);
        Route::get('/api/centro-custos', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCusto']);
        Route::get('/api/centro-custos/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCustoAutocomplete']);
    });

    Route::middleware(['can:autorizacao-pedidos','throttle:120,1'])->group(function () {
        Route::get('/pedidos/autorizacao', [App\Http\Controllers\PedidoComprasController::class, 'autorizacao'])->name('pedidos.autorizacao');
        Route::get('/pedidos/autorizacao/pendentes', [App\Http\Controllers\PedidoComprasController::class, 'autorizacoesPendentesView'])->name('pedidos.autorizacao.pendentes');
        Route::get('/pedidos/autorizacao/aprovadas', [App\Http\Controllers\PedidoComprasController::class, 'autorizacoesAprovadasView'])->name('pedidos.autorizacao.aprovadas');
        Route::get('/pedidos/autorizacao/rejeitadas', [App\Http\Controllers\PedidoComprasController::class, 'autorizacoesRejeitadasView'])->name('pedidos.autorizacao.rejeitadas');

        Route::get('/api/pedidos-pendentes', [App\Http\Controllers\PedidoComprasController::class, 'pedidosPendentes']);
        Route::get('/api/pedidos-pendentes-agrupados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosPendentesAgrupados']);
        Route::get('/api/pedidos-agrupado/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'detalhesPedidoAgrupado']);
        Route::put('/api/pedidos-agrupado/{hash}/aprovar', [App\Http\Controllers\PedidoComprasController::class, 'aprovarGrupo']);
        Route::put('/api/pedidos-agrupado/{hash}/rejeitar', [App\Http\Controllers\PedidoComprasController::class, 'rejeitarGrupo']);
        Route::post('/api/pedidos-agrupado/{hash}/mensagem', [App\Http\Controllers\PedidoComprasController::class, 'mensagemGrupo']);
        Route::get('/api/pedidos-aprovados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosAprovados']);
        Route::get('/api/pedidos-aprovados-agrupados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosAprovadosAgrupados']);
        Route::get('/api/pedidos-rejeitados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosRejeitados']);
        Route::get('/api/pedidos-rejeitados-agrupados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosRejeitadosAgrupados']);
        Route::put('/api/pedidos/{id}/aprovar', [App\Http\Controllers\PedidoComprasController::class, 'aprovar']);
        Route::put('/api/pedidos/{id}/rejeitar', [App\Http\Controllers\PedidoComprasController::class, 'rejeitar']);
    });

    // Endpoints utilitários disponíveis para telas autenticadas (ex.: Controle de Estoque)
    Route::middleware(['auth','throttle:120,1'])->group(function () {
        Route::get('/api/centros-custo', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCusto']);
        Route::get('/api/centros-custo/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCustoAutocomplete']);
    });

    // Página de histórico e interações dos pedidos do próprio usuário
    Route::middleware(['can:Pedidos de Compras'])->group(function () {
        Route::get('/pedidos/minhas-interacoes', [App\Http\Controllers\PedidoComprasController::class, 'minhasInteracoesView'])->name('pedidos.minhas.interacoes');
        Route::get('/api/pedidos/minhas-interacoes', [App\Http\Controllers\PedidoComprasController::class, 'minhasInteracoesData']);
        Route::get('/api/pedidos/{id}/interacoes', [App\Http\Controllers\PedidoComprasController::class, 'interacoesPorPedido']);
        Route::post('/api/pedidos/{id}/interagir', [App\Http\Controllers\PedidoComprasController::class, 'enviarInteracaoSolicitante']);
    });

    // Acompanhar Pedido (somente leitura) – permissão: Acompanhar Pedido
    Route::middleware(['can:Acompanhar Pedido','throttle:60,1'])->group(function () {
        Route::get('/pedidos/acompanhar', [App\Http\Controllers\PedidoComprasController::class, 'acompanharView'])->name('pedidos.acompanhar');
        Route::get('/pedidos/acompanhar/pendentes', function(){ return view('pedidos.acompanhar_pendentes'); })->name('pedidos.acompanhar.pendentes');
        Route::get('/pedidos/acompanhar/aprovadas', function(){ return view('pedidos.acompanhar_aprovadas'); })->name('pedidos.acompanhar.aprovadas');
        Route::get('/pedidos/acompanhar/rejeitadas', function(){ return view('pedidos.acompanhar_rejeitadas'); })->name('pedidos.acompanhar.rejeitadas');
        Route::get('/api/pedidos/acompanhar/lista', [App\Http\Controllers\PedidoComprasController::class, 'acompanharLista']);
        Route::get('/api/pedidos/acompanhar/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'acompanharDetalhes']);
    });

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

        // TESTE DE CONEXÃO FORÇADA (apenas ambiente local)
        if (app()->environment('local')) {
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
        }
        
        // Rota para exibir um perfil específico - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
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
        }
        
        // Rota para criar um novo perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
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
        }
        
        // Rota para atualizar um perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
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
        }
        
        // Rota para excluir um perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::delete('/perfis/{id}', function ($id) {
            // Remover relacionamentos primeiro diretamente do banco de dados
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            
            // Remover perfil diretamente do banco de dados
            DB::table('profiles')->where('id', $id)->delete();
            
            return redirect('/perfis')->with('success', 'Perfil excluído com sucesso');
        });
        }
        
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

    // Rotas de Diárias desativadas

    // Rota principal de relatórios removida - relatórios específicos desabilitados

    // Rota para buscar diárias dos gerentes
    // Relatórios de diárias desativados

    // Rota para buscar lista de gerentes
    // API de gerentes/diárias desativadas

    // Rota para buscar lista apenas de gerentes que têm diárias registradas
    // API de gerentes com diárias desativadas

    // Rota para listar todos os usuários
    Route::get('/api/usuarios/listar', function() {
        $usuarios = \App\Models\User::with('profile')->get();
        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    })->middleware(['can:gerenciar-usuarios','throttle:60,1']);
    // Rota para obter dados de usuários via API
    Route::get('/api/usuarios/{id}', [UsuariosController::class, 'show'])->middleware(['can:gerenciar-usuarios','throttle:60,1']);
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

    // Rotas do RH desativadas
    /*Route::middleware(['auth'])->group(function () {
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
            
            // Rotas de Tarefas removidas
            
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
            
            // Rotas de Tarefas por Usuários removidas

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
    });*/
});

Auth::routes();

// Redirecionamentos para URLs sem o prefixo /admin
Route::get('/gerenciar-permissoes', function () {
    return redirect('/permissoes');
});

// Rota de perfis
Route::get('/perfis', function () {
    $perfis = \App\Models\Profile::all();
    $permissoes = \App\Models\Permission::all();
    return view('admin.perfis', compact('perfis', 'permissoes'));
});

// Rotas de administração
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::middleware(['can:gerenciar-permissoes'])->group(function () {
        Route::get('/gerenciar-permissoes', function () {
            $users = \App\Models\User::with('profile')->get();
            $profiles = \App\Models\Profile::all();
            return view('admin.gerenciar-permissoes', compact('users', 'profiles'));
        })->name('gerenciar-permissoes');
    });
    
    // Rotas de conflito removidas
});

// APIs para gerenciamento de permissões
Route::middleware(['auth','throttle:60,1'])->prefix('api')->group(function () {
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

// API para usuários (usado no select do formulário) — protegido
Route::get('/api/usuarios', function() {
    return App\Models\User::select('id', 'name')->get();
})->middleware(['auth','can:gerenciar-usuarios','throttle:60,1'])->name('api.usuarios');

// API para obter dados de usuário — protegido
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
})->middleware(['auth','can:gerenciar-usuarios','throttle:60,1']);

/* Rota temporária para corrigir status
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
})->middleware('auth');*/

// Rota para atualizar diretamente o status
/*Route::get('/atualizar-status/{id}/{status}', function ($id, $status) {
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
})->middleware('auth');*/

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
})->middleware(['auth']);

// Nova rota para atualização de status via AJAX
Route::post('/rh/problemas/{id}/status', [App\Http\Controllers\RHController::class, 'updateStatus'])->name('rh.problema.status');

// Rotas de Debug para depuração
Route::get('/debug/enable-sql-log', function() {
    \DB::enableQueryLog();
    \Log::info('Logging SQL ativado via AJAX');
    return response()->json(['status' => 'success', 'message' => 'SQL logging enabled']);
})->middleware(['auth','throttle:30,1']);

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
})->middleware(['auth','throttle:30,1']);

// Rotas de debug de tarefas removidas

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
