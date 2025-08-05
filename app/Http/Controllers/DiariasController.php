<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Diaria;
use App\Models\Funcionario;
use Carbon\Carbon;

class DiariasController extends Controller
{
    /**
     * Exibe a lista de todas as diárias.
     */
    public function index()
    {
        $diarias = Diaria::orderBy('data_inclusao', 'desc')->paginate(15);
        return view('diarias.index', compact('diarias'));
    }

    /**
     * Exibe o formulário de cadastro de diárias.
     */
    public function cadastro()
    {
        // Buscar departamentos para passar para a view
        $departamentos = \App\Models\Funcionario::select('departamento')
            ->distinct()
            ->orderBy('departamento')
            ->pluck('departamento')
            ->toArray();
            
        // Adicionar scores para resolver o erro na linha 32
        $scores = [];
            
        return view('diarias.cadastro', compact('departamentos', 'scores'));
    }

    /**
     * Exibe a página de relatórios com filtros.
     */
    public function relatorio(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));
        
        $query = Diaria::query();
        
        if ($request->filled('data_inicio')) {
            $query->where('data_inclusao', '>=', $dataInicio . ' 00:00:00');
        }
        
        if ($request->filled('data_fim')) {
            $query->where('data_inclusao', '<=', $dataFim . ' 23:59:59');
        }
        
        if ($request->filled('departamento')) {
            $query->where('departamento', $request->input('departamento'));
        }
        
        if ($request->filled('referencia')) {
            $query->where('referencia', $request->input('referencia'));
        }
        
        $diarias = $query->orderBy('data_inclusao', 'desc')->get();
        
        $totalDiarias = $diarias->sum('diaria');
        $departamentos = Funcionario::select('departamento')->distinct()->pluck('departamento');
        $referencias = ['Compensação', 'Feriado', 'Horas acumuladas', 'Folga', 'Teste', 'Diária'];
        
        return view('diarias.relatorio', compact('diarias', 'totalDiarias', 'departamentos', 'referencias', 'dataInicio', 'dataFim'));
    }

    /**
     * Exibe a página de relatório gerente com filtros.
     */
    public function relatorioGerente(Request $request)
    {
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dataFim = $request->input('data_fim', Carbon::now()->format('Y-m-d'));
        
        $query = Diaria::query()
            ->where('departamento', 'Gerência');
        
        if ($request->filled('data_inicio')) {
            $query->where('data_inclusao', '>=', $dataInicio . ' 00:00:00');
        }
        
        if ($request->filled('data_fim')) {
            $query->where('data_inclusao', '<=', $dataFim . ' 23:59:59');
        }
        
        if ($request->filled('referencia')) {
            $query->where('referencia', $request->input('referencia'));
        }
        
        $diarias = $query->orderBy('data_inclusao', 'desc')->get();
        
        $totalDiarias = $diarias->sum('diaria');
        $referencias = ['Compensação', 'Feriado', 'Horas acumuladas', 'Folga', 'Teste', 'Diária'];
        
        return view('diarias.relatorio-gerente', compact('diarias', 'totalDiarias', 'referencias', 'dataInicio', 'dataFim'));
    }

    /**
     * Exporta os dados das diárias para Excel.
     */
    public function exportar(Request $request)
    {
        // Implementação da exportação para Excel pode ser adicionada aqui
        // Utilizando pacotes como maatwebsite/excel
        
        return back()->with('message', 'Função de exportação a ser implementada.');
    }

    // Métodos relatorio1000 e relatorio1001 removidos

    public function buscarDiariasGerentes(Request $request)
    {
        try {
            $gerente = $request->input('gerente');
            $dataInicial = $request->input('data_inicial');
            $dataFinal = $request->input('data_final');

            $query = Diaria::query();

            // Filtro por gerente
            if ($gerente) {
                $query->where('gerente', $gerente);
            }

            // Filtro por data inicial
            if ($dataInicial) {
                $query->where('data_inclusao', '>=', $dataInicial . ' 00:00:00');
            }

            // Filtro por data final
            if ($dataFinal) {
                $query->where('data_inclusao', '<=', $dataFinal . ' 23:59:59');
            }

            $diarias = $query->orderBy('data_inclusao', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $diarias
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar diárias: ' . $e->getMessage()
            ], 500);
        }
    }

    public function listarGerentes()
    {
        try {
            // Busca gerentes da tabela de funcionários
            $gerentesFuncionarios = Funcionario::select('nome')
                ->where('funcao', 'like', '%Gerente%')
                ->orWhere('funcao', 'like', '%GERENTE%')
                ->pluck('nome')
                ->toArray();

            // Busca gerentes da tabela de diárias
            $gerentesDiarias = Diaria::select('gerente')
                ->distinct()
                ->whereNotNull('gerente')
                ->pluck('gerente')
                ->toArray();

            // Combina os dois arrays e remove duplicatas
            $todosGerentes = array_unique(array_merge($gerentesFuncionarios, $gerentesDiarias));
            
            // Ordena os gerentes
            sort($todosGerentes);

            return response()->json([
                'success' => true,
                'data' => $todosGerentes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar gerentes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista apenas os gerentes que têm registros na tabela de diárias
     */
    public function listarGerentesComDiarias()
    {
        try {
            // Busca gerentes apenas da tabela de diárias
            $gerentesDiarias = Diaria::select('gerente')
                ->distinct()
                ->whereNotNull('gerente')
                ->where('gerente', '!=', '')
                ->orderBy('gerente')
                ->pluck('gerente')
                ->toArray();
            
            return response()->json([
                'success' => true,
                'data' => $gerentesDiarias
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar gerentes: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método relatorio1002 removido

    public function buscarRecursosHumanos(Request $request)
    {
        try {
            $dataInicial = $request->input('data_inicial');
            $dataFinal = $request->input('data_final');
            $gerente = $request->input('gerente');
            
            // Verificar se as datas foram fornecidas
            if (!$dataInicial || !$dataFinal) {
                return response()->json([
                    'success' => false,
                    'message' => 'As datas de início e fim são obrigatórias'
                ], 400);
            }
            
            // Formatar as datas para incluir o período completo (início do dia até fim do dia)
            $dataInicialFormatada = Carbon::parse($dataInicial)->startOfDay()->format('Y-m-d H:i:s');
            $dataFinalFormatada = Carbon::parse($dataFinal)->endOfDay()->format('Y-m-d H:i:s');
            
            // Construir a query com os filtros de data
            $query = Diaria::query();
            
            // Aplicar filtro de data na data_inclusao
            $query->whereBetween('data_inclusao', [$dataInicialFormatada, $dataFinalFormatada]);
            
            // Filtrar pelo gerente (nome do usuário logado ou gerente selecionado)
            if ($gerente) {
                $query->where('gerente', $gerente);
            } else {
                $usuarioLogado = auth()->user()->name;
                $query->where('gerente', $usuarioLogado);
            }
            
            // Ordenar por data de inclusão (decrescente)
            $query->orderBy('data_inclusao', 'desc');
            
            // Executar a query e obter os resultados
            $diarias = $query->get();
            
            // Verificar se há resultados
            if ($diarias->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhum registro encontrado para o período selecionado'
                ]);
            }
            
            return response()->json([
                'success' => true,
                'data' => $diarias
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar dados: ' . $e->getMessage()
            ], 500);
        }
    }
}