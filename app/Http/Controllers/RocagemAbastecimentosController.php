<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class RocagemAbastecimentosController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:abas_roca');
    }

    public function index()
    {
        $perfil = optional(Auth::user()->profile)->name;
        $isAdmin = ($perfil === 'Admin' || $perfil === 'Gestão de Frotas' || $perfil === 'Gestao de Frotas' || (Auth::user() && method_exists(Auth::user(),'temPermissao') && (Auth::user()->temPermissao('Gestão de Frotas') || Auth::user()->temPermissao('Gestao de Frotas'))));
        return view('rocagem.abastecimentos', compact('isAdmin'));
    }

    // API: Listar abastecimentos
    public function listar(Request $request)
    {
        $query = DB::table('rocagem_abastecimentos as ra')
            ->join('rocagem_locais as rl', 'ra.local_id', '=', 'rl.id')
            ->join('users as u', 'ra.user_id', '=', 'u.id')
            ->select(
                'ra.*',
                'rl.nome as local_rocagem',
                'u.name as user_name'
            )
            ->orderBy('ra.data', 'desc')
            ->orderBy('ra.id', 'desc');

        // Se não for admin nem Gestão de Frotas, mostrar apenas os próprios registros
        $perfil = optional(Auth::user()->profile)->name;
        $isGestor = ($perfil === 'Gestão de Frotas' || $perfil === 'Gestao de Frotas' || (Auth::user() && method_exists(Auth::user(),'temPermissao') && (Auth::user()->temPermissao('Gestão de Frotas') || Auth::user()->temPermissao('Gestao de Frotas'))));
        if ($perfil !== 'Admin' && !$isGestor) {
            $query->where('ra.user_id', Auth::id());
        }

        // Filtros opcionais por data
        if ($request->filled('data_inicio')) {
            $query->whereDate('ra.data', '>=', $request->input('data_inicio'));
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('ra.data', '<=', $request->input('data_fim'));
        }

        $abastecimentos = $query->get();

        // Calcular preço por litro se não estiver preenchido
        foreach ($abastecimentos as $abast) {
            if (!$abast->preco_litro && $abast->litros > 0) {
                $abast->preco_litro = $abast->valor / $abast->litros;
            }
        }

        return response()->json($abastecimentos);
    }

    // API: Salvar abastecimento
    public function salvar(Request $request)
    {
        $validated = $request->validate([
            'local_id' => 'required|integer|exists:rocagem_locais,id',
            'data' => 'required|date',
            'litros' => 'required|numeric|min:0.01',
            'valor' => 'required|numeric|min:0.01',
            'posto' => 'nullable|string|max:255',
        ]);

        $validated['user_id'] = Auth::id();
        
        // Calcular preço por litro
        if ($validated['litros'] > 0) {
            $validated['preco_litro'] = $validated['valor'] / $validated['litros'];
        }

        $id = DB::table('rocagem_abastecimentos')->insertGetId($validated);

        return response()->json([
            'success' => true,
            'message' => 'Abastecimento registrado com sucesso!',
            'id' => $id
        ]);
    }

    // API: Atualizar abastecimento
    public function atualizar(Request $request, $id)
    {
        $validated = $request->validate([
            'local_id' => 'required|integer|exists:rocagem_locais,id',
            'data' => 'required|date',
            'litros' => 'required|numeric|min:0.01',
            'valor' => 'required|numeric|min:0.01',
            'posto' => 'nullable|string|max:255',
        ]);

        // Calcular preço por litro
        if ($validated['litros'] > 0) {
            $validated['preco_litro'] = $validated['valor'] / $validated['litros'];
        }

        $validated['updated_at'] = now();

        DB::table('rocagem_abastecimentos')
            ->where('id', $id)
            ->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Abastecimento atualizado com sucesso!'
        ]);
    }

    // API: Excluir abastecimento
    public function excluir($id)
    {
        DB::table('rocagem_abastecimentos')->where('id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Abastecimento excluído com sucesso!'
        ]);
    }

    // API: Listar locais ativos
    public function listarLocais()
    {
        $locais = DB::table('rocagem_locais')
            ->where('ativo', 1)
            ->orderBy('nome')
            ->get();

        return response()->json($locais);
    }
}


