<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Baixa;
use App\Models\CentroCusto;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Exibe a view do dashboard usando o componente Livewire
     */
    public function index()
    {
        $dadosGraficos = [];
        
        // Verificar se o usuário tem permissão "Controle de Estoque"
        if (Auth::user() && Auth::user()->temPermissao('Controle de Estoque')) {
            $dadosGraficos = $this->obterDadosGraficos();
        }
        
        return view('admin.dashboard-livewire', compact('dadosGraficos'));
    }

    /**
     * Obtém dados para os gráficos do dashboard
     */
    private function obterDadosGraficos()
    {
        // Período dos últimos 30 dias
        $dataInicio = Carbon::now()->subDays(30);
        $dataFim = Carbon::now();



        // Gráfico 1: Centros de Custo com mais pedidos (últimos 30 dias)
        $centrosMaisPedidos = Baixa::with('centroCusto')
            ->whereBetween('data_baixa', [$dataInicio, $dataFim])
            ->select(
                'centro_custo_id',
                DB::raw('COUNT(*) as total_pedidos'),
                DB::raw('SUM(quantidade) as total_itens')
            )
            ->groupBy('centro_custo_id')
            ->orderByDesc('total_pedidos')
            ->limit(8) // Top 8 centros
            ->get()
            ->map(function ($item, $index) {
                return [
                    'centro_nome' => $item->centroCusto ? $item->centroCusto->nome : 'Não informado',
                    'total_pedidos' => $item->total_pedidos,
                    'total_itens' => $item->total_itens,
                    'cor' => $this->obterCorFixa($index)
                ];
            });

        // Gráfico 2: Produtos mais retirados (últimos 30 dias)
        $produtosMaisRetirados = Baixa::with('produto')
            ->whereBetween('data_baixa', [$dataInicio, $dataFim])
            ->select(
                'produto_id',
                DB::raw('SUM(quantidade) as total_quantidade'),
                DB::raw('COUNT(*) as total_retiradas')
            )
            ->groupBy('produto_id')
            ->orderByDesc('total_quantidade')
            ->limit(8) // Top 8 produtos
            ->get()
            ->map(function ($item, $index) {
                return [
                    'produto_nome' => $item->produto ? $item->produto->nome : 'Não informado',
                    'total_quantidade' => $item->total_quantidade,
                    'total_retiradas' => $item->total_retiradas,
                    'cor' => $this->obterCorFixa($index)
                ];
            });



        return [
            'centros_mais_pedidos' => $centrosMaisPedidos,
            'produtos_mais_retirados' => $produtosMaisRetirados,
            'periodo' => [
                'inicio' => $dataInicio->format('d/m/Y'),
                'fim' => $dataFim->format('d/m/Y')
            ]
        ];
    }

    /**
     * Obtém uma cor fixa baseada no índice para manter consistência
     */
    private function obterCorFixa($index)
    {
        $cores = [
            '#FF6384', // Rosa/Vermelho
            '#36A2EB', // Azul
            '#FFCE56', // Amarelo
            '#4BC0C0', // Turquesa
            '#9966FF', // Roxo
            '#FF9F40', // Laranja
            '#FF6B6B', // Coral
            '#4ECDC4', // Verde-azulado
            '#45B7D1', // Azul claro
            '#96CEB4', // Verde claro
            '#FECA57', // Dourado
            '#FF8A80'  // Rosa claro
        ];
        
        // Retorna a cor baseada no índice, repetindo o ciclo se necessário
        return $cores[$index % count($cores)];
    }
} 