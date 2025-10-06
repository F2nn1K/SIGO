<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Abastecimento;

class NFAbastecimentoController extends Controller
{
    public function index()
    {
        return view('frota.nf-abastecimento.index');
    }

    public function finalizar(Request $request)
    {
        $data = $request->validate([
            'abastecimento_ids' => ['required','array','min:1'],
            'abastecimento_ids.*' => ['integer','exists:abastecimentos,id'],
            'numero_nf' => ['required','string','max:50'],
            'cupons' => ['required','array'],
        ]);

        // Buscar tanto da frota quanto da roçagem
        $frota = Abastecimento::whereIn('id', $data['abastecimento_ids'])->get();
        $rocagem = collect();
        try {
            if (Schema::hasTable('rocagem_abastecimentos')) {
                $rocagem = DB::table('rocagem_abastecimentos')->whereIn('id', $data['abastecimento_ids'])->get();
            }
        } catch (\Throwable $e) { /* ignore */ }
        $rows = $frota->concat($rocagem);
        if ($rows->isEmpty()) {
            return response()->json(['ok'=>false,'message'=>'Nenhum abastecimento encontrado.'], 422);
        }

        // Validar que cada abastecimento possui cupom
        foreach ($rows as $row) {
            if (!isset($data['cupons'][$row->id]) || trim($data['cupons'][$row->id]) === '') {
                return response()->json(['ok'=>false,'message'=>'Informe o número do cupom para todos os abastecimentos.'], 422);
            }
        }

        $totalValor = (float) $rows->sum('valor');
        $totalLitros = (float) $rows->sum('litros');
        $usuarioId = Auth::id();

        $loteId = null;
        if (Schema::hasTable('nf_abastecimento_lotes') && Schema::hasTable('nf_abastecimento_itens')) {
            DB::transaction(function () use (&$loteId, $usuarioId, $data, $rows, $totalLitros, $totalValor) {
                $loteId = DB::table('nf_abastecimento_lotes')->insertGetId([
                    'user_id' => $usuarioId,
                    'numero_cupom' => '',
                    'numero_nf' => $data['numero_nf'],
                    'total_litros' => $totalLitros,
                    'total_valor' => $totalValor,
                    'qtd_itens' => $rows->count(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                foreach ($rows as $r) {
                    $preco = (isset($r->preco_litro) && $r->preco_litro) ? (float)$r->preco_litro : (float)$r->valor / max(1, (float)$r->litros);
                    $veiculoId = property_exists($r, 'vehicle_id') ? $r->vehicle_id : null;
                    $km = property_exists($r, 'km') ? $r->km : 0;
                    DB::table('nf_abastecimento_itens')->insert([
                        'lote_id' => $loteId,
                        'abastecimento_id' => $r->id,
                        'veiculo_id' => $veiculoId,
                        'data' => $r->data,
                        'km' => $km,
                        'litros' => $r->litros,
                        'valor' => $r->valor,
                        'preco_litro' => $preco,
                        'posto' => $r->posto,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });
        }

        return response()->json([
            'ok' => true,
            'lote_id' => $loteId,
            'consolidado' => [
                'usuario_id' => $usuarioId,
                'numero_nf' => $data['numero_nf'],
                'abastecimento_ids' => $rows->pluck('id')->all(),
                'cupons' => array_map('strval', $data['cupons']),
                'resumo' => [
                    'quantidade' => $rows->count(),
                    'total_litros' => $totalLitros,
                    'total_valor' => $totalValor,
                ],
            ],
        ]);
    }

    /** Cadastra abastecimento avulso (sem veículo cadastrado) */
    public function salvarAvulso(Request $request)
    {
        $data = $request->validate([
            'tipo_combustivel' => ['required','in:gasolina,etanol,diesel,gnv'],
            'placa' => ['nullable','string','max:20'],
            'motorista' => ['nullable','string','max:150'],
            'litros' => ['required','numeric','min:0.01'],
            'valor' => ['required','numeric','min:0.01'],
            'posto' => ['nullable','string','max:150'],
            'observacoes' => ['nullable','string','max:1000'],
        ]);

        // Inserir na tabela abastecimentos sem vincular a veículo do sistema
        $row = [
            'vehicle_id' => null,
            'user_id' => auth()->id(),
            'data' => now()->toDateString(),
            'km' => 0,
            'litros' => $data['litros'],
            'valor' => $data['valor'],
            'preco_litro' => round($data['valor'] / max(0.01, (float)$data['litros']), 3),
            'tipo_combustivel' => $data['tipo_combustivel'],
            // Posto: preferir o informado; senão, fallback antigo
            'posto' => ($data['posto'] ?? null) !== null && trim($data['posto']) !== ''
                ? trim($data['posto'])
                : ($data['placa'] ? ('AVULSO - '.$data['placa']) : 'AVULSO'),
            // Observações: preferir o informado; senão, incluir motorista se houver
            'observacoes' => (isset($data['observacoes']) && trim($data['observacoes']) !== '')
                ? trim($data['observacoes'])
                : (isset($data['motorista']) && $data['motorista'] !== '' ? ('Motorista: '.$data['motorista']) : null),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        // Se existirem colunas específicas para avulso, preenche-las
        try {
            if (Schema::hasColumn('abastecimentos', 'placa_avulsa')) {
                $row['placa_avulsa'] = $data['placa'] ?: null;
            }
            if (Schema::hasColumn('abastecimentos', 'motorista')) {
                $row['motorista'] = $data['motorista'] ?: null;
            }
        } catch (\Throwable $e) { /* ignora detecção */ }

        try {
            $id = DB::table('abastecimentos')->insertGetId($row);
            return response()->json(['ok'=>true,'id'=>$id]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            if (stripos($msg, "'vehicle_id' doesn't have a default") !== false || stripos($msg, 'vehicle_id cannot be null') !== false) {
                return response()->json([
                    'ok' => false,
                    'message' => "Para salvar avulso, a coluna vehicle_id precisa aceitar NULL. Rode: ALTER TABLE abastecimentos MODIFY vehicle_id BIGINT(20) UNSIGNED NULL;"
                ], 422);
            }
            return response()->json(['ok'=>false,'message'=>'Erro ao salvar avulso: '.$msg], 500);
        }
    }
}


