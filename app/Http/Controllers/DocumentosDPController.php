<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Funcionario;
use App\Models\FuncionarioDocumento;
use Illuminate\Support\Str;

class DocumentosDPController extends Controller
{
    /**
     * Construtor do controller - SEGURANÇA
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:doc_dp')->only(['inclusao', 'store', 'downloadBLOB']);
        $this->middleware('can:vis_func')->only(['funcionarios', 'buscarFuncionario', 'listarDocumentos', 'anexarFaltantes', 'demitirFuncionario', 'listarAtestados', 'anexarAtestado', 'downloadAtestado', 'listarAdvertencias', 'aplicarAdvertencia', 'downloadAdvertencia']);
    }

    /**
     * Lista de documentos válidos (SEGURANÇA - Whitelist)
     */
    private function getDocumentosValidos()
    {
        return [
            '02 fotos 3x4',
            'Carteira de saúde atualizada com foto 3x4',
            'Encaminhamento para exame admissional',
            'Antecedente cível e criminal',
            'R.G. (identidade)',
            'CPF',
            'CNH (carteira nacional de habilitação)',
            'Título Eleitoral',
            'Comprovante de endereço (com CEP)',
            'Carteira de trabalho, frente e verso',
            'Certidão de nascimento',
            'CPF filho',
            'Carteira de vacinação (menor 07 anos)',
            'Comprovante de frequência escolar (maior 07 anos)'
        ];
    }

    /**
     * Exibe a página de inclusão de documentos DP
     */
    public function inclusao()
    {
        return view('documentos-dp.inclusao');
    }

