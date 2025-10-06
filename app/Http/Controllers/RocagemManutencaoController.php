<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class RocagemManutencaoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Exibir somente para quem tem a permissão específica
        $this->middleware('can:manu_roça');
    }

    public function index()
    {
        return view('rocagem.manutencao');
    }

    // ===== API: Equipamentos (lista) =====
    public function equipamentos(Request $request)
    {
        try {
            if (Schema::hasTable('rocagem_equipamentos')) {
                $q = DB::table('rocagem_equipamentos')
                    ->select('id','codigo','nome','horas_uso','status');
                if ($request->boolean('only_usable')) {
                    // Considera status diferente de 'inativo' como utilizável
                    $q->whereNotIn('status', ['inativo','manutencao']);
                }
                return response()->json($q->orderBy('nome')->get());
            }
        } catch (\Throwable $e) { /* ignore */ }
        return response()->json([]);
    }

    // ===== API: Manutenções (lista) =====
    public function listar(Request $request)
    {
        try {
            if (Schema::hasTable('rocagem_manutencoes')) {
                $q = DB::table('rocagem_manutencoes as rm')
                    ->leftJoin('rocagem_equipamentos as re', 'rm.equip_id', '=', 're.id')
                    ->leftJoin('users as u', 'rm.user_id', '=', 'u.id')
                    ->select(
                        'rm.*',
                        're.nome as equip_nome',
                        're.codigo as equip_codigo',
                        'u.name as user_name'
                    )
                    ->orderByDesc('rm.data');

                if ($request->filled('data_inicio')) {
                    $q->whereDate('rm.data', '>=', $request->input('data_inicio'));
                }
                if ($request->filled('data_fim')) {
                    $q->whereDate('rm.data', '<=', $request->input('data_fim'));
                }

                // Não-admins: retornam só próprios (se quiser, adaptar depois)
                if (optional(Auth::user()->profile)->name !== 'Admin') {
                    $q->where('rm.user_id', Auth::id());
                }

                return response()->json($q->get());
            }
        } catch (\Throwable $e) { /* ignore */ }
        return response()->json([]);
    }

    // ===== API: Manutenções (salvar) =====
    public function salvar(Request $request)
    {
        if (!Schema::hasTable('rocagem_manutencoes')) {
            return response()->json(['message' => 'Tabela rocagem_manutencoes não encontrada'], 422);
        }
        $data = $request->validate([
            'equip_id' => ['nullable','integer'],
            'data' => ['required','date'],
            'tipo' => ['required','in:preventiva,corretiva'],
            'descricao' => ['required','string','max:200'],
            'horas' => ['required','integer','min:0'],
            'custo' => ['nullable','numeric','min:0'],
            'oficina' => ['nullable','string','max:255'],
            'proxima_horas' => ['nullable','integer','min:0'],
        ]);
        $data['user_id'] = Auth::id();
        $data['created_at'] = now();
        $data['updated_at'] = now();

        // Se equipamento não vier e a coluna for NOT NULL, usar/gerar um placeholder
        try {
            if (empty($data['equip_id']) && Schema::hasTable('rocagem_equipamentos')) {
                $placeholderId = DB::table('rocagem_equipamentos')->where('nome', 'SEM EQUIPAMENTO')->value('id');
                if (!$placeholderId) {
                    $placeholderId = DB::table('rocagem_equipamentos')->insertGetId([
                        'codigo' => 'SEM',
                        'nome' => 'SEM EQUIPAMENTO',
                        'horas_uso' => 0,
                        'status' => 'ativo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $data['equip_id'] = $placeholderId;
            }
        } catch (\Throwable $e) { /* ignore */ }

        try {
            $id = DB::table('rocagem_manutencoes')->insertGetId($data);
            return response()->json(['success' => true, 'id' => $id]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao salvar manutenção: '.$e->getMessage()], 500);
        }
    }

    // ===== API: Manutenções (atualizar) =====
    public function atualizar(Request $request, int $id)
    {
        if (!Schema::hasTable('rocagem_manutencoes')) {
            return response()->json(['message' => 'Tabela rocagem_manutencoes não encontrada'], 422);
        }
        $data = $request->validate([
            'equip_id' => ['nullable','integer'],
            'data' => ['required','date'],
            'tipo' => ['required','in:preventiva,corretiva'],
            'descricao' => ['required','string','max:200'],
            'horas' => ['required','integer','min:0'],
            'custo' => ['nullable','numeric','min:0'],
            'oficina' => ['nullable','string','max:255'],
            'proxima_horas' => ['nullable','integer','min:0'],
        ]);
        $data['updated_at'] = now();
        // Garantir placeholder quando não vier equip_id
        try {
            if (empty($data['equip_id']) && Schema::hasTable('rocagem_equipamentos')) {
                $placeholderId = DB::table('rocagem_equipamentos')->where('nome', 'SEM EQUIPAMENTO')->value('id');
                if (!$placeholderId) {
                    $placeholderId = DB::table('rocagem_equipamentos')->insertGetId([
                        'codigo' => 'SEM',
                        'nome' => 'SEM EQUIPAMENTO',
                        'horas_uso' => 0,
                        'status' => 'ativo',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                $data['equip_id'] = $placeholderId;
            }
        } catch (\Throwable $e) { /* ignore */ }

        try {
            DB::table('rocagem_manutencoes')->where('id', $id)->update($data);
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao atualizar manutenção: '.$e->getMessage()], 500);
        }
    }

    // ===== API: Manutenções (excluir) =====
    public function excluir(int $id)
    {
        if (!Schema::hasTable('rocagem_manutencoes')) {
            return response()->json(['message' => 'Tabela rocagem_manutencoes não encontrada'], 422);
        }
        DB::table('rocagem_manutencoes')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }
}


