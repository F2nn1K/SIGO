<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estoque;
use App\Models\Funcionario;
use App\Models\Baixa;
use App\Models\CentroCusto;
use App\Models\LogEstoque;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ControleEstoqueController extends Controller
{
    /**
     * Registra log de movimentação de estoque.
     */
    protected function registrarLogEstoque(Estoque $produto, string $tipo, int $quantidadeAnterior, int $quantidadeAlterada, int $quantidadeNova, ?string $origem = null, ?string $observacao = null): void
    {
        try {
            LogEstoque::create([
                'produto_id' => $produto->id,
                'user_id' => Auth::id(),
                'tipo' => $tipo,
                'quantidade_anterior' => $quantidadeAnterior,
                'quantidade_alterada' => $quantidadeAlterada,
                'quantidade_nova' => $quantidadeNova,
                'origem' => $origem,
                'observacao' => $observacao,
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Falha ao registrar log de estoque', [
                'produto_id' => $produto->id,
                'tipo' => $tipo,
                'erro' => $e->getMessage(),
            ]);
        }
    }

    public function index()
    {
        // Calcular estatísticas para os cards
        $totalProdutos = Estoque::count(); // Total de produtos cadastrados
        
        // Entradas e saídas do mês atual
        $mesAtual = now()->format('Y-m');
        $entradasMes = LogEstoque::where('tipo', 'entrada')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('quantidade_alterada');
        $saidasMes = Baixa::whereYear('data_baixa', now()->year)
                          ->whereMonth('data_baixa', now()->month)
                          ->sum('quantidade');
        
        // Produtos em falta (quantidade = 0)
        $produtosFalta = Estoque::where('quantidade', '=', 0)->count();
        
        // Dados para a página
        return view('brs.controle-estoque', compact(
            'totalProdutos',
            'entradasMes', 
            'saidasMes',
            'produtosFalta'
        ));
    }
    
    public function verificarPrazoFardamento(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'produto_id' => 'required|exists:estoque,id'
        ]);
        try {
            $funcionarioId = (int) $request->funcionario_id;
            $produtoId = (int) $request->produto_id;

            // Regra: considerar produtos cujo nome contenha "fard" (fardamento) ou descrição semelhante
            $produto = Estoque::findOrFail($produtoId);
            $ehFardamento = false;
            $nome = mb_strtolower($produto->nome ?? '');
            $descricao = mb_strtolower($produto->descricao ?? '');
            if (str_contains($nome, 'fard') || str_contains($descricao, 'fard')) {
                $ehFardamento = true;
            }

            if (!$ehFardamento) {
                return response()->json([
                    'success' => true,
                    'alertar' => false,
                    'mensagem' => null
                ]);
            }

            // Buscar a última baixa desse produto para o funcionário
            $ultimaBaixa = Baixa::where('funcionario_id', $funcionarioId)
                ->where('produto_id', $produtoId)
                ->orderBy('data_baixa', 'desc')
                ->first();

            if (!$ultimaBaixa) {
                return response()->json([
                    'success' => true,
                    'alertar' => false,
                    'mensagem' => null
                ]);
            }

            $limite = now()->subMonths(6);
            if ($ultimaBaixa->data_baixa && $ultimaBaixa->data_baixa->greaterThan($limite)) {
                $diasRestantes = $ultimaBaixa->data_baixa->diffInDays($limite, false) * -1; // negativo não interessa
                $dataPermitida = $ultimaBaixa->data_baixa->copy()->addMonths(6)->format('d/m/Y');
                return response()->json([
                    'success' => true,
                    'alertar' => true,
                    'mensagem' => "Este funcionário retirou este fardamento em " . $ultimaBaixa->data_baixa->format('d/m/Y') . ". O próximo está permitido a partir de " . $dataPermitida . ". Deseja continuar mesmo assim?"
                ]);
            }

            return response()->json([
                'success' => true,
                'alertar' => false,
                'mensagem' => null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar prazo: ' . $e->getMessage()
            ], 400);
        }
    }

    public function verificarFardamentoFuncionario(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id'
        ]);
        try {
            $funcionarioId = (int) $request->funcionario_id;

            // Buscar todas as retiradas (EPIs) do mês corrente para este funcionário
            $inicioMes = now()->startOfMonth();
            $fimMes = now()->endOfMonth();

            $registros = Baixa::with('produto')
                ->where('funcionario_id', $funcionarioId)
                ->whereBetween('data_baixa', [$inicioMes, $fimMes])
                ->orderBy('data_baixa', 'desc')
                ->get();

            $itensMes = [];
            foreach ($registros as $b) {
                if (!$b->produto) { continue; }
                $itensMes[] = [
                    'produto' => $b->produto->nome,
                    'data' => $b->data_baixa ? $b->data_baixa->format('d/m/Y') : null,
                    'quantidade' => (int) $b->quantidade
                ];
            }

            return response()->json([
                'success' => true,
                'avisos' => $itensMes
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar fardamentos: ' . $e->getMessage()
            ], 400);
        }
    }

    public function buscarFuncionarios()
    {
        $funcionarios = Funcionario::select('id', 'nome', 'funcao', 'cpf')
                                  ->orderBy('nome')
                                  ->get();
        
        return response()->json($funcionarios);
    }
    
    public function buscarCentroCustos()
    {
        $centroCustos = CentroCusto::where('ativo', true)
                                  ->select('id', 'nome')
                                  ->orderBy('nome')
                                  ->get();
        
        return response()->json($centroCustos);
    }
    
    public function buscarProdutos()
    {
        // Buscar produtos com níveis mínimo/máximo configurados
        $produtos = DB::table('estoque as e')
            ->leftJoin('estoque_min_max as mm', 'mm.produto_id', '=', 'e.id')
            ->select(
                'e.id',
                'e.nome',
                'e.descricao',
                'e.quantidade',
                DB::raw('COALESCE(mm.minimo, 0) as minimo'),
                'mm.maximo'
            )
            ->orderBy('e.nome')
            ->get();
        
        return response()->json($produtos);
    }
    
    public function produtosEmFalta()
    {
        // Buscar produtos com quantidade zero
        $produtosZerados = Estoque::where('quantidade', '=', 0)
                                 ->select('id', 'nome', 'descricao', 'quantidade')
                                 ->orderBy('nome')
                                 ->get();
        
        return response()->json($produtosZerados);
    }
    
    public function buscarProdutosPorNome(Request $request)
    {
        // Aceita tanto 'nome' quanto 'termo' para evitar conflitos de chamadas
        $termo = $request->get('nome') ?: $request->get('termo');

        if (!$termo || mb_strlen($termo) < 3) {
            return response()->json([]);
        }

        $produtos = DB::table('estoque as e')
            ->leftJoin('estoque_min_max as mm', 'mm.produto_id', '=', 'e.id')
            ->where(function($q) use ($termo){
                $q->where('e.nome', 'like', '%' . $termo . '%')
                  ->orWhere('e.descricao', 'like', '%' . $termo . '%');
            })
            ->select(
                'e.id',
                'e.nome',
                'e.descricao',
                'e.quantidade',
                DB::raw('COALESCE(mm.minimo, 0) as minimo'),
                'mm.maximo'
            )
            ->orderBy('e.nome')
            ->limit(50)
            ->get();

        return response()->json($produtos);
    }
    
    public function criarProduto(Request $request)
    {
        $request->validate([
            'nome' => ['required','string','max:255',
                Rule::unique('estoque', 'nome')->where(function($q) use ($request){
                    return $q->where('descricao', $request->descricao);
                })
            ],
            'descricao' => 'nullable|string|max:1000',
            'quantidade' => 'required|integer|min:0'
        ]);
        
        try {
            $produto = Estoque::create([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'quantidade' => $request->quantidade
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Produto cadastrado com sucesso!',
                'produto' => $produto
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao cadastrar produto: ' . $e->getMessage()
            ], 400);
        }
    }
    
    public function atualizarProduto(Request $request, $id)
    {
        $request->validate([
            'nome' => ['required','string','max:255',
                Rule::unique('estoque', 'nome')->ignore($id)->where(function($q) use ($request){
                    return $q->where('descricao', $request->descricao);
                })
            ],
            'descricao' => 'nullable|string|max:1000',
            'quantidade' => 'required|integer|min:0'
        ]);
        
        try {
            $produto = Estoque::findOrFail($id);
            $quantidadeAnterior = (int) $produto->quantidade;

            $produto->update([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'quantidade' => $request->quantidade
            ]);
            
            // Se a quantidade mudou, registrar ajuste
            $produto->refresh();
            $quantidadeNova = (int) $produto->quantidade;
            if ($quantidadeNova !== $quantidadeAnterior) {
                $alterada = $quantidadeNova - $quantidadeAnterior;
                $this->registrarLogEstoque(
                    $produto,
                    'ajuste',
                    $quantidadeAnterior,
                    $alterada,
                    $quantidadeNova,
                    'atualizarProduto',
                    null
                );
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso!',
                'produto' => $produto
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar produto: ' . $e->getMessage()
            ], 400);
        }
    }
    
    public function registrarEntrada(Request $request)
    {
        $request->validate([
            'produto_id' => 'required|exists:estoque,id',
            'quantidade' => 'required|integer|min:1',
            'observacoes' => 'nullable|string|max:1000'
        ]);
        
        try {
            DB::beginTransaction();
            
            $produto = Estoque::findOrFail($request->produto_id);
            
            // Adicionar quantidade ao estoque
            $quantidadeAnterior = (int) $produto->quantidade;
            $alterada = (int) $request->quantidade;
            $produto->increment('quantidade', $alterada);
            
            // Recarregar para obter a quantidade atualizada
            $produto->refresh();
            $this->registrarLogEstoque(
                $produto,
                'entrada',
                $quantidadeAnterior,
                $alterada,
                (int) $produto->quantidade,
                'registrarEntrada',
                $request->observacoes
            );
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Entrada registrada com sucesso! Novo estoque: {$produto->quantidade} unidades",
                'produto' => $produto
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Erro ao registrar entrada: ' . $e->getMessage()
            ], 400);
        }
    }
    
    public function registrarBaixa(Request $request)
    {
        $request->validate([
            'funcionario_id' => 'required|exists:funcionarios,id',
            'centro_custo_id' => 'required|exists:centro_custo,id',
            'baixas' => 'required|array|min:1',
            'baixas.*.produto_id' => 'required|exists:estoque,id',
            'baixas.*.quantidade' => 'required|integer|min:1',
            'observacoes' => 'nullable|string|max:1000'
        ]);
        
        try {
            DB::beginTransaction();
            
            foreach ($request->baixas as $baixaData) {
                // Verificar se o produto existe e tem estoque suficiente
                $produto = Estoque::find($baixaData['produto_id']);
                if (!$produto) {
                    throw new \Exception("Produto não encontrado");
                }
                
                // Verificar se há estoque suficiente
                if ($produto->quantidade < $baixaData['quantidade']) {
                    throw new \Exception("Estoque insuficiente para o produto: {$produto->nome}. Disponível: {$produto->quantidade}, Solicitado: {$baixaData['quantidade']}");
                }
                
                // Registrar a baixa
                Baixa::create([
                    'funcionario_id' => $request->funcionario_id,
                    'centro_custo_id' => $request->centro_custo_id,
                    'produto_id' => $baixaData['produto_id'],
                    'quantidade' => $baixaData['quantidade'],
                    'observacoes' => $request->observacoes,
                    'data_baixa' => now(),
                    'usuario_id' => Auth::id()
                ]);
                
                // Decrementar o estoque
                $quantidadeAnterior = (int) $produto->quantidade;
                $alterada = (int) $baixaData['quantidade'];
                $produto->decrement('quantidade', $alterada);

                // Registrar log de saída
                $produto->refresh();
                $this->registrarLogEstoque(
                    $produto,
                    'saida',
                    $quantidadeAnterior,
                    -$alterada,
                    (int) $produto->quantidade,
                    'registrarBaixa',
                    $request->observacoes
                );
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Baixa registrada com sucesso!'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
