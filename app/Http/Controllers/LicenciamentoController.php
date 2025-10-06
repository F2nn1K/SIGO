<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LicenciamentoController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'can:licens']);
    }

    public function index()
    {
        return view('frota.licenciamento');
    }

    /**
     * Lista veículos (placa/modelo) para o select da tela.
     */
    public function veiculos(Request $request)
    {
        $q = DB::table('veiculos')
            ->select('id','placa','marca','modelo','ano')
            ->orderBy('placa');

        if ($request->filled('search')) {
            $s = trim($request->query('search'));
            $q->where(function($qq) use ($s){
                $qq->where('placa','like',"%$s%")
                   ->orWhere('marca','like',"%$s%")
                   ->orWhere('modelo','like',"%$s%");
            });
        }

        return response()->json(['success' => true, 'data' => $q->limit(500)->get()]);
    }

    /**
     * Retorna o status de licenciamento do veículo e calcula próximo pagamento.
     */
    public function status(int $veiculoId)
    {
        $veiculo = DB::table('veiculos')->where('id',$veiculoId)->first();
        if (!$veiculo) {
            return response()->json(['success' => false, 'message' => 'Veículo não encontrado'], 404);
        }

        $ultimo = DB::table('veiculo_licenciamentos')
            ->where('veiculo_id', $veiculoId)
            ->orderByDesc('ano_exercicio')
            ->orderByDesc('data_pagamento')
            ->first();

        $anoAtual = (int) date('Y');
        $pagoEsteAno = false;
        $proximoPagamento = null;

        if ($ultimo) {
            if (!empty($ultimo->ano_exercicio) && (int)$ultimo->ano_exercicio === $anoAtual) {
                $pagoEsteAno = true;
            } elseif (!empty($ultimo->data_pagamento) && (int)date('Y', strtotime($ultimo->data_pagamento)) === $anoAtual) {
                $pagoEsteAno = true;
            }

            if (!empty($ultimo->data_pagamento)) {
                $proximoPagamento = date('Y-m-d', strtotime('+1 year', strtotime($ultimo->data_pagamento)));
            } else {
                $anoBase = max((int)($ultimo->ano_exercicio ?: $anoAtual), $anoAtual);
                $proximoPagamento = $anoBase . '-12-31'; // estimado (ajuste conforme calendário local)
            }
        } else {
            $proximoPagamento = $anoAtual . '-12-31';
        }

        return response()->json([
            'success' => true,
            'veiculo' => $veiculo,
            'ultimo' => $ultimo,
            'pago_este_ano' => $pagoEsteAno,
            'proximo_pagamento' => $proximoPagamento,
        ]);
    }

    /**
     * Registra/atualiza pagamento do licenciamento e anexa comprovante
     */
    public function store(Request $request)
    {
        $request->validate([
            'veiculo_id'     => ['required','integer'],
            'ano_exercicio'  => ['required','integer','digits:4'],
            'data_pagamento' => ['nullable','date'],
            'valor'          => ['nullable','string'],
            'observacoes'    => ['nullable','string'],
            'comprovante'    => ['nullable','file','max:8192'],
        ]);

        $valor = null;
        if ($request->filled('valor')) {
            $valorStr = (string) $request->input('valor');
            $valor = (float) str_replace(',', '.', str_replace('.', '', $valorStr));
        }

        $blob = null; $mime = null; $nome = null; $tamanho = null;
        if ($request->hasFile('comprovante')) {
            $file = $request->file('comprovante');
            $blob = file_get_contents($file->getRealPath());
            $mime = $file->getMimeType();
            $nome = $file->getClientOriginalName();
            $tamanho = $file->getSize();
        }

        DB::table('veiculo_licenciamentos')->insert([
            'veiculo_id'        => (int)$request->input('veiculo_id'),
            'ano_exercicio'     => (int)$request->input('ano_exercicio'),
            'data_pagamento'    => $request->input('data_pagamento') ?: null,
            'valor'             => $valor,
            'observacoes'       => $request->input('observacoes') ?: null,
            'comprovante'       => $blob,
            'comprovante_mime'  => $mime,
            'comprovante_nome'  => $nome,
            'comprovante_tamanho' => $tamanho,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        return redirect()->route('frota.licenciamento')
            ->with('success', 'Licenciamento registrado com sucesso.');
    }
}


