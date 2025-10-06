<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RelatorioFrotaController extends Controller
{
    public function conferenciaNf()
    {
        return view('frota.relatorios.conferencia-nf');
    }

    public function listarLotes(Request $request)
    {
        $q = \DB::table('nf_abastecimento_lotes')->orderByDesc('created_at');
        if ($request->data_inicio) $q->whereDate('created_at', '>=', $request->data_inicio);
        if ($request->data_fim) $q->whereDate('created_at', '<=', $request->data_fim);
        if ($request->numero_nf) $q->where('numero_nf', 'like', '%'.$request->numero_nf.'%');
        return $q->get();
    }

    public function detalhesLote(int $id)
    {
        $lote = \DB::table('nf_abastecimento_lotes')->where('id', $id)->first();
        if (!$lote) { return response()->json(['ok'=>false,'message'=>'Lote não encontrado'], 404); }
        
        $itens = \DB::table('nf_abastecimento_itens as nfi')
            ->leftJoin('veiculos as v', 'nfi.veiculo_id', '=', 'v.id')
            ->select('nfi.*', 'v.placa')
            ->where('nfi.lote_id', $id)
            ->get();
            
        return response()->json(['ok'=>true, 'lote'=>$lote, 'itens'=>$itens]);
    }
}


