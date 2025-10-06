<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OrdemServicoController extends Controller
{
    /**
     * Segurança básica: exige autenticação e a permissão específica.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:ord_serv');
    }

    /**
     * Página principal da Ordem de Serviço (apenas visualização/print).
     */
    public function index()
    {
        return view('documentos-dp.ordem-servico-index');
    }

    /**
     * Persiste a Ordem de Serviço na tabela ordens_servico
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'data_os'        => ['required','date'],
            'descricao'      => ['required','string'],
            'endereco'       => ['nullable','string','max:255'],
            'cidade'         => ['nullable','string','max:120'],
            'telefone'       => ['nullable','string','max:20'],
            'cpf_cnpj'       => ['nullable','string','max:20'],
            'cep'            => ['nullable','string','max:10'],
            'estado'         => ['nullable','string','max:2'],
            'funcionario_id' => ['nullable','integer'],
            'observacoes'    => ['nullable','string'],
        ]);

        $estado = isset($validated['estado']) ? strtoupper($validated['estado']) : null;
        $numeroGerado = $this->gerarNumeroOs($validated['data_os']);

        DB::table('ordens_servico')->insert([
            'user_id'        => Auth::id(),
            'data_os'        => $validated['data_os'],
            'numero_os'      => $numeroGerado,
            'descricao'      => $validated['descricao'],
            'endereco'       => $validated['endereco'] ?? null,
            'cidade'         => $validated['cidade'] ?? null,
            'telefone'       => $validated['telefone'] ?? null,
            'cpf_cnpj'       => $validated['cpf_cnpj'] ?? null,
            'cep'            => $validated['cep'] ?? null,
            'estado'         => $estado,
            'funcionario_id' => $validated['funcionario_id'] ?? null,
            'observacoes'    => $validated['observacoes'] ?? null,
        ]);

        return redirect()
            ->route('documentos-dp.ordem-servico')
            ->with('success', 'Ordem de Serviço registrada com sucesso.');
    }

    /**
     * Tela de Nova OS (formulário)
     */
    public function nova()
    {
        $numeroOs = $this->gerarNumeroOs(now()->toDateString());
        return view('documentos-dp.ordem-servico', compact('numeroOs'));
    }

    /**
     * Lista O.S. com filtros por período (data inicial/final) OU por número da O.S.
     * Mantém compatibilidade com o parâmetro antigo 'data' (filtra por um único dia).
     */
    public function lista(Request $request)
    {
        $dataIni  = $request->query('data_ini');
        $dataFim  = $request->query('data_fim');
        $numeroOs = trim((string) $request->query('numero_os', ''));

        // Compatibilidade com o filtro antigo (?data=YYYY-MM-DD)
        $dataUnica = $request->query('data');

        // Comportamento padrão: se nada for enviado, usa o dia atual
        if (!$dataIni && !$dataFim && !$numeroOs && !$dataUnica) {
            $dataIni = $dataFim = date('Y-m-d');
        }

        $query = DB::table('ordens_servico as os')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'os.funcionario_id')
            ->select('os.id','os.numero_os','os.data_os','os.cidade','os.estado','os.telefone','f.nome as funcionario');

        if ($numeroOs !== '') {
            // Quando informado número da O.S., prioriza a busca por número
            $query->where('os.numero_os', 'like', '%' . $numeroOs . '%');
        } elseif ($dataUnica) {
            $query->whereDate('os.data_os', $dataUnica);
        } else {
            if ($dataIni) {
                $query->whereDate('os.data_os', '>=', $dataIni);
            }
            if ($dataFim) {
                $query->whereDate('os.data_os', '<=', $dataFim);
            }
        }

        $registros = $query->orderByDesc('os.id')->get();

        return view('documentos-dp.ordem-servico-lista', [
            'registros'  => $registros,
            'data_ini'   => $dataIni,
            'data_fim'   => $dataFim,
            'numero_os'  => $numeroOs,
            'data'       => $dataUnica, // ainda usado na view para retrocompatibilidade
        ]);
    }

    /**
     * Lista as O.S. vinculadas a um funcionário (JSON)
     */
    public function listarPorFuncionario(int $funcionarioId)
    {
        $dados = DB::table('ordens_servico')
            ->select('id','numero_os','data_os','cidade','estado','telefone')
            ->where('funcionario_id', $funcionarioId)
            ->orderByDesc('data_os')
            ->limit(500)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $dados
        ]);
    }

    /**
     * Retorna os dados de uma O.S. em JSON
     */
    public function show(int $id)
    {
        $os = DB::table('ordens_servico as os')
            ->leftJoin('funcionarios as f', 'f.id', '=', 'os.funcionario_id')
            ->select('os.*', 'f.nome as funcionario', 'f.cpf as funcionario_cpf')
            ->where('os.id', $id)
            ->first();

        if (!$os) {
            return response()->json(['success' => false, 'message' => 'O.S. não encontrada'], 404);
        }

        return response()->json(['success' => true, 'data' => $os]);
    }
    /**
     * Busca funcionários por prefixo (3+ letras)
     */
    public function buscarFuncionarios(Request $request)
    {
        // RESPOSTA FIXA TEMPORÁRIA PARA TESTAR
        return response()->json([
            'success' => true, 
            'data' => [
                ['id' => 1, 'nome' => 'Wesley Silva'],
                ['id' => 2, 'nome' => 'Wesley Santos'],
                ['id' => 3, 'nome' => 'Weslley Costa']
            ]
        ]);
        
        /*
        $q = trim((string) $request->query('q', ''));
        if (strlen($q) < 3) {
            return response()->json(['success' => true, 'data' => []]);
        }

        try {
            // Verifica se a tabela funcionarios existe
            if (!Schema::hasTable('funcionarios')) {
                \Log::error('Tabela funcionarios não existe');
                return response()->json(['success' => true, 'data' => []]);
            }

            $result = collect();
            
            // Tenta buscar diretamente
            $result = DB::table('funcionarios')
                ->select('id', 'nome')
                ->where('nome', 'like', '%' . $q . '%')
                ->whereNotNull('nome')
                ->where('nome', '!=', '')
                ->orderBy('nome')
                ->limit(30)
                ->get();

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            \Log::error('Erro buscarFuncionarios OS: '.$e->getMessage(), [
                'query' => $q,
                'trace' => $e->getTraceAsString()
            ]);
            // Sempre retorna sucesso para não quebrar o autocomplete
            return response()->json(['success' => true, 'data' => [], 'error' => $e->getMessage()]);
        }
        */
    }


    /**
     * Gera número único da OS no formato OS-YYYYMMDD-XXXX (sequencial por dia)
     */
    private function gerarNumeroOs(string $dataOs): string
    {
        $countHoje = DB::table('ordens_servico')
            ->whereDate('data_os', $dataOs)
            ->count();

        $sequencia = $countHoje + 1;
        return 'OS-' . date('Ymd', strtotime($dataOs)) . '-' . str_pad((string)$sequencia, 4, '0', STR_PAD_LEFT);
    }
}


