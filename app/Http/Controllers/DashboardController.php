<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Baixa;
use App\Models\CentroCusto;
use App\Models\Veiculo;
use App\Models\Viagem;
use App\Models\Abastecimento;
use App\Models\Manutencao;
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
        $dadosFrota = [];
        $kmPorVeiculo = [];
        
        // Verificar se o usuário tem permissão "Controle de Estoque"
        if (Auth::user() && Auth::user()->temPermissao('Controle de Estoque')) {
            $dadosGraficos = $this->obterDadosGraficos();
        }
        
        // Verificar se o usuário é Administrador para dados da frota
        if (Auth::user() && optional(Auth::user()->profile)->name === 'Admin') {
            $dadosFrota = $this->obterDadosFrota();
        }
        
        // Coletar km atual de cada veículo para exibir no dashboard
        try {
            $kmPorVeiculo = Veiculo::select('id', 'placa', 'modelo', 'km_atual')
                ->orderBy('placa')
                ->get()
                ->map(function ($v) {
                    return [
                        'placa' => $v->placa,
                        'modelo' => $v->modelo,
                        'km_atual' => (int) $v->km_atual,
                    ];
                })
                ->toArray();
        } catch (\Throwable $e) {
            $kmPorVeiculo = [];
        }

        // Alertas e dados de licenciamento: apenas para administradores
        $alertasLicenciamento = [];
        $resumoLicenciamento = [];
        $licenciamentoCompleto = [];
        
        if (Auth::user() && optional(Auth::user()->profile)->name === 'Admin') {
            $alertasLicenciamento = $this->obterAlertasLicenciamento();
            $resumoLicenciamento = $this->obterResumoLicenciamento();
            $licenciamentoCompleto = $this->obterLicenciamentoCompleto();
        }

        return view('admin.dashboard-livewire', compact('dadosGraficos', 'dadosFrota', 'kmPorVeiculo', 'alertasLicenciamento', 'resumoLicenciamento', 'licenciamentoCompleto'));
    }

    /**
     * Obtém dados para os gráficos do dashboard
     */
    private function obterDadosGraficos()
    {
        // Período: mês corrente (zera no dia 01)
        $dataInicio = Carbon::now()->startOfMonth();
        $dataFim = Carbon::now()->endOfMonth();



        // Centros com mais saídas (últimos 30 dias) direto de 'baixas'
        $agregadoCentros = DB::table('baixas as b')
            ->whereBetween('b.data_baixa', [$dataInicio, $dataFim])
            ->whereNotNull('b.centro_custo_id')
            ->groupBy('b.centro_custo_id')
            ->select('b.centro_custo_id', DB::raw('SUM(b.quantidade) as total_quantidade'))
            ->orderByDesc(DB::raw('SUM(b.quantidade)'))
            ->get();

        $mapNomesCentros = CentroCusto::whereIn('id', $agregadoCentros->pluck('centro_custo_id')->all())
            ->pluck('nome', 'id');

        $centrosMaisPedidos = $agregadoCentros->values()->map(function ($item, $index) use ($mapNomesCentros) {
            $nome = $mapNomesCentros[$item->centro_custo_id] ?? ('ID ' . $item->centro_custo_id);
            return [
                'centro_nome' => $nome,
                'total_quantidade' => (int) $item->total_quantidade,
                'cor' => $this->obterCorFixa($index)
            ];
        });

        // Gráfico 2: Produtos mais retirados (somatório de quantidades) - mês corrente (Top 8)
        $produtosMaisRetirados = DB::table('baixas as b')
            ->join('estoque as e', 'e.id', '=', 'b.produto_id')
            ->whereBetween('b.data_baixa', [$dataInicio, $dataFim])
            ->groupBy('b.produto_id', 'e.nome')
            ->orderByDesc(DB::raw('SUM(b.quantidade)'))
            ->select(
                'b.produto_id',
                'e.nome as produto_nome',
                DB::raw('SUM(b.quantidade) as total_quantidade'),
                DB::raw('COUNT(*) as total_retiradas')
            )
            ->limit(8)
            ->get()
            ->map(function ($item, $index) {
                return [
                    'produto_nome' => $item->produto_nome ?? 'Não informado',
                    'total_quantidade' => (int) $item->total_quantidade,
                    'total_retiradas' => (int) $item->total_retiradas,
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

    /**
     * Obtém dados da frota para o dashboard (apenas para Administradores)
     */
    private function obterDadosFrota()
    {
        $dataAtual = Carbon::now();
        $inicioMes = $dataAtual->copy()->startOfMonth();
        $fimMes = $dataAtual->copy()->endOfMonth();

        // Estatísticas gerais da frota (somente ativos)
        $totalVeiculos = Veiculo::where('status', 'ativo')->count();

        // Veículos em uso: há viagem aberta (sem km_retorno ou sem data_retorno)
        $veiculosEmUsoIds = Viagem::select('vehicle_id')
            ->where(function($q){
                $q->whereNull('km_retorno')->orWhereNull('data_retorno');
            })
            ->groupBy('vehicle_id')
            ->pluck('vehicle_id');
        $veiculosEmUso = $veiculosEmUsoIds->count();

        // Disponíveis: status ativo e não estão em uso
        $veiculosAtivos = Veiculo::where('status', 'ativo')
            ->whereNotIn('id', $veiculosEmUsoIds)
            ->count();

        $veiculosManutencao = Veiculo::where('status', 'manutencao')->count();

        // Viagens no mês atual
        $viagensNoMes = Viagem::whereBetween('data_saida', [$inicioMes, $fimMes])->count();
        $kmPercorridoMes = Viagem::whereBetween('data_saida', [$inicioMes, $fimMes])
            ->whereNotNull('km_retorno')
            ->sum(DB::raw('km_retorno - km_saida'));

        // Custos no mês atual
        $custoAbastecimentoMes = Abastecimento::whereBetween('data', [$inicioMes, $fimMes])->sum('valor');
        $custoManutencaoMes = Manutencao::whereBetween('data', [$inicioMes, $fimMes])->sum('custo');
        $custoTotalMes = $custoAbastecimentoMes + $custoManutencaoMes;

        // Veículos por status (para gráfico)
        $veiculosPorStatus = [
            ['label' => 'Disponível', 'value' => $veiculosAtivos, 'cor' => '#28a745'],
            ['label' => 'Em Uso', 'value' => $veiculosEmUso, 'cor' => '#ffc107'],
            ['label' => 'Manutenção', 'value' => $veiculosManutencao, 'cor' => '#dc3545'],
        ];

        // Consumo mensal (últimos 6 meses)
        $consumoMensal = [];
        for ($i = 5; $i >= 0; $i--) {
            $mesAtual = Carbon::now()->subMonths($i);
            $inicioMesAtual = $mesAtual->copy()->startOfMonth();
            $fimMesAtual = $mesAtual->copy()->endOfMonth();
            
            $valorMes = Abastecimento::whereBetween('data', [$inicioMesAtual, $fimMesAtual])
                ->sum('valor');
            
            $consumoMensal[] = [
                'mes' => $mesAtual->format('M/Y'),
                'valor' => (float) $valorMes
            ];
        }

        // Top 5 veículos mais utilizados no mês
        $veiculosMaisUtilizados = Viagem::select('vehicle_id', DB::raw('COUNT(*) as total_viagens'))
            ->with('veiculo:id,placa,modelo')
            ->whereBetween('data_saida', [$inicioMes, $fimMes])
            ->groupBy('vehicle_id')
            ->orderByDesc('total_viagens')
            ->limit(5)
            ->get()
            ->map(function ($viagem, $index) {
                return [
                    'veiculo' => $viagem->veiculo ? $viagem->veiculo->placa . ' - ' . $viagem->veiculo->modelo : 'N/A',
                    'viagens' => $viagem->total_viagens,
                    'cor' => $this->obterCorFixa($index)
                ];
            });

        return [
            'cards' => [
                'total_veiculos' => $totalVeiculos,
                'veiculos_ativos' => $veiculosAtivos,
                'veiculos_em_uso' => $veiculosEmUso,
                'viagens_mes' => $viagensNoMes,
                'km_percorrido_mes' => number_format($kmPercorridoMes, 0, ',', '.'),
                'custo_total_mes' => number_format($custoTotalMes, 2, ',', '.')
            ],
            'graficos' => [
                'veiculos_por_status' => $veiculosPorStatus,
                'consumo_mensal' => $consumoMensal,
                'veiculos_mais_utilizados' => $veiculosMaisUtilizados
            ],
            'periodo' => [
                'mes_atual' => $dataAtual->format('M/Y')
            ]
        ];
    }

    /**
     * Lista veículos com licenciamento a vencer nos próximos N dias (ou vencidos).
     * Não usa BLOBs e evita consultas pesadas.
     */
    private function obterAlertasLicenciamento(): array
    {
        try {
            $limiteDias = 45; // janela para aviso
            $hoje = Carbon::today();

            // Mapa de veículos
            $veiculos = DB::table('veiculos')
                ->select('id','placa','marca','modelo')
                ->orderBy('placa')
                ->get();

            if ($veiculos->isEmpty()) {
                return [];
            }

            $mapVeiculo = [];
            foreach ($veiculos as $v) {
                $mapVeiculo[(int)$v->id] = $v;
            }

            // Buscar todos os registros de licenciamento ordenados por veículo e recência
            $lics = DB::table('veiculo_licenciamentos')
                ->select('veiculo_id','ano_exercicio','data_pagamento')
                ->orderBy('veiculo_id')
                ->orderByDesc('ano_exercicio')
                ->orderByDesc('data_pagamento')
                ->get();

            // Pegar o mais recente por veículo
            $ultimoPorVeiculo = [];
            foreach ($lics as $l) {
                $vid = (int)$l->veiculo_id;
                if (!isset($ultimoPorVeiculo[$vid])) {
                    $ultimoPorVeiculo[$vid] = $l;
                }
            }

            $alertas = [];
            foreach ($ultimoPorVeiculo as $vid => $l) {
                if (!isset($mapVeiculo[$vid])) continue;
                $proximo = null;
                if (!empty($l->data_pagamento)) {
                    $proximo = Carbon::parse($l->data_pagamento)->addYear()->startOfDay();
                } elseif (!empty($l->ano_exercicio)) {
                    $anoBase = max((int)$l->ano_exercicio, (int)$hoje->format('Y'));
                    $proximo = Carbon::createFromDate($anoBase, 12, 31)->startOfDay();
                }

                if (!$proximo) continue;

                $dias = $hoje->diffInDays($proximo, false); // negativo se vencido
                if ($dias <= $limiteDias) {
                    $v = $mapVeiculo[$vid];
                    $alertas[] = [
                        'veiculo_id' => $vid,
                        'placa' => $v->placa,
                        'modelo' => trim(($v->marca ?? '') . ' ' . ($v->modelo ?? '')),
                        'proximo' => $proximo->format('Y-m-d'),
                        'dias' => $dias,
                        'status' => $dias < 0 ? 'vencido' : ($dias <= 15 ? 'critico' : 'aviso'),
                    ];
                }
            }

            // Ordenar: vencidos primeiro, depois por dias restantes
            usort($alertas, function($a, $b){
                if (($a['dias'] < 0) !== ($b['dias'] < 0)) return $a['dias'] < 0 ? -1 : 1;
                return $a['dias'] <=> $b['dias'];
            });

            return $alertas;
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Produz um resumo: próximos vencimentos (até 90 dias) e pagos no ano corrente.
     */
    private function obterResumoLicenciamento(): array
    {
        try {
            $hoje = Carbon::today();
            $limiteDias = 90;

            $veiculos = DB::table('veiculos')
                ->select('id','placa','marca','modelo')
                ->orderBy('placa')
                ->get();

            if ($veiculos->isEmpty()) return ['vencimentos'=>[], 'pagos_ano'=>[]];

            $map = [];
            foreach ($veiculos as $v) { $map[(int)$v->id] = $v; }

            $lics = DB::table('veiculo_licenciamentos')
                ->select('veiculo_id','ano_exercicio','data_pagamento')
                ->orderBy('veiculo_id')
                ->orderByDesc('ano_exercicio')
                ->orderByDesc('data_pagamento')
                ->get();

            $ultimo = [];
            foreach ($lics as $l) { $vid = (int)$l->veiculo_id; if (!isset($ultimo[$vid])) $ultimo[$vid] = $l; }

            $vencimentos = [];
            $pagosAno = [];
            $anoAtual = (int)$hoje->format('Y');

            foreach ($ultimo as $vid => $l) {
                if (!isset($map[$vid])) continue;
                $proximo = null;
                if (!empty($l->data_pagamento)) {
                    $proximo = Carbon::parse($l->data_pagamento)->addYear()->startOfDay();
                } elseif (!empty($l->ano_exercicio)) {
                    $anoBase = max((int)$l->ano_exercicio, $anoAtual);
                    $proximo = Carbon::createFromDate($anoBase, 12, 31)->startOfDay();
                }
                if ($proximo) {
                    $dias = $hoje->diffInDays($proximo, false);
                    if ($dias <= $limiteDias) {
                        $v = $map[$vid];
                        $vencimentos[] = [
                            'placa' => $v->placa,
                            'modelo' => trim(($v->marca ?? '').' '.($v->modelo ?? '')),
                            'data' => $proximo->format('Y-m-d'),
                            'dias' => $dias,
                        ];
                    }
                }

                // Pago no ano atual?
                $pagoAno = false;
                if (!empty($l->ano_exercicio) && (int)$l->ano_exercicio === $anoAtual) $pagoAno = true;
                if (!$pagoAno && !empty($l->data_pagamento)) {
                    $pagoAno = ((int)Carbon::parse($l->data_pagamento)->format('Y') === $anoAtual);
                }
                if ($pagoAno) {
                    $v = $map[$vid];
                    $pagosAno[] = [
                        'placa' => $v->placa,
                        'modelo' => trim(($v->marca ?? '').' '.($v->modelo ?? '')),
                        'ano' => $anoAtual,
                    ];
                }
            }

            usort($vencimentos, function($a,$b){ return $a['dias'] <=> $b['dias']; });

            return [
                'vencimentos' => $vencimentos,
                'pagos_ano' => $pagosAno,
            ];
        } catch (\Throwable $e) {
            return ['vencimentos'=>[], 'pagos_ano'=>[]];
        }
    }

    /**
     * Lista todos os veículos com status de licenciamento (Pago no ano/Pendente) e data prevista do próximo pagamento.
     */
    private function obterLicenciamentoCompleto(): array
    {
        try {
            $hoje = Carbon::today();
            $anoAtual = (int)$hoje->format('Y');

            $veiculos = DB::table('veiculos')
                ->select('id','placa','marca','modelo')
                ->orderBy('placa')
                ->get();

            if ($veiculos->isEmpty()) return [];

            $map = [];
            foreach ($veiculos as $v) { $map[(int)$v->id] = $v; }

            $lics = DB::table('veiculo_licenciamentos')
                ->select('veiculo_id','ano_exercicio','data_pagamento')
                ->orderBy('veiculo_id')
                ->orderByDesc('ano_exercicio')
                ->orderByDesc('data_pagamento')
                ->get();

            $ultimo = [];
            foreach ($lics as $l) { $vid = (int)$l->veiculo_id; if (!isset($ultimo[$vid])) $ultimo[$vid] = $l; }

            $saida = [];
            foreach ($map as $vid => $v) {
                $u = $ultimo[$vid] ?? null;
                $pagoAno = false;
                $proximo = null;

                if ($u) {
                    if (!empty($u->ano_exercicio) && (int)$u->ano_exercicio === $anoAtual) $pagoAno = true;
                    if (!$pagoAno && !empty($u->data_pagamento)) {
                        $pagoAno = ((int)Carbon::parse($u->data_pagamento)->format('Y') === $anoAtual);
                    }

                    if (!empty($u->data_pagamento)) {
                        $proximo = Carbon::parse($u->data_pagamento)->addYear()->startOfDay();
                    } elseif (!empty($u->ano_exercicio)) {
                        $anoBase = max((int)$u->ano_exercicio, $anoAtual);
                        $proximo = Carbon::createFromDate($anoBase, 12, 31)->startOfDay();
                    }
                } else {
                    // Nunca pago: estimar como 31/12 do ano atual
                    $proximo = Carbon::createFromDate($anoAtual, 12, 31)->startOfDay();
                }

                $saida[] = [
                    'placa' => $v->placa,
                    'modelo' => trim(($v->marca ?? '').' '.($v->modelo ?? '')),
                    'status' => $pagoAno ? 'Pago no ano' : 'Pendente',
                    'proximo' => $proximo ? $proximo->format('Y-m-d') : null,
                ];
            }

            // Ordenar: pendentes primeiro, depois por placa
            usort($saida, function($a,$b){
                if ($a['status'] !== $b['status']) return $a['status'] === 'Pendente' ? -1 : 1;
                return strcmp($a['placa'], $b['placa']);
            });

            return $saida;
        } catch (\Throwable $e) {
            return [];
        }
    }
} 