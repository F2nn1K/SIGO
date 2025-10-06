<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioKmController extends Controller
{
    public function index()
    {
        return view('frota.relatorios.km-percorrido');
    }

    public function data(Request $request)
    {
        $agrupar = $request->input('agrupar', 'veiculo'); // 'veiculo' | 'usuario'
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');
        $vehicleId = $request->input('vehicle_id');
        $userId = $request->input('user_id');

        $q = DB::table('viagens')
            ->when($vehicleId, fn($qq) => $qq->where('vehicle_id', $vehicleId))
            ->when($userId, fn($qq) => $qq->where('user_id', $userId))
            ->when($dataInicio, fn($qq) => $qq->where('data_saida', '>=', $dataInicio))
            ->when($dataFim, fn($qq) => $qq->where('data_saida', '<=', $dataFim))
            ->whereNotNull('km_saida')
            ->whereNotNull('km_retorno')
            ->where('km_retorno', '>', DB::raw('km_saida')); // Garantir que retorno > saída

        if ($agrupar === 'usuario') {
            // Agrupar por usuário: somar todos os KM que cada usuário percorreu
            $rows = $q->select(
                    'user_id as id',
                    DB::raw('SUM(km_retorno - km_saida) as km_percorrido_total'),
                    DB::raw('MIN(km_saida) as km_inicial'),
                    DB::raw('MAX(km_retorno) as km_final')
                )
                ->groupBy('user_id')
                ->get();

            $map = DB::table('users')->pluck('name', 'id');

            $dados = $rows->map(function($r) use ($map) {
                $kmPercorrido = (int)($r->km_percorrido_total ?? 0);
                return [
                    'label' => (string)($map[$r->id] ?? "Usuário {$r->id}"),
                    'kmInicial' => (int)($r->km_inicial ?? 0),
                    'kmFinal' => (int)($r->km_final ?? 0),
                    'kmPercorrido' => $kmPercorrido,
                ];
            })->values();
        } else {
            // Agrupar por veículo: somar todos os KM que cada veículo percorreu
            $rows = $q->select(
                    'vehicle_id as id',
                    DB::raw('SUM(km_retorno - km_saida) as km_percorrido_total'),
                    DB::raw('MIN(km_saida) as km_inicial'),
                    DB::raw('MAX(km_retorno) as km_final')
                )
                ->groupBy('vehicle_id')
                ->get();

            $veiculos = DB::table('veiculos')->select('id','placa','marca','modelo')->get();
            $map = $veiculos->mapWithKeys(function($v){
                $label = trim(($v->placa ?? '') . ' - ' . (($v->marca ?? '') . ' ' . ($v->modelo ?? '')));
                return [$v->id => $label];
            });

            $dados = $rows->map(function($r) use ($map) {
                $kmPercorrido = (int)($r->km_percorrido_total ?? 0);
                return [
                    'label' => (string)($map[$r->id] ?? "Veículo {$r->id}"),
                    'kmInicial' => (int)($r->km_inicial ?? 0),
                    'kmFinal' => (int)($r->km_final ?? 0),
                    'kmPercorrido' => $kmPercorrido,
                ];
            })->values();
        }

        // Ordenar desc por KM percorrido
        $dados = $dados->sortByDesc('kmPercorrido')->values();

        return response()->json(['success' => true, 'data' => $dados]);
    }
}


