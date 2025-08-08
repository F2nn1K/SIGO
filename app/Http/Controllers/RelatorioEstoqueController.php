<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Baixa;
use App\Models\Estoque;
use App\Models\CentroCusto;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;

class RelatorioEstoqueController extends Controller
{
    public function index()
    {
        return view('relatorios.estoque');
    }

    public function gerarRelatorio(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'produto_id' => 'nullable|exists:estoque,id',
            'centro_custo_id' => 'nullable|exists:centro_custo,id'
        ]);

        $query = Baixa::with(['funcionario', 'produto', 'centroCusto', 'usuario'])
            ->whereBetween('data_baixa', [
                $request->data_inicio . ' 00:00:00',
                $request->data_fim . ' 23:59:59'
            ]);

        // Filtro por produto
        if ($request->produto_id) {
            $query->where('produto_id', $request->produto_id);
        }

        // Filtro por centro de custo
        if ($request->centro_custo_id) {
            $query->where('centro_custo_id', $request->centro_custo_id);
        }

        $baixas = $query->orderBy('data_baixa', 'desc')->get();

        // Agrupar por funcionário e centro de custo
        $dadosAgrupados = $this->agruparBaixas($baixas);

        // Calcular resumo
        $resumo = $this->calcularResumo($baixas, $request);

        return response()->json([
            'success' => true,
            'dados' => $dadosAgrupados,
            'resumo' => $resumo,
            'total_registros' => count($dadosAgrupados)
        ]);
    }

    private function agruparBaixas($baixas)
    {
        $agrupados = [];

        foreach ($baixas as $baixa) {
            $chave = $baixa->funcionario_id . '_' . $baixa->centro_custo_id . '_' . $baixa->data_baixa->format('Y-m-d H:i');
            
            if (!isset($agrupados[$chave])) {
                $agrupados[$chave] = [
                    'data' => $baixa->data_baixa->format('d/m/Y'),
                    'hora' => $baixa->data_baixa->format('H:i'),
                    'funcionario' => [
                        'id' => $baixa->funcionario->id,
                        'nome' => $baixa->funcionario->nome,
                        'funcao' => $baixa->funcionario->funcao ?? 'Não informado'
                    ],
                    'centro_custo' => [
                        'id' => $baixa->centroCusto->id ?? null,
                        'nome' => $baixa->centroCusto->nome ?? 'Não informado'
                    ],
                    'observacoes' => $baixa->observacoes,
                    'usuario' => $baixa->usuario->name,
                    'produtos' => [],
                    'total_itens' => 0
                ];
            }

            $agrupados[$chave]['produtos'][] = [
                'id' => $baixa->produto->id,
                'nome' => $baixa->produto->nome,
                'descricao' => $baixa->produto->descricao,
                'quantidade' => $baixa->quantidade
            ];

            $agrupados[$chave]['total_itens'] += $baixa->quantidade;
        }

        return array_values($agrupados);
    }

    private function calcularResumo($baixas, $request)
    {
        $totalSaidas = $baixas->sum('quantidade');
        $totalMovimentacoes = $baixas->count();
        $produtosUnicos = $baixas->pluck('produto_id')->unique()->count();
        $centrosUnicos = $baixas->pluck('centro_custo_id')->unique()->filter()->count();
        $funcionariosUnicos = $baixas->pluck('funcionario_id')->unique()->count();

        // Calcular total de entradas no período (se houver tabela de entradas)
        $totalEntradas = 0; // Por enquanto 0, pois não temos tabela de entradas separada

        return [
            'total_saidas' => $totalSaidas,
            'total_entradas' => $totalEntradas,
            'total_movimentacoes' => $totalMovimentacoes,
            'produtos_movimentados' => $produtosUnicos,
            'centros_envolvidos' => $centrosUnicos,
            'funcionarios_atendidos' => $funcionariosUnicos,
            'periodo' => [
                'inicio' => $request->data_inicio,
                'fim' => $request->data_fim
            ]
        ];
    }

    public function exportarExcel(Request $request)
    {
        // Implementar exportação Excel futuramente
        return response()->json([
            'success' => false,
            'message' => 'Funcionalidade de exportação em desenvolvimento'
        ]);
    }
}