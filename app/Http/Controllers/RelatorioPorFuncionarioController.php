<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Baixa;
use App\Models\Estoque;
use App\Models\CentroCusto;
use App\Models\Funcionario;
use Illuminate\Support\Facades\DB;

class RelatorioPorFuncionarioController extends Controller
{
    public function index()
    {
        return view('relatorios.funcionario');
    }

    public function gerarRelatorio(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'funcionario_id' => 'nullable|exists:funcionarios,id'
        ]);

        // Montar query base
        $query = Baixa::with(['funcionario', 'produto', 'centroCusto', 'usuario']);

        // Aplicar filtro de datas com precisão:
        // - Se início == fim, filtra exatamente aquele dia
        // - Caso contrário, aplica intervalo completo incluindo os horários extremos
        if ($request->data_inicio === $request->data_fim) {
            $query->whereDate('data_baixa', '=', $request->data_inicio);
        } else {
            $dataInicio = $request->data_inicio . ' 00:00:00';
            $dataFim = $request->data_fim . ' 23:59:59';
            $query->whereBetween('data_baixa', [$dataInicio, $dataFim]);
        }

        // Filtro por funcionário
        if ($request->funcionario_id) {
            $query->where('funcionario_id', $request->funcionario_id);
        }

        $baixas = $query->orderBy('funcionario_id', 'asc')
                       ->orderBy('data_baixa', 'desc')
                       ->get();

        // Agrupar por funcionário
        $dadosAgrupados = $this->agruparBaixasPorFuncionario($baixas);

        // Calcular resumo
        $resumo = $this->calcularResumo($baixas, $request);

        return response()->json([
            'success' => true,
            'dados' => $dadosAgrupados,
            'resumo' => $resumo,
            'total_registros' => count($dadosAgrupados)
        ]);
    }

    private function agruparBaixasPorFuncionario($baixas)
    {
        $agrupados = [];

        foreach ($baixas as $baixa) {
            // Criar chave única para cada combinação funcionário-data-hora
            $chave = $baixa->funcionario_id . '_' . $baixa->data_baixa->format('Y-m-d H:i');
            
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
        $totalItens = $baixas->sum('quantidade');
        $totalEntregas = $baixas->count();
        $funcionariosUnicos = $baixas->pluck('funcionario_id')->unique()->count();
        $centrosUnicos = $baixas->pluck('centro_custo_id')->unique()->filter()->count();

        // Calcular detalhamento por funcionário
        $porFuncionario = [];
        foreach ($baixas->groupBy('funcionario_id') as $funcionarioId => $baixasFuncionario) {
            $funcionario = $baixasFuncionario->first()->funcionario;
            $porFuncionario[] = [
                'funcionario_id' => $funcionarioId,
                'funcionario_nome' => $funcionario ? $funcionario->nome : 'Não informado',
                'funcionario_funcao' => $funcionario ? $funcionario->funcao : 'Não informada',
                'total_itens' => $baixasFuncionario->sum('quantidade'),
                'total_entregas' => $baixasFuncionario->count(),
                'centros_atendidos' => $baixasFuncionario->pluck('centro_custo_id')->unique()->filter()->count()
            ];
        }

        // Ordenar por total de itens (maior primeiro)
        usort($porFuncionario, function($a, $b) {
            return $b['total_itens'] - $a['total_itens'];
        });

        return [
            'total_funcionarios' => $funcionariosUnicos,
            'total_centros' => $centrosUnicos,
            'total_entregas' => $totalEntregas,
            'total_itens' => $totalItens,
            'ranking_funcionarios' => array_slice($porFuncionario, 0, 10), // Top 10
            'detalhamento_por_funcionario' => $porFuncionario,
            'periodo' => [
                'inicio' => $request->data_inicio,
                'fim' => $request->data_fim
            ]
        ];
    }

    public function exportarExcel(Request $request)
    {
        $request->validate([
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'funcionario_id' => 'nullable|exists:funcionarios,id'
        ]);

        $query = Baixa::with(['funcionario', 'produto', 'centroCusto', 'usuario']);

        if ($request->data_inicio === $request->data_fim) {
            $query->whereDate('data_baixa', '=', $request->data_inicio);
        } else {
            $dataInicio = $request->data_inicio . ' 00:00:00';
            $dataFim = $request->data_fim . ' 23:59:59';
            $query->whereBetween('data_baixa', [$dataInicio, $dataFim]);
        }

        // Aplicar os mesmos filtros
        if ($request->funcionario_id) {
            $query->where('funcionario_id', $request->funcionario_id);
        }

        $baixas = $query->orderBy('funcionario_id', 'asc')
                       ->orderBy('data_baixa', 'desc')
                       ->get();

        // Para simular Excel, retornamos CSV por enquanto
        $csv = "Funcionário,Função,Centro de Custo,Data,Hora,Produto,Quantidade,Observações\n";
        
        foreach ($baixas as $baixa) {
            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s",%d,"%s"' . "\n",
                $baixa->funcionario->nome,
                $baixa->funcionario->funcao ?? 'Não informada',
                $baixa->centroCusto->nome ?? 'Não informado',
                $baixa->data_baixa->format('d/m/Y'),
                $baixa->data_baixa->format('H:i'),
                $baixa->produto->nome,
                $baixa->quantidade,
                $baixa->observacoes ?? ''
            );
        }

        $filename = 'relatorio-funcionario-' . date('Y-m-d') . '.csv';
        
        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}