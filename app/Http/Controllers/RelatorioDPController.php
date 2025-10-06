<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Carbon\Carbon;

class RelatorioDPController extends Controller
{
    /**
     * Construtor - aplicar middleware de autenticação e permissão
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:rel_dp');
    }

    /**
     * Exibe a página do relatório DP
     */
    public function index()
    {
        return view('relatorios.dp');
    }

    /**
     * Gera o relatório de documentos DP
     */
    public function gerarRelatorio(Request $request)
    {
        try {
            $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'tipo_relatorio' => 'required|in:funcionarios,documentos,todos'
            ]);

            $dataInicio = $request->data_inicio;
            $dataFim = $request->data_fim;
            $tipoRelatorio = $request->tipo_relatorio;

            $dados = [];

            // Buscar dados dos funcionários e seus documentos
            if ($tipoRelatorio === 'funcionarios' || $tipoRelatorio === 'todos') {
                $funcionarios = DB::table('funcionarios')
                    ->select('id', 'nome', 'cpf', 'sexo', 'funcao', 'status', 'created_at')
                    ->whereDate('created_at', '>=', $dataInicio)
                    ->whereDate('created_at', '<=', $dataFim)
                    ->orderBy('created_at', 'desc')
                    ->get();

                $dados['funcionarios'] = $funcionarios;
            }

            // Buscar dados dos documentos anexados
            if ($tipoRelatorio === 'documentos' || $tipoRelatorio === 'todos') {
                $documentos = DB::table('funcionarios_documentos as fd')
                    ->join('funcionarios as f', 'fd.funcionario_id', '=', 'f.id')
                    ->select(
                        'f.nome as funcionario_nome',
                        'f.cpf',
                        'f.funcao',
                        'fd.tipo_documento',
                        'fd.arquivo_nome',
                        'fd.arquivo_tamanho',
                        'fd.created_at'
                    )
                    ->whereDate('fd.created_at', '>=', $dataInicio)
                    ->whereDate('fd.created_at', '<=', $dataFim)
                    ->orderBy('fd.created_at', 'desc')
                    ->get();

                $dados['documentos'] = $documentos;

                // Buscar documentos de atestados
                $atestados = DB::table('funcionarios_atestados as fa')
                    ->join('funcionarios as f', 'fa.funcionario_id', '=', 'f.id')
                    ->select(
                        'f.nome as funcionario_nome',
                        'f.cpf',
                        'f.funcao',
                        'fa.data_inicio',
                        'fa.data_fim',
                        'fa.arquivo_nome',
                        'fa.created_at'
                    )
                    ->whereDate('fa.created_at', '>=', $dataInicio)
                    ->whereDate('fa.created_at', '<=', $dataFim)
                    ->orderBy('fa.created_at', 'desc')
                    ->get();

                $dados['atestados'] = $atestados;

                // Buscar documentos de advertências
                $advertencias = DB::table('funcionarios_advertencias as fad')
                    ->join('funcionarios as f', 'fad.funcionario_id', '=', 'f.id')
                    ->select(
                        'f.nome as funcionario_nome',
                        'f.cpf',
                        'f.funcao',
                        'fad.data_advertencia',
                        'fad.motivo',
                        'fad.arquivo_nome',
                        'fad.created_at'
                    )
                    ->whereDate('fad.created_at', '>=', $dataInicio)
                    ->whereDate('fad.created_at', '<=', $dataFim)
                    ->orderBy('fad.created_at', 'desc')
                    ->get();

                $dados['advertencias'] = $advertencias;

                // Buscar documentos de décimo terceiro
                $decimos = DB::table('funcionarios_decimo as fd')
                    ->join('funcionarios as f', 'fd.funcionario_id', '=', 'f.id')
                    ->select(
                        'f.nome as funcionario_nome',
                        'f.cpf',
                        'f.funcao',
                        'fd.ano_referencia',
                        'fd.parcela',
                        'fd.valor_bruto',
                        'fd.arquivo_nome',
                        'fd.created_at'
                    )
                    ->whereDate('fd.created_at', '>=', $dataInicio)
                    ->whereDate('fd.created_at', '<=', $dataFim)
                    ->orderBy('fd.created_at', 'desc')
                    ->get();

                $dados['decimos'] = $decimos;

                // Buscar documentos de rescisão
                $rescisoes = DB::table('funcionarios_rescisao as fr')
                    ->join('funcionarios as f', 'fr.funcionario_id', '=', 'f.id')
                    ->select(
                        'f.nome as funcionario_nome',
                        'f.cpf',
                        'f.funcao',
                        'fr.data_rescisao',
                        'fr.tipo_rescisao',
                        'fr.valor_total',
                        'fr.arquivo_nome',
                        'fr.created_at'
                    )
                    ->whereDate('fr.created_at', '>=', $dataInicio)
                    ->whereDate('fr.created_at', '<=', $dataFim)
                    ->orderBy('fr.created_at', 'desc')
                    ->get();

                $dados['rescisoes'] = $rescisoes;
            }

