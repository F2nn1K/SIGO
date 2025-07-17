<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\RHProblema;
use App\Models\Cronograma;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Models\CronogramaData;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CronogramaController extends Controller
{
    /**
     * Construtor do controller
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:Cronograma');
    }
    
    /**
     * Método para verificar e criar a estrutura de tabelas se necessário
     */
    private function verificarEstrutura()
    {
        // Todas as tabelas já existem no banco de dados
        Log::info('Verificação de estrutura de tabelas não é necessária - tabelas já existem.');
    }

    /**
     * Exibe a página principal do cronograma
     */
    public function index()
    {
        return view('rh.cronograma.index');
    }

    /**
     * Retorna os eventos do cronograma para carregar na tabela
     */
    public function eventos()
    {
        try {
            Log::info("Iniciando requisição de eventos do cronograma - versão simplificada");
            
            // Buscar problemas com o eloquent para ter acesso ao campo detalhes
            $problemas = RHProblema::with(['usuario', 'responsavel'])->orderBy('id', 'desc')->get();
            
            Log::info("Encontrados {$problemas->count()} problemas na tabela rh_problemas");
            
            // Formatar dados de acordo com o esperado pelo frontend
            $tarefasFormatadas = [];
            
            foreach ($problemas as $problema) {
                Log::info("Processando problema ID {$problema->id}: {$problema->descricao}");
                
                // Lista de usuários
                $usuariosNomes = [];
                $usuariosIds = [];
                $usuariosRegistrados = [];
                
                // Adicionar usuário criador
                if ($problema->usuario_id && $problema->usuario) {
                    $usuariosNomes[] = $problema->usuario->name;
                    $usuariosIds[] = $problema->usuario_id;
                    $usuariosRegistrados[$problema->usuario_id] = true;
                    Log::info("Adicionado usuario_id: {$problema->usuario_id} ({$problema->usuario->name})");
                }
                
                // Adicionar responsável se diferente do criador
                if ($problema->responsavel_id && $problema->responsavel && 
                    (!$problema->usuario_id || $problema->usuario_id != $problema->responsavel_id)) {
                    $usuariosNomes[] = $problema->responsavel->name;
                    $usuariosIds[] = $problema->responsavel_id;
                    $usuariosRegistrados[$problema->responsavel_id] = true;
                    Log::info("Adicionado responsavel_id: {$problema->responsavel_id} ({$problema->responsavel->name})");
                }
                
                // Buscar usuários adicionais no campo detalhes
                $detalhes = [];
                if (is_string($problema->detalhes)) {
                    $detalhes = json_decode($problema->detalhes, true) ?: [];
                } elseif (is_array($problema->detalhes)) {
                    $detalhes = $problema->detalhes;
                }
                
                // Verificar se existem usuários adicionais no campo detalhes
                if (isset($detalhes['usuarios_cronograma']) && is_array($detalhes['usuarios_cronograma'])) {
                    // Limpar usuariosIds para usar apenas os do campo detalhes, que já contém todos os necessários
                    $usuariosIds = [];
                    $usuariosNomes = [];
                    $usuariosRegistrados = [];
                    
                    // Adicionar todos os usuários do campo detalhes
                    foreach ($detalhes['usuarios_cronograma'] as $userId) {
                        // Evitar duplicatas
                        if (!isset($usuariosRegistrados[$userId])) {
                            $usuariosIds[] = $userId;
                            $usuariosRegistrados[$userId] = true;
                            
                            // Buscar o nome do usuário
                            $user = \App\Models\User::find($userId);
                            if ($user) {
                                $usuariosNomes[] = $user->name;
                                Log::info("Adicionado usuário do campo detalhes: {$userId} ({$user->name})");
                            } else {
                                Log::warning("Usuário ID {$userId} não encontrado");
                            }
                        }
                    }
                }
                
                // Normalizar a prioridade
                $prioridade = 'media'; // valor padrão
                if ($problema->prioridade) {
                    $prioridadeLower = strtolower($problema->prioridade);
                    if (strpos($prioridadeLower, 'alta') !== false) {
                        $prioridade = 'alta';
                    } else if (strpos($prioridadeLower, 'baixa') !== false) {
                        $prioridade = 'baixa';
                    }
                }
                
                // Data
                $proximaData = null;
                if ($problema->prazo_entrega) {
                    $proximaData = $problema->prazo_entrega->format('Y-m-d');
                }
                
                // Montar tarefa formatada
                $tarefasFormatadas[] = [
                    'id' => $problema->id,
                    'descricao' => $problema->descricao,
                    'status' => $prioridade,
                    'prioridade' => $prioridade,
                    'usuarios_nomes' => count($usuariosNomes) > 0 ? implode(', ', $usuariosNomes) : 'Não atribuído',
                    'usuarios_ids' => $usuariosIds,
                    'proxima_data' => $proximaData,
                    'criado_em' => $problema->created_at ? $problema->created_at->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
                ];
                
                Log::info("Problema {$problema->id} formatado com " . count($usuariosIds) . " usuários: " . json_encode($usuariosIds));
            }
            
            return response()->json([
                'success' => true,
                'data' => $tarefasFormatadas
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar eventos: " . $e->getMessage());
            Log::error("Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")");
            return response()->json([
                'success' => false,
                'error' => 'Erro ao buscar eventos: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Sincroniza o cronograma com os problemas do RH
     * Agora só garante que o campo detalhes esteja corretamente configurado
     */
    public function sincronizar()
    {
        try {
            // Buscar tarefas diretamente da tabela rh_problemas
            $problemas = RHProblema::all();
            
            Log::info("Sincronizando {$problemas->count()} problemas do RH com o novo formato de dados");
            
            $problemasAtualizados = 0;
            
            foreach ($problemas as $problema) {
                $atualizado = false;
                $detalhes = json_decode($problema->detalhes, true) ?? [];
                
                // Verificar se há dados de cronograma no campo detalhes
                if (!isset($detalhes['usuarios_cronograma']) && ($problema->usuario_id || $problema->responsavel_id)) {
                    // Inicializar array de usuários
                    $detalhes['usuarios_cronograma'] = [];
                    
                    // Adicionar usuário principal se disponível
                    if ($problema->usuario_id) {
                        $detalhes['usuarios_cronograma'][] = $problema->usuario_id;
                    }
                    
                    // Adicionar responsável se disponível e diferente do usuário
                    if ($problema->responsavel_id && $problema->responsavel_id != $problema->usuario_id) {
                        $detalhes['usuarios_cronograma'][] = $problema->responsavel_id;
                    }
                    
                    Log::info("Adicionados usuários para problema ID {$problema->id}: " . 
                        json_encode($detalhes['usuarios_cronograma']));
                    $atualizado = true;
                }
                
                // Adicionar data do prazo no campo detalhes se disponível
                if (!isset($detalhes['datas_cronograma']) && $problema->prazo_entrega) {
                    $data = $problema->prazo_entrega->format('Y-m-d');
                    $mes = $problema->prazo_entrega->month;
                    
                    $detalhes['datas_cronograma'] = [];
                    $detalhes['datas_cronograma'][$mes] = $data;
                    
                    Log::info("Adicionada data de prazo para problema ID {$problema->id}: " . 
                        json_encode($detalhes['datas_cronograma']));
                    $atualizado = true;
                }
                
                // Salvar se houve alterações
                if ($atualizado) {
                    $problema->detalhes = json_encode($detalhes);
                    $problema->save();
                    $problemasAtualizados++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Sincronização concluída. {$problemasAtualizados} problemas atualizados de {$problemas->count()} total."
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao sincronizar: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => "Erro ao sincronizar: " . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Atualiza uma tarefa
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info("Atualizando tarefa ID {$id}. Dados recebidos: " . json_encode($request->all()));
            
            $tarefa = RHProblema::findOrFail($id);
            
            // Validar dados
            $request->validate([
                'status' => 'sometimes|string|in:baixa,media,alta',
                'usuario_id' => 'sometimes|array',
                'usuario_id.*' => 'integer|exists:users,id',
                'data_janeiro' => 'nullable|date_format:d/m/Y',
                'data_fevereiro' => 'nullable|date_format:d/m/Y',
                'data_marco' => 'nullable|date_format:d/m/Y',
                'data_abril' => 'nullable|date_format:d/m/Y',
                'data_maio' => 'nullable|date_format:d/m/Y',
                'data_junho' => 'nullable|date_format:d/m/Y',
                'data_julho' => 'nullable|date_format:d/m/Y',
                'data_agosto' => 'nullable|date_format:d/m/Y',
                'data_setembro' => 'nullable|date_format:d/m/Y',
                'data_outubro' => 'nullable|date_format:d/m/Y',
                'data_novembro' => 'nullable|date_format:d/m/Y',
                'data_dezembro' => 'nullable|date_format:d/m/Y',
            ]);
            
            // Atualizar a prioridade
            if ($request->has('status')) {
                $tarefa->prioridade = $request->status;
                Log::info("Atualizando prioridade para: " . $request->status);
            }
            
            // Atualizar prazo se alguma data for informada
            $meses = [
                1 => 'data_janeiro',
                2 => 'data_fevereiro',
                3 => 'data_marco',
                4 => 'data_abril',
                5 => 'data_maio',
                6 => 'data_junho',
                7 => 'data_julho',
                8 => 'data_agosto',
                9 => 'data_setembro',
                10 => 'data_outubro',
                11 => 'data_novembro',
                12 => 'data_dezembro',
            ];
            
            $datas = [];
            $proximaData = null;
            
            foreach ($meses as $mes => $campo) {
                if ($request->filled($campo)) {
                    $data = $request->input($campo);
                    $datas[$mes] = $data;
                    
                    try {
                        $dataObj = Carbon::createFromFormat('d/m/Y', $data);
                        if (!isset($proximaData) || $dataObj->lt($proximaData)) {
                            $proximaData = $dataObj;
                        }
                    } catch (\Exception $e) {
                        Log::warning("Erro ao converter data {$data}: " . $e->getMessage());
                    }
                }
            }
            
            // Atualizar prazo de entrega diretamente na tabela rh_problemas
            if (isset($proximaData)) {
                $tarefa->prazo_entrega = $proximaData;
                Log::info("Atualizando prazo para: " . $proximaData->format('Y-m-d'));
            }
            
            // Armazenar as datas em um campo JSON na tabela principal
            $todasDatas = [];
            foreach ($datas as $mes => $data) {
                if (!empty($data)) {
                    try {
                        $dataObj = Carbon::createFromFormat('d/m/Y', $data);
                        $todasDatas[$mes] = $dataObj->format('Y-m-d');
                    } catch (\Exception $e) {
                        Log::warning("Erro ao converter data {$data}: " . $e->getMessage());
                    }
                }
            }
            
            // Armazenar as datas como um campo JSON nos dados do problema
            if (!empty($todasDatas)) {
                // Garantir que detalhes seja um array
                $detalhesArray = [];
                
                if (is_string($tarefa->detalhes)) {
                    $detalhesArray = json_decode($tarefa->detalhes, true) ?: [];
                } elseif (is_array($tarefa->detalhes)) {
                    $detalhesArray = $tarefa->detalhes;
                }
                
                // Adicionar as datas ao array
                $detalhesArray['datas_cronograma'] = $todasDatas;
                
                // Converter para JSON
                $tarefa->detalhes = json_encode($detalhesArray);
                
                Log::info("Armazenando datas no campo detalhes: " . json_encode($todasDatas));
            }
            
            // Atualizar usuários - tratamento mais robusto
            // Garantir que detalhes seja um array
            $detalhesArray = [];
            if (is_string($tarefa->detalhes)) {
                $detalhesArray = json_decode($tarefa->detalhes, true) ?: [];
            } elseif (is_array($tarefa->detalhes)) {
                $detalhesArray = $tarefa->detalhes;
            }
            
            // Verificar se há usuários no request
            if ($request->has('usuario_id')) {
                $usuariosIds = $request->usuario_id;
                Log::info("IDs de usuários recebidos: " . json_encode($usuariosIds));
                
                // Se tiver usuários, atualizar
                if (is_array($usuariosIds) && count($usuariosIds) > 0) {
                    // O primeiro usuário será o responsável principal
                    $tarefa->responsavel_id = $usuariosIds[0];
                    Log::info("Definido responsável principal: {$usuariosIds[0]}");
                    
                    // Atualizar o array de usuários
                    $detalhesArray['usuarios_cronograma'] = $usuariosIds;
                    
                    Log::info("Armazenando " . count($usuariosIds) . " usuários no campo detalhes");
                } else {
                    // Se não houver usuários selecionados, limpar
                    Log::info("Removendo todos os usuários da tarefa");
                    $tarefa->responsavel_id = null;
                    
                    // Remover a chave de usuários
                    if (isset($detalhesArray['usuarios_cronograma'])) {
                        unset($detalhesArray['usuarios_cronograma']);
                        Log::info("Chave 'usuarios_cronograma' removida do campo detalhes");
                    }
                }
            } else if ($request->has('usuarios_vazio') && $request->usuarios_vazio === 'true') {
                // Flag explícita indicando que usuários foram limpos
                Log::info("Flag 'usuarios_vazio' recebida - limpando usuários");
                $tarefa->responsavel_id = null;
                
                // Remover a chave de usuários
                if (isset($detalhesArray['usuarios_cronograma'])) {
                    unset($detalhesArray['usuarios_cronograma']);
                    Log::info("Chave 'usuarios_cronograma' removida do campo detalhes");
                }
            }
            
            // Converter de volta para JSON
            $tarefa->detalhes = json_encode($detalhesArray);
            Log::info("Campo detalhes final: " . $tarefa->detalhes);
            
            $tarefa->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Tarefa atualizada com sucesso.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Erro de validação: " . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Erro ao atualizar tarefa: " . $e->getMessage());
            Log::error("Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")");
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Armazena uma nova tarefa
     */
    public function store(Request $request)
    {
        try {
            // Validar dados
            $request->validate([
                'nova_tarefa' => 'required|string|max:255',
                'status' => 'required|string|in:baixa,media,alta',
                'novo_usuario_id' => 'sometimes|array',
                'novo_usuario_id.*' => 'integer|exists:users,id',
                // Validar também datas opcionais
                'data_janeiro' => 'nullable|date_format:d/m/Y',
                'data_fevereiro' => 'nullable|date_format:d/m/Y',
                'data_marco' => 'nullable|date_format:d/m/Y',
                'data_abril' => 'nullable|date_format:d/m/Y',
                'data_maio' => 'nullable|date_format:d/m/Y',
                'data_junho' => 'nullable|date_format:d/m/Y',
                'data_julho' => 'nullable|date_format:d/m/Y',
                'data_agosto' => 'nullable|date_format:d/m/Y',
                'data_setembro' => 'nullable|date_format:d/m/Y',
                'data_outubro' => 'nullable|date_format:d/m/Y',
                'data_novembro' => 'nullable|date_format:d/m/Y',
                'data_dezembro' => 'nullable|date_format:d/m/Y',
            ]);
            
            // Criar nova tarefa
            $tarefa = new RHProblema();
            $tarefa->descricao = $request->nova_tarefa;
            $tarefa->prioridade = $request->status;
            $tarefa->usuario_id = Auth::id();
            
            // Detalhes JSON para armazenar metadados
            $detalhes = [];
            
            // Processar datas, similar ao método update
            $meses = [
                1 => 'data_janeiro',
                2 => 'data_fevereiro',
                3 => 'data_marco',
                4 => 'data_abril',
                5 => 'data_maio',
                6 => 'data_junho',
                7 => 'data_julho',
                8 => 'data_agosto',
                9 => 'data_setembro',
                10 => 'data_outubro',
                11 => 'data_novembro',
                12 => 'data_dezembro',
            ];
            
            $todasDatas = [];
            $proximaData = null;
            
            foreach ($meses as $mes => $campo) {
                if ($request->filled($campo)) {
                    $data = $request->input($campo);
                    
                    try {
                        $dataObj = Carbon::createFromFormat('d/m/Y', $data);
                        $todasDatas[$mes] = $dataObj->format('Y-m-d');
                        
                        if (!isset($proximaData) || $dataObj->lt($proximaData)) {
                            $proximaData = $dataObj;
                        }
                    } catch (\Exception $e) {
                        Log::warning("Erro ao converter data {$data}: " . $e->getMessage());
                    }
                }
            }
            
            // Definir prazo de entrega se alguma data foi informada
            if (isset($proximaData)) {
                $tarefa->prazo_entrega = $proximaData;
                Log::info("Definindo prazo para: " . $proximaData->format('Y-m-d'));
            }
            
            // Adicionar datas ao array de detalhes
            if (!empty($todasDatas)) {
                $detalhes['datas_cronograma'] = $todasDatas;
                Log::info("Armazenando datas no campo detalhes: " . json_encode($todasDatas));
            }
            
            // Definir responsável se informado
            if ($request->has('novo_usuario_id') && is_array($request->novo_usuario_id) && count($request->novo_usuario_id) > 0) {
                $tarefa->responsavel_id = $request->novo_usuario_id[0];
                
                // Armazenar todos os usuários no campo detalhes
                $detalhes['usuarios_cronograma'] = $request->novo_usuario_id;
                Log::info("Armazenando usuários no campo detalhes: " . json_encode($request->novo_usuario_id));
            }
            
            // Se tiver detalhes, adicionar ao campo
            if (!empty($detalhes)) {
                $tarefa->detalhes = json_encode($detalhes);
            }
            
            $tarefa->save();
            
            Log::info("Nova tarefa criada: {$tarefa->id} - {$tarefa->descricao}");
            
            return response()->json([
                'success' => true,
                'message' => 'Tarefa criada com sucesso.',
                'id' => $tarefa->id
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error("Erro de validação: " . json_encode($e->errors()));
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error("Erro ao criar tarefa: " . $e->getMessage());
            Log::error("Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")");
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Remove uma tarefa
     */
    public function destroy($id)
    {
        try {
            // Remover a tarefa
            $tarefa = RHProblema::findOrFail($id);
            $tarefa->delete();
            
            Log::info('Tarefa ID ' . $id . ' excluída com sucesso');
            
            return response()->json([
                'success' => true,
                'message' => 'Tarefa excluída com sucesso'
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao excluir tarefa: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'error' => 'Erro ao excluir tarefa: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Força a migração de usuários antigos
     */
    public function migrarUsuarios()
    {
        try {
            $count = Cronograma::migrarUsuariosAntigos();
            
            if ($count >= 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Migração de usuários concluída. {$count} usuários migrados."
                ]);
            } else {
                return response()->json([
                    'error' => 'Erro na migração de usuários'
                ], 500);
            }
        } catch (\Exception $e) {
            Log::error("Erro na migração de usuários: " . $e->getMessage());
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca os problemas do RH para sincronização
     */
    private function getProblemasRH()
    {
        try {
            Log::info("Iniciando busca de problemas no RH para sincronização");
            
            // Busca direta SQL para garantir que todos os registros sejam recuperados
            $problemas = DB::select("SELECT * FROM rh_problemas");
            
            if (!$problemas || count($problemas) == 0) {
                Log::warning("SQL direto não retornou resultados da tabela rh_problemas");
                
                // Verificar quantos registros existem na tabela
                $totalRegistros = DB::selectOne("SELECT COUNT(*) as total FROM rh_problemas");
                
                if ($totalRegistros && $totalRegistros->total > 0) {
                    Log::warning("A tabela tem {$totalRegistros->total} registros, tentando consulta alternativa");
                    
                    // Tentar consulta alternativa mais simples
                    $problemas = DB::select("SELECT id, descricao, prioridade, status, usuario_id, responsavel_id FROM rh_problemas");
                    
                    if (count($problemas) == 0) {
                        Log::error("Consulta alternativa também não retornou resultados!");
                    } else {
                        Log::info("Consulta alternativa recuperou " . count($problemas) . " problemas");
                    }
                } else {
                    Log::warning("A tabela rh_problemas realmente está vazia");
                }
            } else {
                Log::info("Recuperados " . count($problemas) . " problemas via SQL direto");
            }
            
            return collect($problemas);
        } catch (\Exception $e) {
            Log::error("Erro ao buscar problemas do RH: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return collect();
        }
    }

    /**
     * Carregar datas de uma tarefa
     */
    public function carregarDatas($id)
    {
        try {
            Log::info("Iniciando carregamento de datas para tarefa ID {$id}");
            
            // Buscar a tarefa para obter os dados do campo detalhes
            $tarefa = RHProblema::findOrFail($id);
            
            // Verificar se detalhes é string ou array
            $detalhes = [];
            if (is_string($tarefa->detalhes)) {
                $detalhes = json_decode($tarefa->detalhes, true) ?: [];
            } elseif (is_array($tarefa->detalhes)) {
                $detalhes = $tarefa->detalhes;
            }
            
            // Obter as datas do campo detalhes
            $datasArmazenadas = $detalhes['datas_cronograma'] ?? [];
            
            Log::info("Carregadas datas do campo detalhes para tarefa {$id}: " . json_encode($datasArmazenadas));
            
            $meses = [
                1 => 'janeiro',
                2 => 'fevereiro',
                3 => 'marco',
                4 => 'abril',
                5 => 'maio',
                6 => 'junho',
                7 => 'julho',
                8 => 'agosto',
                9 => 'setembro',
                10 => 'outubro',
                11 => 'novembro',
                12 => 'dezembro',
            ];
            
            $response = [];
            
            // Formatar as datas para o frontend
            foreach ($datasArmazenadas as $mes => $dataSQL) {
                if (isset($meses[$mes])) {
                    $campo = 'data_' . $meses[$mes];
                    try {
                        $dataFormatada = Carbon::parse($dataSQL)->format('d/m/Y');
                        $response[$campo] = $dataFormatada;
                        Log::info("Data formatada para campo {$campo}: {$dataFormatada}");
                    } catch (\Exception $e) {
                        Log::warning("Erro ao formatar data {$dataSQL} para o mês {$mes}: " . $e->getMessage());
                    }
                }
            }
            
            // Se não houver dados no campo detalhes, verificar o prazo de entrega
            if (empty($response) && $tarefa->prazo_entrega) {
                $mes = (int)$tarefa->prazo_entrega->format('m');
                if (isset($meses[$mes])) {
                    $campo = 'data_' . $meses[$mes];
                    $response[$campo] = $tarefa->prazo_entrega->format('d/m/Y');
                    Log::info("Usando prazo de entrega como data para o mês {$mes}: {$response[$campo]}");
                }
            }
            
            Log::info("Resposta final do carregarDatas: " . json_encode($response));
            
            return response()->json([
                'success' => true,
                'data' => $response
            ]);
        } catch (\Exception $e) {
            Log::error("Erro ao carregar datas: " . $e->getMessage());
            Log::error("Arquivo: " . $e->getFile() . " (Linha " . $e->getLine() . ")");
            Log::error("Stack trace: " . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'error' => 'Erro ao carregar datas: ' . $e->getMessage()
            ], 500);
        }
    }
} 