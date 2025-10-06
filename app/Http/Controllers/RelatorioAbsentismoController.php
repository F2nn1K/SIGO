<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioAbsentismoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:rel_abse');
    }

    public function index()
    {
        return view('relatorios.absenteismo');
    }

    public function data(Request $request)
    {
        $q = DB::table('funcionarios_atestados as fa')
            ->join('funcionarios as f', 'fa.funcionario_id', '=', 'f.id')
            ->select(
                'fa.id',
                'fa.funcionario_id',
                'f.nome as funcionario_nome',
                'fa.data_atestado',
                'fa.dias_afastamento',
                'fa.tipo_atestado',
                'fa.created_at'
            )
            ->orderByDesc('fa.data_atestado');

        if ($request->filled('data_ini')) {
            $q->whereDate('fa.data_atestado', '>=', $request->input('data_ini'));
        }
        if ($request->filled('data_fim')) {
            $q->whereDate('fa.data_atestado', '<=', $request->input('data_fim'));
        }
        // Filtro por funcionário (id tem prioridade)
        if ($request->filled('func_id')) {
            $q->where('fa.funcionario_id', (int) $request->input('func_id'));
        } elseif ($request->filled('q')) {
            $termo = trim((string) $request->input('q'));
            if ($termo !== '') {
                // Prefixo: começa com as letras digitadas
                $q->where('f.nome', 'like', $termo.'%');
            }
        }

        // Filtro por Centro de Custo, se a coluna existir nas tabelas
        if ($request->filled('centro_custo_id')) {
            $ccId = (int) $request->input('centro_custo_id');
            if ($ccId > 0) {
                try {
                    // Tente por coluna em funcionarios
                    $q->whereExists(function($sub) use ($ccId){
                        $sub->from('funcionarios as fx')
                            ->select(DB::raw(1))
                            ->whereColumn('fx.id','fa.funcionario_id')
                            ->where('fx.centro_custo_id', $ccId);
                    });
                } catch (\Throwable $e) {
                    // Se não existir, ignora o filtro para não quebrar
                }
            }
        }

        $rows = $q->limit(1000)->get();
        return response()->json(['success' => true, 'data' => $rows]);
    }
}


