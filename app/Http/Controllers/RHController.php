<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RHProblema;
use App\Models\RHAnotacao;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RHController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Exibe a lista de problemas de RH.
     */
    public function index()
    {
        $problemas = RHProblema::with(['usuario', 'respondente', 'anotacoes'])
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('rh.index', compact('problemas'));
    }

    /**
     * Exibe a lista de tarefas de RH atribuídas ao usuário logado.
     */
    public function tarefas()
    {
        try {
            \Log::info("Carregando todas as tarefas do sistema");
            
            // 1. Buscar todas as tarefas de RH
            $problemasRH = RHProblema::with(['usuario', 'respondente', 'anotacoes'])
                ->orderBy('created_at', 'desc')
                ->get();
                
            // 2. Buscar todas as tarefas do cronograma
            $tarefasCronograma = DB::table('cronogramas')
                ->orderBy('created_at', 'desc')
                ->get();
                
            // Transformar modelo de Cronograma para compatibilidade com modelo RHProblema
            $tarefasCronogramaFormatadas = collect();
            foreach($tarefasCronograma as $tarefa) {
                // Verificar se já existe nos problemas
                $jaExiste = $problemasRH->where('descricao', $tarefa->descricao)->first();
                
                if (!$jaExiste) {
                    // Buscar datas do cronograma para a tarefa
                    $datasCronograma = DB::table('cronograma_datas')
                        ->where('cronograma_id', $tarefa->id)
                        ->orderBy('data')
                        ->first();
                        
                    // Criar um objeto RHProblema temporário
                    $tarefaObj = new RHProblema();
                    $tarefaObj->id = 'C' . $tarefa->id; // Prefixo C para identificar do cronograma
                    $tarefaObj->descricao = $tarefa->descricao;
                    $tarefaObj->status = 'Pendente';
                    $tarefaObj->prioridade = $tarefa->status; // no cronograma, status armazena a prioridade
                    $tarefaObj->origem = 'cronograma';
                    $tarefaObj->created_at = $tarefa->created_at;
                    $tarefaObj->detalhes = json_encode(['origem' => 'cronograma', 'id_cronograma' => $tarefa->id]);
                    
                    // Buscar usuário responsável do cronograma
                    $usuarioCronograma = DB::table('cronograma_usuarios')
                        ->where('cronograma_id', $tarefa->id)
                        ->first();
                        
                    if ($usuarioCronograma) {
                        $tarefaObj->responsavel_id = $usuarioCronograma->usuario_id;
                    }
                    
                    // Se houver data, atribuir ao prazo_entrega
                    if ($datasCronograma && isset($datasCronograma->data)) {
                        try {
                            $tarefaObj->prazo_entrega = new \Carbon\Carbon($datasCronograma->data);
                        } catch (\Exception $e) {
                            $tarefaObj->prazo_entrega = null;
                        }
                    }
                    
                    $tarefasCronogramaFormatadas->push($tarefaObj);
                }
            }
            
            // 3. Mesclar as duas coleções
            $problemas = $problemasRH->merge($tarefasCronogramaFormatadas);
            
            // Ordenar por data de criação (mais recentes primeiro)
            $problemas = $problemas->sortByDesc('created_at');
            
            \Log::info("Encontradas " . $problemas->count() . " tarefas no total");
            
            // Transformar cada problema para garantir que campos JSON sejam strings
            foreach ($problemas as $problema) {
                if (is_array($problema->detalhes)) {
                    $problema->detalhes = json_encode($problema->detalhes);
                } elseif (is_null($problema->detalhes)) {
                    $problema->detalhes = '{}';
                }
                
                if (is_null($problema->resposta)) {
                    $problema->resposta = '';
                }
            }
            
            return view('rh.tarefas', compact('problemas'));
        } catch (\Exception $e) {
            \Log::error("Erro ao carregar tarefas: " . $e->getMessage());
            \Log::error("Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")");
            
            return view('rh.tarefas', [
                'problemas' => collect([]),
                'erro' => "Erro ao carregar tarefas: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Exibe o formulário para criar um novo problema.
     */
    public function create()
    {
        return view('rh.create');
    }

    /**
     * Armazena um novo problema no banco de dados.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'status' => 'required|string',
            'prioridade' => 'required|string',
            'detalhes' => 'nullable|string',
        ]);

        $problema = new RHProblema();
        $problema->descricao = $validated['descricao'];
        $problema->status = $validated['status'];
        $problema->prioridade = $validated['prioridade'];
        $problema->detalhes = $validated['detalhes'];
        $problema->usuario_id = Auth::id();
        $problema->usuario_nome = Auth::user()->name;
        $problema->horario = now();
        $problema->inicio_contagem = now();
        $problema->save();

        return redirect()->route('rh.index')
            ->with('success', 'Problema registrado com sucesso!');
    }

    /**
     * Exibe um problema específico.
     */
    public function show($id)
    {
        $problema = RHProblema::with(['usuario', 'respondente', 'anotacoes'])
            ->findOrFail($id);
            
        return view('rh.show', compact('problema'));
    }

    /**
     * Exibe o formulário para edição de um problema.
     */
    public function edit($id)
    {
        $problema = RHProblema::with(['usuario', 'respondente', 'anotacoes'])
            ->findOrFail($id);
            
        // Buscar as anotações relacionadas a este problema
        $anotacoes = RHAnotacao::where('problema_id', $id)
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->get();
            
        \Log::info("Editando problema ID {$id}: {$problema->descricao}");
        \Log::info("Carregadas " . $anotacoes->count() . " anotações para este problema");
            
        return view('rh.edit', compact('problema', 'anotacoes'));
    }

    /**
     * Método para forçar a atualização do status diretamente no banco e limpar o cache.
     */
    public function forceUpdateStatus($id, $status)
    {
        \Log::info("Forçando atualização de status do problema ID {$id} para '{$status}'");
        
        try {
            // Usar query builder para atualizar diretamente o banco sem passar pelo modelo
            $affected = DB::table('rh_problemas')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'updated_at' => now()
                ]);
                
            // Forçar o modelo a ser reinicializado
            $problema = RHProblema::findOrFail($id);
            $problema = $problema->fresh();
            
            \Log::info("Status após atualização forçada: '{$problema->status}' (Registros afetados: {$affected})");
            
            return $problema;
        } catch (\Exception $e) {
            \Log::error("Erro ao forçar atualização de status: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Atualiza um problema no banco de dados.
     */
    public function update(Request $request, $id)
    {
        // Log para debug
        \Log::info("Início do método update para o problema #{$id}");
        \Log::info("Dados recebidos: " . json_encode($request->all()));
        
        // Verificar se o usuário tem permissão para edição completa (RH)
        $permissaoCompleta = auth()->user()->can('RH');
        \Log::info("Usuário " . auth()->user()->name . " tem permissão completa: " . ($permissaoCompleta ? "Sim" : "Não"));
        
        // Regras de validação diferentes dependendo da permissão
        if ($permissaoCompleta) {
            // Validação dos dados para usuários com permissão completa
            $validated = $request->validate([
                'descricao' => 'sometimes|required|string|max:255',
                'detalhes' => 'nullable|string',
                'status' => 'sometimes|required|string|in:Pendente,Em andamento,Concluído,No prazo',
                'prioridade' => 'sometimes|required|string|in:baixa,media,alta',
                'prazo_entrega' => 'nullable|string',
                'resposta' => 'nullable|string',
            ]);
        } else {
            // Validação dos dados para usuários sem permissão completa (apenas anotações)
            $validated = $request->validate([
                'resposta' => 'nullable|string',
            ]);
        }
        
        \Log::info("Dados validados: " . json_encode($validated));
        
        try {
            // Buscar o problema existente
            $problema = RHProblema::findOrFail($id);
            \Log::info("Problema encontrado: #" . $problema->id);
            
            // Registrar valores originais
            $statusAnterior = $problema->status;
            $prazoAnterior = $problema->prazo_entrega;
            
            // Atualizar campos apenas se o usuário tiver permissão completa
            if ($permissaoCompleta) {
                if (isset($validated['descricao'])) {
                    $problema->descricao = $validated['descricao'];
                }
                
                if (isset($validated['status'])) {
                    $problema->status = $validated['status'];
                }
                
                if (isset($validated['prioridade'])) {
                    $problema->prioridade = $validated['prioridade'];
                }
                
                // Atualizar detalhes (pode ser vazio)
                if (array_key_exists('detalhes', $validated)) {
                    $problema->detalhes = $validated['detalhes'] ?: "";
                    \Log::info("Definindo detalhes como: " . ($validated['detalhes'] ?: "string vazia"));
                }
                
                // Processar a data de prazo_entrega do formato brasileiro para o formato SQL
                if (isset($validated['prazo_entrega'])) {
                    if (empty($validated['prazo_entrega'])) {
                        $problema->prazo_entrega = null;
                        \Log::info("Campo prazo_entrega vazio, definindo como null");
                    } else {
                        try {
                            // Sanitizar a entrada removendo caracteres invisíveis e espaços extras
                            $dataString = preg_replace('/[^\d\/: ]/', '', trim($validated['prazo_entrega']));
                            \Log::info("Data após sanitização inicial: {$dataString}");
                            
                            // Garantir que estamos trabalhando com o formato esperado: dd/mm/yyyy hh:mm
                            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/', $dataString, $matches)) {
                                $dia = (int)$matches[1];
                                $mes = (int)$matches[2];
                                $ano = (int)$matches[3];
                                $hora = (int)$matches[4];
                                $minuto = (int)$matches[5];
                                
                                \Log::info("Componentes de data extraídos: dia={$dia}, mes={$mes}, ano={$ano}, hora={$hora}, minuto={$minuto}");
                                
                                // Validar os componentes da data
                                if ($mes < 1 || $mes > 12 || $dia < 1 || $dia > 31 || $hora < 0 || $hora > 23 || $minuto < 0 || $minuto > 59) {
                                    throw new \Exception("Componentes de data inválidos");
                                }
                                
                                // Criar string de data diretamente no formato SQL (YYYY-MM-DD HH:MM:SS)
                                $dataSQL = sprintf('%04d-%02d-%02d %02d:%02d:00', $ano, $mes, $dia, $hora, $minuto);
                                \Log::info("Data formatada para SQL: {$dataSQL}");
                                
                                // Definir diretamente o valor no atributo (sem usar a função setAttribute)
                                $problema->attributes['prazo_entrega'] = $dataSQL;
                                \Log::info("Prazo entrega definido diretamente: " . $dataSQL);
                                
                            } else {
                                throw new \Exception("A data não corresponde ao formato esperado após sanitização: {$dataString}");
                            }
                        } catch (\Exception $e) {
                            \Log::error("Erro ao processar data: " . $e->getMessage());
                            
                            // Manter o valor anterior
                            \Log::info("Mantendo o valor anterior da data");
                        }
                    }
                }
            }
            
            // Adicionar nova anotação, se fornecida (permitido para todos os usuários)
            if (!empty($validated['resposta'])) {
                $anotacao = new RHAnotacao();
                $anotacao->problema_id = $problema->id;
                $anotacao->usuario_id = auth()->id();
                $anotacao->conteudo = $validated['resposta'];
                $anotacao->save();
                
                \Log::info("Nova anotação adicionada por " . auth()->user()->name);
                
                // Se status foi alterado e usuário tem permissão, registrar na anotação
                if ($permissaoCompleta && $statusAnterior != $problema->status) {
                    $anotacaoStatus = new RHAnotacao();
                    $anotacaoStatus->problema_id = $problema->id;
                    $anotacaoStatus->usuario_id = auth()->id();
                    $anotacaoStatus->conteudo = "Status alterado de '{$statusAnterior}' para '{$problema->status}'";
                    $anotacaoStatus->save();
                    
                    \Log::info("Anotação de mudança de status adicionada");
                }
            }
            
            // Log antes de salvar
            \Log::info("Valores do objeto antes de salvar: " . json_encode($problema->toArray()));
            
            // Log específico para o valor da data de prazo
            if (isset($problema->prazo_entrega)) {
                \Log::info("Valor final de prazo_entrega: " . var_export($problema->prazo_entrega, true));
            } else {
                \Log::info("prazo_entrega está null antes de salvar");
            }
            
            // Ativar log de consultas SQL para depuração
            \DB::enableQueryLog();
            
            // Salvar usando uma consulta direta para contornar problemas de conversão
            if (isset($validated['prazo_entrega']) && !empty($validated['prazo_entrega']) && isset($dataSQL)) {
                // Salvar outros campos primeiro
                $problema->timestamps = false; // Desabilitar timestamps temporariamente
                $problema->save();
                
                // Atualizar a data diretamente com query builder
                \DB::table('rh_problemas')
                    ->where('id', $problema->id)
                    ->update(['prazo_entrega' => $dataSQL]);
                
                \Log::info("Data atualizada via query builder: " . $dataSQL);
                
                // Recarregar o modelo para refletir as mudanças
                $problema = RHProblema::findOrFail($id);
            } else {
                // Salvar normalmente
                $resultado = $problema->save();
                
                if ($resultado) {
                    \Log::info("Save retornou true");
                } else {
                    \Log::warning("Save retornou false");
                }
            }
            
            // Log de consultas SQL
            $queries = \DB::getQueryLog();
            if (!empty($queries)) {
                \Log::info("Última consulta SQL: " . json_encode(end($queries)));
            }
            
            // Definir redirecionamento com base na origem
            $origem = $request->input('origem');
            $redirecionamento = route('rh.administrador'); // Padrão

            if ($origem === 'tarefas-por-usuarios') {
                $redirecionamento = route('rh.tarefas-por-usuarios');
            }

            // Retornar resposta baseada no tipo de requisição
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Problema atualizado com sucesso',
                    'redirect' => $redirecionamento
                ]);
            }

            return redirect($redirecionamento)
                ->with('success', 'Problema atualizado com sucesso!');
        } catch (\Exception $e) {
            \Log::error("Erro ao atualizar problema: " . $e->getMessage());
            \Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Erro ao atualizar o problema: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exibe a lista de tarefas agrupadas por usuários.
     */
    public function tarefasPorUsuarios()
    {
        try {
            \Log::info("Iniciando carregamento da página de tarefas por usuários");
            
            // Obter ID do usuário logado
            $userId = auth()->id();
            
            // Buscar apenas o usuário logado
            $usuarios = User::with(['responsavelProblemas' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->where('id', $userId)->get();
            
            // Preparar a estrutura de usuários com tarefas
            $usuariosComTarefas = [];
            
            foreach ($usuarios as $usuario) {
                // Verificar se o usuário tem problemas atribuídos
                $problemas = $usuario->responsavelProblemas;
                
                // Buscar tarefas do cronograma atribuídas ao usuário
                $tarefasCronograma = DB::table('cronograma_usuarios')
                    ->where('usuario_id', $userId)
                    ->join('cronogramas', 'cronograma_usuarios.cronograma_id', '=', 'cronogramas.id')
                    ->select('cronogramas.*')
                    ->get();
                
                // Converter tarefas do cronograma para o formato de problemas
                foreach ($tarefasCronograma as $tarefa) {
                    $tarefaObj = new RHProblema();
                    $tarefaObj->id = 'C' . $tarefa->id;
                    $tarefaObj->descricao = $tarefa->descricao;
                    $tarefaObj->status = 'Pendente';
                    $tarefaObj->prioridade = $tarefa->status;
                    $tarefaObj->responsavel_id = $userId;
                    $tarefaObj->created_at = $tarefa->created_at;
                    $problemas->push($tarefaObj);
                }
                
                // Se não tiver problemas, o array estará vazio, mas não nulo
                $totalTarefas = $problemas->count();
                
                // Só adicionar usuários que têm ou tiveram tarefas
                if ($totalTarefas > 0) {
                    // Contar tarefas por status
                    $pendentes = $problemas->where('status', 'Pendente')->count();
                    $emAndamento = $problemas->where('status', 'Em andamento')->count();
                    $concluidas = $problemas->where('status', 'Concluído')->count();
                    
                    // Calcular quantas estão no prazo
                    $noPrazo = $pendentes + $emAndamento;
                    
                    // Calcular taxa de conclusão
                    $taxaConclusao = $totalTarefas > 0 ? round(($concluidas / $totalTarefas) * 100) : 0;
                    
                    $usuariosComTarefas[] = [
                        'id' => $usuario->id,
                        'nome' => $usuario->name,
                        'total_tarefas' => $totalTarefas,
                        'pendentes' => $pendentes,
                        'em_andamento' => $emAndamento,
                        'no_prazo' => $noPrazo,
                        'concluidas' => $concluidas,
                        'taxa_conclusao' => $taxaConclusao
                    ];
                }
            }
            
            // Buscar tarefas ativas (incluindo do cronograma)
            $tarefasAtivas = RHProblema::where(function($query) {
                    $query->where('status', 'Pendente')
                          ->orWhere('status', 'Em andamento')
                          ->orWhere(function($q) {
                              $q->where('status', 'Concluído')
                                ->where('finalizado_em', '>=', now()->subDays(7));
                          });
                })
                ->where(function($query) use ($userId) {
                    $query->where('responsavel_id', $userId);
                })
                ->with(['usuario', 'responsavel', 'respondente'])
                ->orderBy('status', 'asc')
                ->orderBy('updated_at', 'desc')
                ->get();
                
            // Adicionar tarefas do cronograma à lista de tarefas ativas
            $tarefasCronogramaAtivas = DB::table('cronograma_usuarios')
                ->where('usuario_id', $userId)
                ->join('cronogramas', 'cronograma_usuarios.cronograma_id', '=', 'cronogramas.id')
                ->select('cronogramas.*')
                ->get();
                
            foreach ($tarefasCronogramaAtivas as $tarefa) {
                $tarefaObj = new RHProblema();
                $tarefaObj->id = 'C' . $tarefa->id;
                $tarefaObj->descricao = $tarefa->descricao;
                $tarefaObj->status = 'Pendente';
                $tarefaObj->prioridade = $tarefa->status;
                $tarefaObj->responsavel_id = $userId;
                $tarefaObj->created_at = $tarefa->created_at;
                $tarefasAtivas->push($tarefaObj);
            }
            
            \Log::info("Carregados " . count($usuariosComTarefas) . " usuários com tarefas e " . 
                      $tarefasAtivas->count() . " tarefas ativas");
            
            return view('rh.tarefas-por-usuarios', compact('usuariosComTarefas', 'tarefasAtivas'));
        } catch (\Exception $e) {
            \Log::error("Erro ao carregar tarefas por usuários: " . $e->getMessage());
            \Log::error("Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")");
            
            return view('rh.tarefas-por-usuarios', [
                'usuariosComTarefas' => [],
                'tarefasAtivas' => collect([]),
                'erro' => "Erro ao carregar dados: " . $e->getMessage()
            ]);
        }
    }

    /**
     * Inicia o trabalho em um problema.
     */
    public function iniciar(Request $request, $problema)
    {
        $problema = RHProblema::findOrFail($problema);
        
        // Registrar que este usuário está trabalhando no problema
        $problema->responsavel_id = Auth::id();
        $problema->status = 'Em andamento';
        $problema->inicio_contagem = Carbon::now();
        $problema->save();
        
        // Adicionar uma anotação automática
        $anotacao = new RHAnotacao();
        $anotacao->problema_id = $problema->id;
        $anotacao->usuario_id = Auth::id();
        $anotacao->conteudo = "Tarefa iniciada por " . Auth::user()->name;
        $anotacao->save();
        
        return redirect()->back()
            ->with('success', 'Tarefa iniciada com sucesso!');
    }

    /**
     * Marca um problema como concluído.
     */
    public function concluir(Request $request, $problema)
    {
        $problema = RHProblema::findOrFail($problema);
        
        // Validar os dados
        $validated = $request->validate([
            'resolucao' => 'required|string'
        ]);
        
        // Atualizar status e adicionar resolução
        $problema->status = 'Concluído';
        $problema->resposta = $validated['resolucao'];
        $problema->respondido_por = Auth::id();
        $problema->data_resposta = Carbon::now();
        $problema->finalizado_em = Carbon::now();
        $problema->save();
        
        // Adicionar uma anotação
        $anotacao = new RHAnotacao();
        $anotacao->problema_id = $problema->id;
        $anotacao->usuario_id = Auth::id();
        $anotacao->conteudo = "Tarefa concluída por " . Auth::user()->name;
        $anotacao->save();
        
        return redirect()->back()
            ->with('success', 'Tarefa concluída com sucesso!');
    }

    /**
     * Retorna as anotações de um problema.
     */
    public function getAnotacoes($problema)
    {
        $anotacoes = RHAnotacao::where('problema_id', $problema)
            ->with('usuario')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json([
            'success' => true,
            'data' => $anotacoes
        ]);
    }

    /**
     * Atualiza apenas o status de um problema.
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            // Validação do status
            $validated = $request->validate([
                'status' => 'required|string|in:Pendente,Em andamento,Concluído,No prazo',
            ]);
            
            \Log::info("Atualizando apenas o status do problema ID {$id} para '{$validated['status']}'");
            
            // Buscar problema para obter status atual
            $problema = RHProblema::findOrFail($id);
            $statusAnterior = $problema->status;
            
            // Usar o método de atualização forçada
            $problemaAtualizado = $this->forceUpdateStatus($id, $validated['status']);
            
            // Verificar se realmente foi atualizado
            if ($problemaAtualizado->status === $validated['status']) {
                \Log::info("Status atualizado com sucesso de '{$statusAnterior}' para '{$validated['status']}'");
            } else {
                \Log::warning("Possível problema de sincronização - Status após atualização: '{$problemaAtualizado->status}'");
            }
            
            // Retornar resposta baseada no tipo de requisição
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status atualizado com sucesso!',
                    'status_anterior' => $statusAnterior,
                    'status_atual' => $validated['status'],
                    'problema_id' => $id,
                    'refresh' => true // Sinalizar que a página deve ser recarregada
                ]);
            }
            
            return redirect()->back()
                ->with('success', 'Status atualizado com sucesso!');
        } catch (\Exception $e) {
            \Log::error("Erro ao atualizar status: " . $e->getMessage());
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar status: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erro ao atualizar status: ' . $e->getMessage());
        }
    }

    /**
     * Remove um problema específico do banco de dados.
     */
    public function destroy($id)
    {
        try {
            $problema = RHProblema::findOrFail($id);
            
            // Primeiro remover anotações relacionadas
            RHAnotacao::where('problema_id', $id)->delete();
            
            // Depois remover o problema
            $problema->delete();
            
            // Verificar se é uma requisição AJAX
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Problema removido com sucesso!'
                ]);
            }
            
            // Se não for AJAX, redirecionar para a página de listagem
            return redirect()->route('rh.administrador')
                ->with('success', 'Problema removido com sucesso!');
        } catch (\Exception $e) {
            \Log::error("Erro ao excluir problema: " . $e->getMessage());
            
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao excluir problema: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->back()
                ->with('error', 'Erro ao excluir problema: ' . $e->getMessage());
        }
    }
} 