            return response()->json([
                'success' => true,
                'dados' => $dados,
                'periodo' => [
                    'inicio' => Carbon::parse($dataInicio)->format('d/m/Y'),
                    'fim' => Carbon::parse($dataFim)->format('d/m/Y')
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório DP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar funcionários por nome ou CPF
     */
    public function buscarFuncionarios(Request $request)
    {
        try {
            $termo = $request->input('termo');
            
            if (strlen($termo) < 3) {
                return response()->json([]);
            }
            
            // Buscar funcionários por nome ou CPF
            $funcionarios = DB::table('funcionarios')
                ->where('nome', 'LIKE', "%{$termo}%")
                ->orWhere('cpf', 'LIKE', "%{$termo}%")
                ->select('id', 'nome', 'cpf', 'sexo', 'funcao', 'status', 'created_at')
                ->orderBy('nome')
                ->limit(20)
                ->get();
                
            return response()->json($funcionarios);
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar funcionários: ' . $e->getMessage());
            return response()->json([], 500);
        }
    }
    
    /**
     * Obter dados de um funcionário específico
     */
    public function obterFuncionario($id)
    {
        try {
            $funcionario = DB::table('funcionarios')
                ->where('id', $id)
                ->first();
                
            if (!$funcionario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }
            
            return response()->json($funcionario);
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Exporta o relatório para Excel
     */
    public function exportarExcel(Request $request)
    {
        try {
            $request->validate([
                'data_inicio' => 'required|date',
                'data_fim' => 'required|date|after_or_equal:data_inicio',
                'tipo_relatorio' => 'required|in:funcionarios,documentos,todos'
            ]);

            // Reutilizar a lógica do gerarRelatorio
            $dadosResponse = $this->gerarRelatorio($request);
            $dadosArray = json_decode($dadosResponse->getContent(), true);

            if (!$dadosArray['success']) {
                return response()->json($dadosArray, 500);
            }

            $dados = $dadosArray['dados'];
            $periodo = $dadosArray['periodo'];

            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Relatório DP');

            // Cabeçalho
            $sheet->setCellValue('A1', 'RELATÓRIO DEPARTAMENTO PESSOAL');
            $sheet->setCellValue('A2', 'Período: ' . $periodo['inicio'] . ' a ' . $periodo['fim']);
            $sheet->setCellValue('A3', 'Gerado em: ' . now()->format('d/m/Y H:i:s'));

            $linha = 5;

            // Exportar funcionários se existir
            if (isset($dados['funcionarios']) && count($dados['funcionarios']) > 0) {
                $sheet->setCellValue('A' . $linha, 'FUNCIONÁRIOS CADASTRADOS');
                $linha += 2;

                $sheet->setCellValue('A' . $linha, 'Nome');
                $sheet->setCellValue('B' . $linha, 'CPF');
                $sheet->setCellValue('C' . $linha, 'Sexo');
                $sheet->setCellValue('D' . $linha, 'Função');
                $sheet->setCellValue('E' . $linha, 'Status');
                $sheet->setCellValue('F' . $linha, 'Data Cadastro');
                $linha++;

                foreach ($dados['funcionarios'] as $funcionario) {
                    $sheet->setCellValue('A' . $linha, $funcionario->nome);
                    $sheet->setCellValue('B' . $linha, $funcionario->cpf);
                    $sheet->setCellValue('C' . $linha, $funcionario->sexo);
                    $sheet->setCellValue('D' . $linha, $funcionario->funcao);
                    $sheet->setCellValue('E' . $linha, $funcionario->status);
                    $sheet->setCellValue('F' . $linha, Carbon::parse($funcionario->created_at)->format('d/m/Y H:i'));
                    $linha++;
                }
                $linha += 2;
            }

            // Exportar documentos se existir
            if (isset($dados['documentos']) && count($dados['documentos']) > 0) {
                $sheet->setCellValue('A' . $linha, 'DOCUMENTOS ANEXADOS');
                $linha += 2;

                $sheet->setCellValue('A' . $linha, 'Funcionário');
                $sheet->setCellValue('B' . $linha, 'CPF');
                $sheet->setCellValue('C' . $linha, 'Função');
                $sheet->setCellValue('D' . $linha, 'Tipo Documento');
                $sheet->setCellValue('E' . $linha, 'Arquivo');
                $sheet->setCellValue('F' . $linha, 'Data Anexo');
                $linha++;

                foreach ($dados['documentos'] as $documento) {
                    $sheet->setCellValue('A' . $linha, $documento->funcionario_nome);
                    $sheet->setCellValue('B' . $linha, $documento->cpf);
                    $sheet->setCellValue('C' . $linha, $documento->funcao);
                    $sheet->setCellValue('D' . $linha, $documento->tipo_documento);
                    $sheet->setCellValue('E' . $linha, $documento->arquivo_nome);
                    $sheet->setCellValue('F' . $linha, Carbon::parse($documento->created_at)->format('d/m/Y H:i'));
                    $linha++;
                }
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'relatorio-dp-' . now()->format('Y-m-d-H-i-s') . '.xlsx';
            $tempFile = tempnam(sys_get_temp_dir(), $filename);
            $writer->save($tempFile);

            return response()->download($tempFile, $filename)->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar relatório DP: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao exportar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    public function funcionariosAtivosInativos(\Illuminate\Http\Request $request)
    {
        try {
            $q = \DB::table('funcionarios');

            // Filtro de status com normalização para cobrir variações (ex.: ativo -> trabalhando, férias -> ferias)
            if ($request->filled('status')) {
                $statusReq = strtolower(trim((string)$request->input('status')));
                $map = [
                    'trabalhando' => ['trabalhando','ativo'],
                    'afastado'    => ['afastado'],
                    'ferias'      => ['ferias','férias'],
                    'demitido'    => ['demitido','inativo'],
                ];
                $lista = $map[$statusReq] ?? [$statusReq];
                // Aplica comparando em lower-case
                $q->where(function($w) use ($lista){
                    foreach ($lista as $st) {
                        $w->orWhereRaw('LOWER(status) = ?', [strtolower($st)]);
                    }
                });
            }
            // Filtrar pelo período de atualização (quando o status foi alterado)
            if ($request->filled('data_inicio')) {
                $q->whereDate('updated_at', '>=', $request->input('data_inicio'));
            }
            if ($request->filled('data_fim')) {
                $q->whereDate('updated_at', '<=', $request->input('data_fim'));
            }

            $dados = $q->orderBy('nome')->get(['id','nome','cpf','sexo','funcao','status','observacoes','created_at','updated_at']);
            return response()->json(['success' => true, 'data' => $dados]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Erro ao gerar relatório: '.$e->getMessage()], 500);
        }
    }
}
