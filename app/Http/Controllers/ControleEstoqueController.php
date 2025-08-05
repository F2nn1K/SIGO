<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Estoque;
use App\Models\Funcionario;
use App\Models\Baixa;
use App\Models\CentroCusto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ControleEstoqueController extends Controller
{
    public function index()
    {
        // Calcular estatísticas para os cards
        $totalProdutos = Estoque::count(); // Total de produtos cadastrados
        
        // Entradas e saídas do mês atual
        $mesAtual = now()->format('Y-m');
        $entradasMes = 0; // TODO: Implementar quando houver tabela de entradas
        $saidasMes = Baixa::whereRaw('DATE_FORMAT(data_baixa, "%Y-%m") = ?', [$mesAtual])
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
        // Buscar todos os produtos da tabela estoque com quantidade real
        $produtos = Estoque::select('id', 'nome', 'descricao', 'quantidade')
                          ->orderBy('nome')
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
        $nome = $request->get('nome');
        
        if (!$nome || strlen($nome) < 3) {
            return response()->json([]);
        }
        
        $produtos = Estoque::where('nome', 'like', '%' . $nome . '%')
                          ->select('id', 'nome', 'descricao', 'quantidade', 'created_at', 'updated_at')
                          ->orderBy('nome')
                          ->limit(20)
                          ->get();
        
        return response()->json($produtos);
    }
    
    public function criarProduto(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:estoque,nome',
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
            'nome' => 'required|string|max:255',
            'descricao' => 'nullable|string|max:1000',
            'quantidade' => 'required|integer|min:0'
        ]);
        
        try {
            $produto = Estoque::findOrFail($id);
            
            $produto->update([
                'nome' => $request->nome,
                'descricao' => $request->descricao,
                'quantidade' => $request->quantidade
            ]);
            
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
            $produto->increment('quantidade', $request->quantidade);
            
            // Recarregar para obter a quantidade atualizada
            $produto->refresh();
            
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
            'cc' => 'required|exists:centro_custo,id',
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
                    'cc' => $request->cc,
                    'produto_id' => $baixaData['produto_id'],
                    'quantidade' => $baixaData['quantidade'],
                    'observacoes' => $request->observacoes,
                    'data_baixa' => now(),
                    'usuario_id' => Auth::id()
                ]);
                
                // Decrementar o estoque
                $produto->decrement('quantidade', $baixaData['quantidade']);
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