    /**
     * Processa o formulário de inclusão de documentos - SEGURANÇA RIGOROSA
     */
    public function store(Request $request)
    {
        try {
            Log::info('Início do processo de inclusão de documentos DP', [
                'usuario_id' => Auth::id(),
                'dados_recebidos' => $request->except(['_token', 'arquivo_*'])
            ]);

            // Validação rigorosa dos dados
            $validator = Validator::make($request->all(), [
                'nome_funcionario' => 'required|string|max:255|regex:/^[a-zA-ZÀ-ÿ\s]+$/',
                'funcao' => 'required|string|max:255',
                'cpf' => 'required|string|regex:/^\d{3}\.\d{3}\.\d{3}-\d{2}$/',
                'sexo' => 'required|string|in:M,F',
                'documentos' => 'nullable|array',
                'documentos.*' => 'string|in:' . implode(',', $this->getDocumentosValidos()),
            ], [
                'nome_funcionario.required' => 'O nome do funcionário é obrigatório.',
                'nome_funcionario.regex' => 'O nome deve conter apenas letras e espaços.',
                'funcao.required' => 'A função é obrigatória.',
                'cpf.required' => 'O CPF é obrigatório.',
                'cpf.regex' => 'O CPF deve estar no formato 000.000.000-00.',
                'sexo.required' => 'O sexo é obrigatório.',
                'sexo.in' => 'O sexo deve ser Masculino (M) ou Feminino (F).',
                'documentos.*.in' => 'O documento ":input" não é válido. Recarregue a página e tente novamente.',
            ]);

            // Validação adicional com mensagem específica (apenas se existirem documentos)
            if ($request->has('documentos') && is_array($request->documentos)) {
                $documentosInvalidos = [];
                foreach ($request->documentos as $documento) {
                    if (!in_array($documento, $this->getDocumentosValidos())) {
                        $documentosInvalidos[] = $documento;
                    }
                }
                
                if (!empty($documentosInvalidos)) {
                    Log::warning('Documentos inválidos detectados', [
                        'documentos_invalidos' => $documentosInvalidos,
                        'usuario_id' => Auth::id()
                    ]);
                    
                    return back()->withErrors([
                        'documentos' => 'Documentos inválidos encontrados: ' . implode(', ', $documentosInvalidos) . '. Recarregue a página e tente novamente.'
                    ])->withInput();
                }
            }

            if ($validator->fails()) {
                Log::warning('Validação falhou em inclusão de documentos DP', [
                    'errors' => $validator->errors()->toArray(),
                    'usuario_id' => Auth::id()
                ]);
                
                return back()->withErrors($validator)->withInput();
            }

            // Verificação adicional de permissão
            if (!Auth::user()->temPermissao('doc_dp')) {
                Log::warning('Tentativa de acesso sem permissão doc_dp', [
                    'usuario_id' => Auth::id(),
                    'usuario_nome' => Auth::user()->name
                ]);
                
                abort(403, 'Acesso não autorizado');
            }

            // Sanitização dos dados
            $dadosLimpos = [
                'nome' => strip_tags(trim($request->nome_funcionario)),
                'funcao' => strip_tags(trim($request->funcao)),
                'cpf' => preg_replace('/[^0-9]/', '', strip_tags(trim($request->cpf))), // Remove pontos e hífen
                'sexo' => strip_tags(trim($request->sexo)),
                'documentos' => $request->has('documentos') ? array_map('strip_tags', $request->documentos) : []
            ];

            // Iniciar transação para garantir integridade
            DB::beginTransaction();

            // Verificar se funcionário já existe pelo CPF
            $funcionario = Funcionario::where('cpf', $dadosLimpos['cpf'])->first();
            
            if (!$funcionario) {
                // Criar novo funcionário
                $funcionario = Funcionario::create([
                    'nome' => $dadosLimpos['nome'],
                    'funcao' => $dadosLimpos['funcao'],
                    'cpf' => $dadosLimpos['cpf'],
                    'sexo' => $dadosLimpos['sexo'],
                    'status' => 'trabalhando'
                ]);
                
                Log::info('Novo funcionário criado', [
                    'funcionario_id' => $funcionario->id,
                    'nome' => $funcionario->nome,
                    'cpf' => $funcionario->cpf
                ]);
            } else {
                // Atualizar dados do funcionário existente se necessário
                $funcionario->update([
                    'nome' => $dadosLimpos['nome'],
                    'funcao' => $dadosLimpos['funcao'],
                    'sexo' => $dadosLimpos['sexo']
                ]);
                
                Log::info('Funcionário existente atualizado', [
                    'funcionario_id' => $funcionario->id,
                    'nome' => $funcionario->nome,
                    'cpf' => $funcionario->cpf
                ]);
            }

            // Processar TODOS os tipos, salvando somente os que tiverem arquivo anexado
            foreach ($this->getDocumentosValidos() as $tipoDocumento) {
                $arquivoField = $this->getArquivoFieldName($tipoDocumento);
                if ($request->hasFile($arquivoField)) {
                    $this->processarUploadArquivoBLOB($request->file($arquivoField), $funcionario->id, $tipoDocumento);
                }
            }

            // Commit da transação
            DB::commit();

            return back()->with('success', 'Documentos registrados com sucesso!');

        } catch (\Exception $e) {
            // Rollback em caso de erro
            DB::rollback();
            
            Log::error('Erro ao salvar documentos DP: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'usuario_id' => Auth::id()
            ]);

            return back()->with('error', 'Erro ao registrar documentos. Tente novamente.')->withInput();
        }
    }

    /**
     * Processa upload de arquivo e salva como BLOB no banco
     */
    private function processarUploadArquivoBLOB($arquivo, $funcionarioId, $tipoDocumento)
    {
        try {
            // Validações de segurança do arquivo
            $validator = Validator::make(['arquivo' => $arquivo], [
                'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360' // Max 15MB para MEDIUMBLOB
            ]);

            if ($validator->fails()) {
                return false;
            }

            // Ler conteúdo do arquivo para BLOB
            $arquivoConteudo = file_get_contents($arquivo->getRealPath());
            $extensao = $arquivo->getClientOriginalExtension();
            $mimeType = $arquivo->getMimeType();
            $tamanho = $arquivo->getSize();

            // Hash para integridade
            $hashArquivo = hash('sha256', $arquivoConteudo);

            // Usar o model para inserir
            $documento = FuncionarioDocumento::create([
                'funcionario_id' => $funcionarioId,
                'tipo_documento' => $tipoDocumento,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_conteudo' => $arquivoConteudo,
                'arquivo_mime_type' => $mimeType,
                'arquivo_extensao' => $extensao,
                'arquivo_tamanho' => $tamanho,
                'arquivo_hash' => $hashArquivo,
                'usuario_cadastro' => Auth::id(),
                'status' => 'pendente'
            ]);

            Log::info('Documento salvo com sucesso', [
                'documento_id' => $documento->id,
                'funcionario_id' => $funcionarioId,
                'tipo_documento' => $tipoDocumento,
                'tamanho' => $tamanho
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Erro ao salvar arquivo BLOB: ' . $e->getMessage(), [
                'funcionario_id' => $funcionarioId,
                'tipo_documento' => $tipoDocumento
            ]);
            return false;
        }
    }

    /**
     * Gera nome do campo de arquivo baseado no tipo de documento
     */
    private function getArquivoFieldName($tipoDocumento)
    {
        $mapeamento = [
            '02 fotos 3x4' => 'arquivo_fotos',
            'Carteira de saúde atualizada com foto 3x4' => 'arquivo_carteira_saude',
            'Encaminhamento para exame admissional' => 'arquivo_encaminhamento',
            'Antecedente cível e criminal' => 'arquivo_antecedente',
            'R.G. (identidade)' => 'arquivo_rg',
            'CPF' => 'arquivo_cpf',
            'CNH (carteira nacional de habilitação)' => 'arquivo_cnh',
            'Título Eleitoral' => 'arquivo_titulo',
            'Comprovante de endereço (com CEP)' => 'arquivo_endereco',
            'Carteira de trabalho, frente e verso' => 'arquivo_carteira_trabalho',
            'Certidão de nascimento' => 'arquivo_certidao_nascimento',
            'CPF filho' => 'arquivo_cpf_filho',
            'Carteira de vacinação (menor 07 anos)' => 'arquivo_vacinacao',
            'Comprovante de frequência escolar (maior 07 anos)' => 'arquivo_frequencia'
        ];

        return $mapeamento[$tipoDocumento] ?? 'arquivo_generico';
    }

    /**
     * Download/Visualização de arquivos BLOB (PROTEÇÃO)
     */
    public function downloadBLOB($arquivoId)
    {
        try {
            if (!Auth::user()->temPermissao('doc_dp')) {
                abort(403);
            }

            $arquivo = DB::table('funcionarios_documentos')->where('id', $arquivoId)->first();
            if (!$arquivo) {
                abort(404, 'Arquivo não encontrado');
            }

            return response($arquivo->arquivo_conteudo)
                ->header('Content-Type', $arquivo->arquivo_mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $arquivo->arquivo_nome . '"')
                ->header('Content-Length', strlen($arquivo->arquivo_conteudo));

        } catch (\Exception $e) {
            Log::error('Erro no download BLOB: ' . $e->getMessage());
            abort(500, 'Erro interno do servidor');
        }
    }

    /**
     * Buscar funcionário e seus documentos
     */
    public function buscarFuncionario(Request $request)
    {
        try {
            $nome = $request->get('nome', '');
            
            if (strlen($nome) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Digite pelo menos 2 caracteres para buscar'
                ]);
            }

            // Buscar funcionários e seus documentos
            $funcionarios = DB::table('funcionarios as f')
                ->select([
                    'f.id',
                    'f.nome',
                    'f.cpf',
                    'f.sexo',
                    'f.funcao',
                    'f.status',
                    'f.created_at',
                    DB::raw('COUNT(fd.id) as total_documentos')
                ])
                ->leftJoin('funcionarios_documentos as fd', 'f.id', '=', 'fd.funcionario_id')
                ->where('f.nome', 'LIKE', '%' . $nome . '%')
                ->groupBy('f.id', 'f.nome', 'f.cpf', 'f.sexo', 'f.funcao', 'f.status', 'f.created_at')
                ->orderBy('f.created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'funcionarios' => $funcionarios
            ]);

        } catch (\Exception $e) {
            Log::error('Erro na busca de funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro na busca'
            ]);
        }
    }

    /**
     * Listar documentos de um funcionário específico
     */
    public function listarDocumentos($funcionarioId)
    {
        try {
            // Verificar permissão (doc_dp OU vis_func)
            if (!Auth::user()->temPermissao('doc_dp') && !Auth::user()->temPermissao('vis_func')) {
                abort(403);
            }

            // Buscar funcionário e documentos
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            
            if (!$funcionario) {
                abort(404, 'Funcionário não encontrado');
            }

            $documentos = DB::table('funcionarios_documentos')
                ->select('id','funcionario_id','tipo_documento','arquivo_nome','arquivo_extensao','arquivo_mime_type','arquivo_tamanho','created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('tipo_documento')
                ->get();

            return response()->json([
                'success' => true,
                'funcionario' => $funcionario,
                'documentos' => $documentos
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar documentos: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar documentos'
            ]);
        }
    }

    /**
     * Anexar documentos faltantes na página de funcionários
     */
    public function anexarFaltantes(Request $request, $funcionarioId)
    {
        try {
            if (!Auth::user()->temPermissao('doc_dp')) {
                abort(403);
            }

            $request->validate([
                'tipo_documento' => 'required|string|in:' . implode(',', $this->getDocumentosValidos()),
                'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360'
            ]);

            // Garante que o funcionário existe
            $existe = DB::table('funcionarios')->where('id', $funcionarioId)->exists();
            if (!$existe) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $this->processarUploadArquivoBLOB($request->file('arquivo'), (int)$funcionarioId, $request->tipo_documento);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Erro ao anexar documento faltante: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao anexar documento'], 500);
        }
    }

    /**
     * Alterar status do funcionário (generalizado)
     */
    public function alterarStatusFuncionario(Request $request, $funcionarioId)
    {
        try {
            // Validação
            $validator = Validator::make($request->all(), [
                'status' => 'required|string|in:trabalhando,demitido,afastado,ferias'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Status inválido: ' . $validator->errors()->first()
                ]);
            }

            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            
            if (!$funcionario) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            $novoStatus = $request->status;

            // Verificar se já não está no status solicitado
            if ($funcionario->status === $novoStatus) {
                return response()->json([
                    'success' => false,
                    'message' => "Funcionário já está com status '{$novoStatus}'"
                ]);
            }

            // Atualizar status
            $affected = DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->update([
                    'status' => $novoStatus,
                    'updated_at' => now()
                ]);

            if ($affected) {
                Log::info('Status do funcionário alterado', [
                    'funcionario_id' => $funcionarioId,
                    'nome' => $funcionario->nome,
                    'status_anterior' => $funcionario->status,
                    'status_novo' => $novoStatus,
                    'usuario_acao' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => "Status alterado para '{$novoStatus}' com sucesso"
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao alterar status do funcionário'
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao alterar status do funcionário: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    /**
     * Demitir funcionário - altera status para 'demitido'
     */
    public function demitirFuncionario(Request $request, $funcionarioId)
    {
        try {
            // Verificar se o funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            
            if (!$funcionario) {
                return response()->json([
                    'success' => false, 
                    'message' => 'Funcionário não encontrado'
                ], 404);
            }

            // Verificar se já não está demitido
            if ($funcionario->status === 'demitido') {
                return response()->json([
                    'success' => false,
                    'message' => 'Funcionário já está demitido'
                ]);
            }

            // Atualizar status para demitido usando SQL direto (sem migration)
            $affected = DB::table('funcionarios')
                ->where('id', $funcionarioId)
                ->update([
                    'status' => 'demitido',
                    'updated_at' => now()
                ]);

            if ($affected) {
                Log::info('Funcionário demitido', [
                    'funcionario_id' => $funcionarioId,
                    'nome' => $funcionario->nome,
                    'usuario_acao' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Funcionário demitido com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar status do funcionário'
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Erro ao demitir funcionário: ' . $e->getMessage(), [
                'funcionario_id' => $funcionarioId,
                'usuario' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor'
            ], 500);
        }
    }

    // ========================================
    // MÉTODOS PARA ATESTADOS
    // ========================================

    /**
     * Listar atestados de um funcionário
     */
    public function listarAtestados($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $atestados = DB::table('funcionarios_atestados')
                ->select('id', 'tipo_atestado', 'data_atestado', 'data_entrega', 'dias_afastamento', 'observacoes', 'arquivo_nome', 'arquivo_extensao', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data_atestado', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'atestados' => $atestados
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar atestados: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar atestados']);
        }
    }

    /**
     * Anexar novo atestado
     */
    public function anexarAtestado(Request $request, $funcionarioId)
    {
        try {
            // Validação
            $validator = Validator::make($request->all(), [
                'tipo_atestado' => 'required|string|in:Médico,Odontológico,Psicológico,Fisioterapia,Exame,Outros',
                'data_atestado' => 'required|date',
                'dias_afastamento' => 'nullable|integer|min:0|max:365',
                'observacoes' => 'nullable|string|max:1000',
                'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Dados inválidos: ' . $validator->errors()->first()]);
            }

            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $arquivo = $request->file('arquivo');
            $conteudo = file_get_contents($arquivo->getPathname());
            $hash = hash('sha256', $conteudo);

            // Inserir atestado
            $atestadoId = DB::table('funcionarios_atestados')->insertGetId([
                'funcionario_id' => $funcionarioId,
                'tipo_atestado' => $request->tipo_atestado,
                'data_atestado' => $request->data_atestado,
                'data_entrega' => now(),
                'dias_afastamento' => $request->dias_afastamento ?: null,
                'observacoes' => $request->observacoes,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_extensao' => $arquivo->getClientOriginalExtension(),
                'arquivo_mime_type' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'arquivo_hash' => $hash,
                'arquivo_conteudo' => $conteudo,
                'usuario_cadastro' => Auth::id(),
                'status' => 'pendente',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Log da ação
            $this->logAcao($funcionarioId, 'atestados', $atestadoId, 'anexou_atestado', 
                "Anexou atestado {$request->tipo_atestado} para {$funcionario->nome}");

            return response()->json(['success' => true, 'message' => 'Atestado anexado com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao anexar atestado: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Download de atestado (BLOB)
     */
    public function downloadAtestado($atestadoId)
    {
        try {
            $atestado = DB::table('funcionarios_atestados')->where('id', $atestadoId)->first();
            
            if (!$atestado) {
                abort(404, 'Atestado não encontrado');
            }

            return response($atestado->arquivo_conteudo)
                ->header('Content-Type', $atestado->arquivo_mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $atestado->arquivo_nome . '"');

        } catch (\Exception $e) {
            Log::error('Erro ao baixar atestado: ' . $e->getMessage());
            abort(500, 'Erro ao carregar arquivo');
        }
    }

    // ========================================
    // MÉTODOS PARA ADVERTÊNCIAS
    // ========================================

    /**
     * Listar advertências de um funcionário
     */
    public function listarAdvertencias($funcionarioId)
    {
        try {
            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $advertencias = DB::table('funcionarios_advertencias')
                ->select('id', 'tipo_advertencia', 'motivo', 'data_advertencia', 'data_entrega', 'dias_suspensao', 'observacoes', 'arquivo_nome', 'arquivo_extensao', 'arquivo_tamanho', 'created_at')
                ->where('funcionario_id', $funcionarioId)
                ->orderBy('data_advertencia', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'advertencias' => $advertencias
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao listar advertências: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro ao carregar advertências']);
        }
    }

    /**
     * Aplicar nova advertência
     */
    public function aplicarAdvertencia(Request $request, $funcionarioId)
    {
        try {
            // Validação
            $validator = Validator::make($request->all(), [
                'tipo_advertencia' => 'required|string|in:verbal,escrita,suspensao',
                'motivo' => 'required|string|max:500',
                'data_advertencia' => 'required|date',
                'dias_suspensao' => 'nullable|integer|min:1|max:30|required_if:tipo_advertencia,suspensao',
                'observacoes' => 'nullable|string|max:1000',
                'arquivo' => 'required|file|mimes:pdf,jpg,jpeg,png|max:15360'
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Dados inválidos: ' . $validator->errors()->first()]);
            }

            // Verificar se funcionário existe
            $funcionario = DB::table('funcionarios')->where('id', $funcionarioId)->first();
            if (!$funcionario) {
                return response()->json(['success' => false, 'message' => 'Funcionário não encontrado'], 404);
            }

            $arquivo = $request->file('arquivo');
            $conteudo = file_get_contents($arquivo->getPathname());
            $hash = hash('sha256', $conteudo);

            // Inserir advertência
            $advertenciaId = DB::table('funcionarios_advertencias')->insertGetId([
                'funcionario_id' => $funcionarioId,
                'tipo_advertencia' => $request->tipo_advertencia,
                'motivo' => $request->motivo,
                'data_advertencia' => $request->data_advertencia,
                'data_entrega' => now(),
                'dias_suspensao' => $request->tipo_advertencia === 'suspensao' ? $request->dias_suspensao : null,
                'observacoes' => $request->observacoes,
                'arquivo_nome' => $arquivo->getClientOriginalName(),
                'arquivo_extensao' => $arquivo->getClientOriginalExtension(),
                'arquivo_mime_type' => $arquivo->getMimeType(),
                'arquivo_tamanho' => $arquivo->getSize(),
                'arquivo_hash' => $hash,
                'arquivo_conteudo' => $conteudo,
                'usuario_cadastro' => Auth::id(),
                'status' => 'ativa',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Log da ação
            $this->logAcao($funcionarioId, 'advertencias', $advertenciaId, 'aplicou_advertencia', 
                "Aplicou advertência {$request->tipo_advertencia} para {$funcionario->nome}: {$request->motivo}");

            return response()->json(['success' => true, 'message' => 'Advertência aplicada com sucesso']);

        } catch (\Exception $e) {
            Log::error('Erro ao aplicar advertência: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Erro interno do servidor'], 500);
        }
    }

    /**
     * Download de advertência (BLOB)
     */
    public function downloadAdvertencia($advertenciaId)
    {
        try {
            $advertencia = DB::table('funcionarios_advertencias')->where('id', $advertenciaId)->first();
            
            if (!$advertencia) {
                abort(404, 'Advertência não encontrada');
            }

            return response($advertencia->arquivo_conteudo)
                ->header('Content-Type', $advertencia->arquivo_mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $advertencia->arquivo_nome . '"');

        } catch (\Exception $e) {
            Log::error('Erro ao baixar advertência: ' . $e->getMessage());
            abort(500, 'Erro ao carregar arquivo');
        }
    }

    // ========================================
    // MÉTODO AUXILIAR PARA LOGS
    // ========================================

    /**
     * Registrar log de ação
     */
    private function logAcao($funcionarioId, $tabelaOrigem, $registroId, $acao, $descricao, $dadosAnteriores = null, $dadosNovos = null)
    {
        try {
            DB::table('funcionarios_logs')->insert([
                'funcionario_id' => $funcionarioId,
                'tabela_origem' => $tabelaOrigem,
                'registro_id' => $registroId,
                'acao' => $acao,
                'descricao' => $descricao,
                'dados_anteriores' => $dadosAnteriores ? json_encode($dadosAnteriores) : null,
                'dados_novos' => $dadosNovos ? json_encode($dadosNovos) : null,
                'usuario_id' => Auth::id(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now()
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao registrar log: ' . $e->getMessage());
        }
    }
}
