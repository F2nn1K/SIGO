<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PermissoesController;
use App\Http\Controllers\UsuariosController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Livewire\GerenciarPermissoes;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\LicenseController;
use App\Http\Controllers\VeiculoController;
use App\Http\Controllers\AbastecimentoController;
use App\Http\Controllers\ManutencaoController;
use App\Http\Controllers\ViagemController;
use App\Http\Controllers\RelatorioKmController;
use App\Http\Controllers\RelatorioProdutoEstoqueController;
use App\Http\Controllers\LicenciamentoController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect()->route('login');
});


Route::middleware(['auth'])->group(function () {
    // Dashboard - acessível para todos os usuários autenticados
    Route::get('/home', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');


    // Rotas de Documentos DP - protegidas pela permissão 'doc_dp'
    Route::middleware(['can:doc_dp','throttle:60,1'])->group(function () {
        Route::get('/documentos-dp/inclusao', [App\Http\Controllers\DocumentosDPController::class, 'inclusao'])->name('documentos-dp.inclusao');
        Route::post('/documentos-dp/inclusao', [App\Http\Controllers\DocumentosDPController::class, 'store'])->name('documentos-dp.store');
        Route::get('/api/documentos-dp/check-cpf', [App\Http\Controllers\DocumentosDPController::class, 'checkCpf'])->name('documentos-dp.check-cpf');
        Route::get('/documentos-dp/arquivo/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadBLOB'])->name('documentos-dp.arquivo');
        Route::get('/documentos-dp/buscar', [App\Http\Controllers\DocumentosDPController::class, 'buscarFuncionario'])->name('documentos-dp.buscar');
        Route::get('/documentos-dp/funcionario/{id}/documentos', [App\Http\Controllers\DocumentosDPController::class, 'listarDocumentos'])->name('documentos-dp.documentos');
        // Ordem de Serviço (nova) – protegida pela permissão específica 'ord_serv'
        Route::get('/documentos-dp/ordem-servico', [App\Http\Controllers\OrdemServicoController::class, 'index'])->middleware('can:ord_serv')->name('documentos-dp.ordem-servico');
        Route::get('/documentos-dp/ordem-servico/nova', [App\Http\Controllers\OrdemServicoController::class, 'nova'])->middleware('can:ord_serv')->name('documentos-dp.ordem-servico.nova');
        Route::post('/documentos-dp/ordem-servico', [App\Http\Controllers\OrdemServicoController::class, 'store'])->middleware('can:ord_serv')->name('documentos-dp.ordem-servico.store');
        Route::get('/documentos-dp/ordem-servico/lista', [App\Http\Controllers\OrdemServicoController::class, 'lista'])->middleware('can:ord_serv')->name('documentos-dp.ordem-servico.lista');
        // Visualização via JSON (usada no modal da aba O.S.)
        Route::get('/api/ordens-servico/{id}', [App\Http\Controllers\OrdemServicoController::class, 'show'])->middleware('can:ord_serv')->name('ordens-servico.show');
        // Autocomplete de funcionários: leitura simples; manter apenas 'auth' do grupo para não bloquear
        Route::get('/api/ordens-servico/funcionarios', [App\Http\Controllers\OrdemServicoController::class, 'buscarFuncionarios'])->name('ordens-servico.buscar-funcionarios');
        
        // Teste simples para debug
        Route::get('/api/test-funcionarios', function() {
            return response()->json(['success' => true, 'message' => 'Endpoint funcionando', 'data' => []]);
        });
        
        // Endpoint alternativo para funcionários - BUSCA REAL NO BANCO
        Route::get('/api/funcionarios-busca', function(Illuminate\Http\Request $request) {
            $q = trim($request->get('q', ''));
            if (strlen($q) < 3) {
                return response()->json(['success' => true, 'data' => []]);
            }
            
            try {
                $result = DB::table('funcionarios')
                    ->select('id', 'nome', 'cpf')
                    ->where('nome', 'like', '%' . $q . '%')
                    ->whereNotNull('nome')
                    ->where('nome', '!=', '')
                    ->orderBy('nome')
                    ->limit(30)
                    ->get();
                    
                return response()->json(['success' => true, 'data' => $result]);
            } catch (\Exception $e) {
                return response()->json(['success' => true, 'data' => []]);
            }
        });
        Route::get('/api/ordens-servico/por-funcionario/{id}', [App\Http\Controllers\OrdemServicoController::class, 'listarPorFuncionario'])->middleware('can:ord_serv')->name('ordens-servico.por-funcionario');
    });

    // Página de visualização de funcionários (somente quem tem a permissão específica)
    Route::middleware(['can:vis_func','throttle:60,1'])->group(function () {
        Route::view('/documentos-dp/funcionarios', 'documentos-dp.funcionarios')->name('documentos-dp.funcionarios');
        // Endpoint para anexar documentos faltantes na página de funcionários
        Route::post('/documentos-dp/funcionario/{id}/anexar', [App\Http\Controllers\DocumentosDPController::class, 'anexarFaltantes'])->name('documentos-dp.anexar');
                    // Endpoint para demitir funcionário
            Route::post('/documentos-dp/funcionario/{id}/demitir', [App\Http\Controllers\DocumentosDPController::class, 'demitirFuncionario'])->name('documentos-dp.demitir');
            // Endpoint para alterar status do funcionário
            Route::post('/documentos-dp/funcionario/{id}/alterar-status', [App\Http\Controllers\DocumentosDPController::class, 'alterarStatusFuncionario'])->name('documentos-dp.alterar-status');
        
        // Endpoints para atestados
        Route::get('/documentos-dp/funcionario/{id}/atestados', [App\Http\Controllers\DocumentosDPController::class, 'listarAtestados'])->name('documentos-dp.atestados');
        Route::post('/documentos-dp/funcionario/{id}/atestados', [App\Http\Controllers\DocumentosDPController::class, 'anexarAtestado'])->name('documentos-dp.anexar-atestado');
        Route::get('/documentos-dp/atestado/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadAtestado'])->name('documentos-dp.atestado');
        
        // Endpoints para advertências
        Route::get('/documentos-dp/funcionario/{id}/advertencias', [App\Http\Controllers\DocumentosDPController::class, 'listarAdvertencias'])->name('documentos-dp.advertencias');
        Route::post('/documentos-dp/funcionario/{id}/advertencias', [App\Http\Controllers\DocumentosDPController::class, 'aplicarAdvertencia'])->name('documentos-dp.aplicar-advertencia');
        Route::get('/documentos-dp/advertencia/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadAdvertencia'])->name('documentos-dp.advertencia');
        
        // Endpoints para EPIs (materiais retirados)
        Route::get('/documentos-dp/funcionario/{id}/epis', [App\Http\Controllers\DocumentosDPController::class, 'listarEpis'])->name('documentos-dp.epis');
        
        // Endpoints para EPIs retroativos (PDFs)
        Route::get('/documentos-dp/funcionario/{id}/epis-retroativos', [App\Http\Controllers\DocumentosDPController::class, 'listarEpisRetroativos'])->name('documentos-dp.epis-retroativos');
        Route::post('/documentos-dp/epi-retroativo/store', [App\Http\Controllers\DocumentosDPController::class, 'storeEpiRetroativo'])->name('documentos-dp.store-epi-retroativo');
        Route::get('/documentos-dp/epi-retroativo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadEpiRetroativo'])->name('documentos-dp.download-epi-retroativo');
        
        // Endpoints para contra cheques
        Route::get('/documentos-dp/funcionario/{id}/contra-cheques', [App\Http\Controllers\DocumentosDPController::class, 'listarContraCheques'])->name('documentos-dp.contra-cheques');
        Route::post('/documentos-dp/contra-cheque/store', [App\Http\Controllers\DocumentosDPController::class, 'storeContraCheque'])->name('documentos-dp.store-contra-cheque');
        Route::get('/documentos-dp/contra-cheque/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadContraCheque'])->name('documentos-dp.download-contra-cheque');
        
        // Endpoints para férias
        Route::get('/documentos-dp/funcionario/{id}/ferias', [App\Http\Controllers\DocumentosDPController::class, 'listarFerias'])->name('documentos-dp.ferias');
        Route::post('/documentos-dp/ferias/store', [App\Http\Controllers\DocumentosDPController::class, 'storeFerias'])->name('documentos-dp.store-ferias');
        Route::get('/documentos-dp/ferias/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadFerias'])->name('documentos-dp.download-ferias');
        
        // Endpoints para décimo terceiro
        Route::get('/documentos-dp/funcionario/{id}/decimo', [App\Http\Controllers\DocumentosDPController::class, 'listarDecimo'])->name('documentos-dp.decimo');
        Route::post('/documentos-dp/decimo/store', [App\Http\Controllers\DocumentosDPController::class, 'storeDecimo'])->name('documentos-dp.store-decimo');
        Route::get('/documentos-dp/decimo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadDecimo'])->name('documentos-dp.download-decimo');
        
        // Endpoints para rescisão
        Route::get('/documentos-dp/funcionario/{id}/rescisao', [App\Http\Controllers\DocumentosDPController::class, 'listarRescisao'])->name('documentos-dp.rescisao');
        Route::post('/documentos-dp/rescisao/store', [App\Http\Controllers\DocumentosDPController::class, 'storeRescisao'])->name('documentos-dp.store-rescisao');
        Route::get('/documentos-dp/rescisao/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadRescisao'])->name('documentos-dp.download-rescisao');
        
        // Endpoints para frequência
        Route::get('/documentos-dp/funcionario/{id}/frequencia', [App\Http\Controllers\DocumentosDPController::class, 'listarFrequencia'])->name('documentos-dp.frequencia');
        Route::post('/documentos-dp/frequencia/store', [App\Http\Controllers\DocumentosDPController::class, 'storeFrequencia'])->name('documentos-dp.store-frequencia');
        Route::get('/documentos-dp/frequencia/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadFrequencia'])->name('documentos-dp.download-frequencia');
        
        // Endpoints para certificados
        Route::get('/documentos-dp/funcionario/{id}/certificado', [App\Http\Controllers\DocumentosDPController::class, 'listarCertificado'])->name('documentos-dp.certificado');
        Route::post('/documentos-dp/certificado/store', [App\Http\Controllers\DocumentosDPController::class, 'storeCertificado'])->name('documentos-dp.store-certificado');
        Route::get('/documentos-dp/certificado/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadCertificado'])->name('documentos-dp.download-certificado');
        
        // Endpoints para Termo Aditivo
        Route::get('/documentos-dp/funcionario/{id}/termo-aditivo', [App\Http\Controllers\DocumentosDPController::class, 'listarTermoAditivo']);
        Route::post('/documentos-dp/termo-aditivo/store', [App\Http\Controllers\DocumentosDPController::class, 'storeTermoAditivo']);
        Route::get('/documentos-dp/termo-aditivo/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadTermoAditivo']);
        
        // Endpoints para ASOS
        Route::get('/documentos-dp/funcionario/{id}/asos', [App\Http\Controllers\DocumentosDPController::class, 'listarAsos'])->name('documentos-dp.asos');
        Route::post('/documentos-dp/asos/store', [App\Http\Controllers\DocumentosDPController::class, 'storeAsos'])->name('documentos-dp.store-asos');
        Route::get('/documentos-dp/asos/{id}/download', [App\Http\Controllers\DocumentosDPController::class, 'downloadAsos'])->name('documentos-dp.download-asos');
        
        // Endpoint para visualizar PDF completo do funcionário (todos os PDFs juntos)
        Route::get('/documentos-dp/funcionario/{id}/visualizar-pdf', [App\Http\Controllers\DocumentosDPController::class, 'visualizarPdfCompleto'])->name('documentos-dp.visualizar-pdf');
        
        // Endpoint para gerar arquivo completo do funcionário (ZIP com todos os documentos)
        Route::get('/documentos-dp/funcionario/{id}/arquivo-completo', [App\Http\Controllers\DocumentosDPController::class, 'gerarArquivoCompleto'])->name('documentos-dp.arquivo-completo');
        
        // Rotas DELETE para exclusão de documentos
        Route::delete('/documentos-dp/documento/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteDocumento'])->name('documentos-dp.delete-documento');
        Route::delete('/documentos-dp/atestado/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteAtestado'])->name('documentos-dp.delete-atestado');
        Route::delete('/documentos-dp/advertencia/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteAdvertencia'])->name('documentos-dp.delete-advertencia');
        Route::delete('/documentos-dp/contra-cheque/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteContraCheque'])->name('documentos-dp.delete-contra-cheque');
        Route::delete('/documentos-dp/ferias/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteFerias'])->name('documentos-dp.delete-ferias');
        Route::delete('/documentos-dp/epi-retroativo/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteEpiRetroativo'])->name('documentos-dp.delete-epi-retroativo');
        Route::delete('/documentos-dp/decimo/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteDecimo'])->name('documentos-dp.delete-decimo');
        Route::delete('/documentos-dp/rescisao/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteRescisao'])->name('documentos-dp.delete-rescisao');
        Route::delete('/documentos-dp/frequencia/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteFrequencia'])->name('documentos-dp.delete-frequencia');
        Route::delete('/documentos-dp/certificado/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteCertificado'])->name('documentos-dp.delete-certificado');
        Route::delete('/documentos-dp/asos/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteAsos'])->name('documentos-dp.delete-asos');
        Route::delete('/documentos-dp/termo-aditivo/{id}/delete', [App\Http\Controllers\DocumentosDPController::class, 'deleteTermoAditivo']);
    });

    // Rotas de BRS - Controle de Estoque - protegidas pela permissão 'Controle de Estoque'
    Route::middleware(['can:controle-estoque','throttle:120,1'])->group(function () {
    Route::get('/brs/controle-estoque', [App\Http\Controllers\ControleEstoqueController::class, 'index'])->name('brs.controle-estoque');
    Route::get('/api/funcionarios', [App\Http\Controllers\ControleEstoqueController::class, 'buscarFuncionarios']);
    Route::get('/api/centro-custos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarCentroCustos']);
    Route::get('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutos']);
    Route::get('/api/produtos-em-falta', [App\Http\Controllers\ControleEstoqueController::class, 'produtosEmFalta']);
    // Endpoint específico do módulo de estoque para evitar conflito com pedidos de compras
    Route::get('/api/estoque/produtos/buscar', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutosPorNome']);
    Route::post('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'criarProduto']);
    Route::put('/api/produtos/{id}', [App\Http\Controllers\ControleEstoqueController::class, 'atualizarProduto']);
    Route::post('/api/entradas', [App\Http\Controllers\ControleEstoqueController::class, 'registrarEntrada']);
    Route::post('/api/baixas/verificar-funcionario', [App\Http\Controllers\ControleEstoqueController::class, 'verificarFardamentoFuncionario']);
    Route::post('/api/baixas/verificar', [App\Http\Controllers\ControleEstoqueController::class, 'verificarPrazoFardamento']);
    Route::post('/api/baixas', [App\Http\Controllers\ControleEstoqueController::class, 'registrarBaixa']);
});

// Módulo Frota - somente para usuários com permissões
Route::middleware(['auth'])->prefix('frota')->name('frota.')->group(function () {
    // Veículos
    Route::get('/veiculos', [VeiculoController::class, 'index'])
        ->middleware('can:veiculos')->name('veiculos.index');
    // APIs Veículos
    // Leitura liberada para quem possui acesso a Viagens (usado pela tela de Nova Viagem)
    Route::get('/api/veiculos', [VeiculoController::class, 'json'])
        ->middleware('can:viagens');
    // Leitura para relatórios de KM (sem exigir permissão de Viagens)
    Route::get('/api/veiculos-relatorio', [VeiculoController::class, 'json'])
        ->middleware('can:rel_km');
    Route::get('/api/veiculos/{id}', [VeiculoController::class, 'showJson'])
        ->middleware('can:viagens');
    Route::post('/api/veiculos', [VeiculoController::class, 'store'])
        ->middleware('can:veiculos');
    Route::put('/api/veiculos/{id}', [VeiculoController::class, 'update'])
        ->middleware('can:veiculos');
    Route::delete('/api/veiculos/{id}', [VeiculoController::class, 'destroy'])
        ->middleware('can:veiculos');

    // Abastecimentos
    Route::get('/abastecimentos', [AbastecimentoController::class, 'index'])
        ->middleware('can:abastecimento')->name('abastecimentos.index');
    Route::get('/api/abastecimentos', [AbastecimentoController::class, 'json'])
        ->middleware('can:abastecimento');
    Route::post('/api/abastecimentos', [AbastecimentoController::class, 'store'])
        ->middleware('can:abastecimento');
    Route::put('/api/abastecimentos/{id}', [AbastecimentoController::class, 'update'])
        ->middleware('can:abastecimento');
    Route::delete('/api/abastecimentos/{id}', [AbastecimentoController::class, 'destroy'])
        ->middleware('can:abastecimento');

    // Manutenções
    Route::get('/manutencoes', [ManutencaoController::class, 'index'])
        ->middleware('can:manutencao')->name('manutencoes.index');
    Route::get('/api/manutencoes', [ManutencaoController::class, 'json'])
        ->middleware('can:manutencao');
    Route::post('/api/manutencoes', [ManutencaoController::class, 'store'])
        ->middleware('can:manutencao');
    Route::put('/api/manutencoes/{id}', [ManutencaoController::class, 'update'])
        ->middleware('can:manutencao');
    Route::delete('/api/manutencoes/{id}', [ManutencaoController::class, 'destroy'])
        ->middleware('can:manutencao');

    // Viagens
    Route::get('/viagens', [ViagemController::class, 'index'])
        ->middleware('can:viagens')->name('viagens.index');
    // Leitura aberta para o módulo Viagens
    Route::get('/api/viagens', [ViagemController::class, 'json'])
        ->middleware('can:viagens');
    // Endpoint espefícico para relatórios de KM (perm rel_km)
    Route::get('/api/viagens-relatorio', [ViagemController::class, 'json'])
        ->middleware('can:rel_km');
    // Escrita/alteração continuam protegidas
    Route::post('/api/viagens', [ViagemController::class, 'store'])
        ->middleware('can:viagens');
    Route::put('/api/viagens/{id}', [ViagemController::class, 'update'])
        ->middleware('can:viagens');
    Route::delete('/api/viagens/{id}', [ViagemController::class, 'destroy'])
        ->middleware('can:viagens');

    // Relatórios
    Route::get('/relatorios/consumo', function(){
        return view('frota.relatorios.consumo');
    })->middleware('can:rel_consm')->name('relatorios.consumo');

    Route::get('/relatorios/custo', function(){
        return view('frota.relatorios.custo');
    })->middleware('can:rel_cust')->name('relatorios.custo');

    // Relatório de Manutenções (Frota)
    Route::get('/relatorios/manutencoes', [\App\Http\Controllers\RelatorioManutencaoController::class, 'index'])
        ->middleware('can:Rel_manu')->name('relatorios.manutencoes');

    // API Relatório de Manutenções (Frota)
    Route::get('/api/relatorios/manutencoes', [\App\Http\Controllers\RelatorioManutencaoController::class, 'data'])
        ->middleware('can:Rel_manu');

    // Relatório de Abastecimento (Frota)
    Route::get('/relatorios/abastecimento', function(){
        return view('frota.relatorios.abastecimento');
    })->middleware('can:rel_abast')->name('relatorios.abastecimento');

    // Relatório: KM Percorrido (permissão: rel_km)
    Route::get('/relatorios/km-percorrido', [RelatorioKmController::class, 'index'])
        ->middleware('can:rel_km')->name('relatorios.km-percorrido');
    Route::get('/api/relatorios/km-percorrido', [RelatorioKmController::class, 'data'])
        ->middleware('can:rel_km');

    // API: opções para selects (veículos e usuários)
    Route::get('/api/relatorios/abastecimento/opcoes', function(){
        $veiculos = \DB::table('veiculos')->select('id','placa')->orderBy('placa')->get();
        $usuarios = \DB::table('users')->select('id','name')->orderBy('name')->get();
        return response()->json(['success' => true, 'veiculos' => $veiculos, 'usuarios' => $usuarios]);
    })->middleware('can:rel_abast');

    // API: listagem detalhada com filtros
    Route::get('/api/relatorios/abastecimento', function(\Illuminate\Http\Request $request){
        $ini = $request->query('data_ini');
        $fim = $request->query('data_fim');
        $veiculoId = $request->query('veiculo_id');
        $userId = $request->query('user_id');

        $query = \DB::table('abastecimentos as a')
            ->leftJoin('veiculos as v', 'a.vehicle_id', '=', 'v.id')
            ->leftJoin('users as u', 'a.user_id', '=', 'u.id')
            ->selectRaw('DATE_FORMAT(a.data, "%d/%m/%Y") as data, v.placa, u.name as funcionario, a.km, a.litros, a.preco_litro, a.valor, a.tipo_combustivel, a.posto')
            ->when($ini, function($q) use ($ini){ $q->whereDate('a.data', '>=', $ini); })
            ->when($fim, function($q) use ($fim){ $q->whereDate('a.data', '<=', $fim); })
            ->when($veiculoId, function($q) use ($veiculoId){ $q->where('a.vehicle_id', $veiculoId); })
            ->when($userId, function($q) use ($userId){ $q->where('a.user_id', $userId); })
            ->orderByRaw('a.data desc, v.placa asc');

        $dados = $query->limit(2000)->get();

        $totais = [
            'litros' => (float) $dados->sum('litros'),
            'valor'  => (float) $dados->sum('valor'),
        ];

        return response()->json(['success' => true, 'data' => $dados, 'totais' => $totais]);
    })->middleware('can:rel_abast');

    // Ocorrências da Frota
    Route::get('/ocorrencias', [App\Http\Controllers\OcorrenciaController::class, 'index'])
        ->middleware('can:ocorrencia')->name('ocorrencias.index');
    Route::post('/ocorrencias', [App\Http\Controllers\OcorrenciaController::class, 'store'])
        ->middleware('can:ocorrencia')->name('ocorrencias.store');

    // Gestor de Ocorrências (somente quem tem a permissão específica)
    Route::get('/ocorrencias/gestor', [App\Http\Controllers\OcorrenciaController::class, 'gestor'])
        ->middleware('can:Gestão de Ocorrencia')->name('ocorrencias.gestor');

    // APIs para o gestor
    Route::get('/ocorrencias/api/{id}', [App\Http\Controllers\OcorrenciaController::class, 'showOccurrence'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::post('/ocorrencias/api/{id}/status', [App\Http\Controllers\OcorrenciaController::class, 'updateStatus'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::get('/ocorrencias/api/veiculo/{veiculoId}/historico', [App\Http\Controllers\OcorrenciaController::class, 'historicoVeiculo'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::get('/ocorrencias/api/{id}/fotos', [App\Http\Controllers\OcorrenciaController::class, 'fotos'])
        ->middleware('can:Gestão de Ocorrencia');
    Route::get('/ocorrencias/api/{id}/foto/{idx}', [App\Http\Controllers\OcorrenciaController::class, 'foto'])
        ->whereNumber('idx')
        ->middleware('can:Gestão de Ocorrencia');

    // NF Abastecimento (nova página protegida por permissão)
    Route::get('/nf-abastecimento', [App\Http\Controllers\NFAbastecimentoController::class, 'index'])
        ->middleware('can:Nf_abas')->name('nf-abastecimento.index');
    Route::post('/nf-abastecimento/finalizar', [App\Http\Controllers\NFAbastecimentoController::class, 'finalizar'])
        ->middleware('can:Nf_abas');
    Route::post('/nf-abastecimento/avulso', [App\Http\Controllers\NFAbastecimentoController::class, 'salvarAvulso'])
        ->middleware('can:Nf_abas');

    // Relatório de conferência de NF (Frota)
    Route::get('/relatorios/conferencia-nf', [App\Http\Controllers\RelatorioFrotaController::class, 'conferenciaNf'])
        ->middleware('can:Rel_conf_nf')->name('frota.relatorios.conferencia-nf');
    Route::get('/api/relatorios/conferencia-nf', [App\Http\Controllers\RelatorioFrotaController::class, 'listarLotes'])
        ->middleware('can:Rel_conf_nf');
    Route::get('/api/relatorios/conferencia-nf/{id}', [App\Http\Controllers\RelatorioFrotaController::class, 'detalhesLote'])
        ->middleware('can:Rel_conf_nf');

    // Imprimir lote (retornará uma view simples para print)
    Route::get('/relatorios/conferencia-nf/imprimir/{id}', function($id){
        $lote = \DB::table('nf_abastecimento_lotes')->where('id',$id)->first();
        $itens = \DB::table('nf_abastecimento_itens as nfi')
            ->leftJoin('veiculos as v', 'nfi.veiculo_id', '=', 'v.id')
            ->select('nfi.*', 'v.placa')
            ->where('nfi.lote_id',$id)->get();
        return view('frota.relatorios.conferencia-nf-print', compact('lote','itens'));
    })->middleware('can:Rel_conf_nf');

    // Licenciamento (Frota)
    Route::get('/licenciamento', function(){ return view('frota.licenciamento'); })
        ->middleware('auth')
        ->name('licenciamento');
    // APIs com apenas 'auth' para facilitar debug; a própria tela já é protegida
    Route::get('/api/licenciamento/veiculos', [VeiculoController::class, 'licenciamentoVeiculos'])
        ->middleware('auth');
    Route::get('/api/licenciamento/status/{veiculo}', [VeiculoController::class, 'licenciamentoStatus'])
        ->middleware('auth');
    Route::post('/licenciamento', [VeiculoController::class, 'licenciamentoStore'])
        ->middleware('auth')
        ->name('licenciamento.store');
});

    // Rotas de Relatórios - protegidas por suas respectivas permissões
    Route::get('/relatorios', function() {
        return view('relatorios.index');
    })->middleware('auth')->name('relatorios.index');
    
    Route::middleware(['can:relatorio-estoque','throttle:60,1'])->group(function () {
        Route::get('/relatorios/estoque', [App\Http\Controllers\RelatorioEstoqueController::class, 'index'])->name('relatorios.estoque');
        Route::post('/api/relatorio-estoque', [App\Http\Controllers\RelatorioEstoqueController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-estoque/exportar', [App\Http\Controllers\RelatorioEstoqueController::class, 'exportarExcel']);
        Route::get('/api/produtos', [App\Http\Controllers\ControleEstoqueController::class, 'buscarProdutos']);
    });

    // Relatório por Produto (Estoque) – permissão: rel_por_prod
    Route::get('/relatorios/produto-estoque', [RelatorioProdutoEstoqueController::class, 'index'])
        ->middleware('can:rel_por_prod')->name('relatorios.produto-estoque');
    Route::get('/api/relatorios/produto-estoque', [RelatorioProdutoEstoqueController::class, 'data'])
        ->middleware('can:rel_por_prod');
    Route::get('/api/relatorios/produto-estoque/centros', [RelatorioProdutoEstoqueController::class, 'centros'])
        ->middleware('can:rel_por_prod');
    Route::get('/api/relatorios/produto-estoque/produtos', [RelatorioProdutoEstoqueController::class, 'produtos'])
        ->middleware('can:rel_por_prod');

    Route::middleware(['can:relatorio-centro-custo','throttle:60,1'])->group(function () {
        Route::get('/relatorios/centro-custo', [App\Http\Controllers\RelatorioCentroCustoController::class, 'index'])->name('relatorios.centro-custo');
        Route::post('/api/relatorio-centro-custo', [App\Http\Controllers\RelatorioCentroCustoController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-centro-custo/exportar', [App\Http\Controllers\RelatorioCentroCustoController::class, 'exportarExcel']);
    });

    Route::middleware(['can:relatorio-funcionario','throttle:60,1'])->group(function () {
        Route::get('/relatorios/funcionario', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'index'])->name('relatorios.funcionario');
        Route::post('/api/relatorio-funcionario', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-funcionario/exportar', [App\Http\Controllers\RelatorioPorFuncionarioController::class, 'exportarExcel']);
    });

    // Relatório Estoque - Máximo e Mínimo (perm: rel_maxmin)
    Route::get('/relatorios/estoque-min-max', [App\Http\Controllers\RelatorioEstoqueMinMaxController::class, 'index'])
        ->middleware('can:rel_maxmin')
        ->name('relatorios.estoque-min-max');
    Route::get('/api/relatorios/estoque-min-max', [App\Http\Controllers\RelatorioEstoqueMinMaxController::class, 'data'])
        ->middleware('can:rel_maxmin');

    // Estoque - Mínimo e Máximo (perm: est_mm)
    Route::get('/brs/estoque-min-max', function () {
        if (!\Auth::check() || !\Auth::user()->temPermissao('est_mm')) {
            abort(403, 'Ação não autorizada.');
        }
        return view('brs.estoque-min-max');
    })->name('brs.estoque-min-max');

    // APIs - Estoque Min/Max (protegidas por permissão est_mm)
    Route::get('/api/estoque/min-max', [App\Http\Controllers\EstoqueMinMaxController::class, 'listar'])
        ->middleware(['auth','throttle:120,1']);
    Route::post('/api/estoque/{produtoId}/min-max', [App\Http\Controllers\EstoqueMinMaxController::class, 'salvar'])
        ->middleware(['auth','throttle:120,1']);

    // Relatório: Pedido de Compras (somente para quem tem a permissão específica criada pelo usuário)
    Route::middleware(['can:rel_pc','throttle:60,1'])->group(function () {
        // Página do relatório
        Route::get('/relatorios/pedidos-compra', function () {
            return view('relatorios.relatorio-pedido-compras');
        })->name('relatorios.pedidos-compra');

        // Relatório de Pedido de Compras por Centro de Custo (nova página)
        Route::get('/relatorios/pedido-cc', function(){
            return view('relatorios.pedido-cc');
        })->middleware('can:rel_ped_cc')->name('relatorios.pedido-cc');

        // API para relatório de pedido por centro de custo
        Route::post('/api/relatorios/pedido-cc', [App\Http\Controllers\PedidoComprasController::class, 'relatorioPedidoCC'])
            ->middleware('can:rel_ped_cc');

        // Endpoints de leitura usados pela página do relatório
        Route::get('/api/relatorio-pc/aprovados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosAprovadosAgrupados']);
        Route::get('/api/relatorio-pc/rejeitados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosRejeitadosAgrupados']);
        // Para aprovados/rejeitados o identificador é o hash do envio
        Route::get('/api/relatorio-pc/detalhes/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'detalhesRelatorioPorHash']);
        Route::post('/api/relatorio-pc/visualizado/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'marcarVisualizadoPorHash']);
        Route::get('/relatorio-pc/imprimir/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'imprimirPedido']);
        // Endpoint agregado por pedido (mesma base do relatório) para o Dashboard
        Route::get('/api/relatorio-pedido-cc', [App\Http\Controllers\PedidoComprasController::class, 'relatorioPedidoCC']);
    });

    // Relatório: DP (somente para quem tem a permissão rel_dp)
    Route::middleware(['can:rel_dp','throttle:60,1'])->group(function () {
        Route::get('/relatorios/dp', [App\Http\Controllers\RelatorioDPController::class, 'index'])->name('relatorios.dp');
        Route::post('/api/relatorio-dp', [App\Http\Controllers\RelatorioDPController::class, 'gerarRelatorio']);
        Route::post('/api/relatorio-dp/exportar', [App\Http\Controllers\RelatorioDPController::class, 'exportarExcel']);
        Route::post('/api/funcionarios/buscar', [App\Http\Controllers\RelatorioDPController::class, 'buscarFuncionarios']);
        Route::get('/api/funcionarios/{id}', [App\Http\Controllers\RelatorioDPController::class, 'obterFuncionario']);

        // Visualizar todos os PDFs do funcionário como um único PDF (acesso via rel_dp)
        Route::get('/relatorios/dp/funcionario/{id}/visualizar', [App\Http\Controllers\DocumentosDPController::class, 'visualizarPdfCompleto'])
            ->name('relatorios.dp.visualizar-pdf');
    });

    // Relatório Absentismo (DP) – apenas para quem tem a permissão específica rel_abse
    Route::get('/relatorios/absenteismo', [App\Http\Controllers\RelatorioAbsentismoController::class, 'index'])
        ->middleware(['can:rel_abse','throttle:60,1'])->name('relatorios.absenteismo');

    // API: dados de absentismo (atestados)
    Route::get('/api/relatorios/absenteismo', [App\Http\Controllers\RelatorioAbsentismoController::class, 'data'])
        ->middleware(['can:rel_abse','throttle:60,1']);

    // Download/visualização de atestado via permissão rel_abse (atalho)
    Route::get('/relatorios/absenteismo/atestado/{id}', [App\Http\Controllers\DocumentosDPController::class, 'downloadAtestado'])
        ->middleware(['can:rel_abse','throttle:60,1'])
        ->name('relatorios.absenteismo.atestado');

    // Módulo Roçagem - Manutenção
    Route::get('/rocagem/manutencao', [App\Http\Controllers\RocagemManutencaoController::class, 'index'])
        ->middleware(['can:manu_roça','throttle:60,1'])
        ->name('rocagem.manutencao');

    // Módulo Roçagem - Abastecimentos
    Route::get('/rocagem/abastecimentos', [App\Http\Controllers\RocagemAbastecimentosController::class, 'index'])
        ->middleware(['can:abas_roca','throttle:60,1'])
        ->name('rocagem.abastecimentos');
    
    // API Roçagem - Abastecimentos
    Route::get('/rocagem/api/abastecimentos', [App\Http\Controllers\RocagemAbastecimentosController::class, 'listar'])
        ->middleware(['can:abas_roca','throttle:60,1']);
    Route::post('/rocagem/api/abastecimentos', [App\Http\Controllers\RocagemAbastecimentosController::class, 'salvar'])
        ->middleware(['can:abas_roca','throttle:60,1']);
    Route::put('/rocagem/api/abastecimentos/{id}', [App\Http\Controllers\RocagemAbastecimentosController::class, 'atualizar'])
        ->middleware(['can:abas_roca','throttle:60,1']);
    Route::delete('/rocagem/api/abastecimentos/{id}', [App\Http\Controllers\RocagemAbastecimentosController::class, 'excluir'])
        ->middleware(['can:abas_roca','throttle:60,1']);
    
    // API Roçagem - Locais
    Route::get('/rocagem/api/locais', [App\Http\Controllers\RocagemAbastecimentosController::class, 'listarLocais'])
        ->middleware(['can:abas_roca','throttle:60,1']);

    // API Roçagem - Manutenções (tela Manutenção Roçagem)
    Route::get('/rocagem/api/equipamentos', [App\Http\Controllers\RocagemManutencaoController::class, 'equipamentos'])
        ->middleware(['can:manu_roça','throttle:60,1']);
    Route::get('/rocagem/api/manutencoes', [App\Http\Controllers\RocagemManutencaoController::class, 'listar'])
        ->middleware(['can:manu_roça','throttle:60,1']);
    Route::post('/rocagem/api/manutencoes', [App\Http\Controllers\RocagemManutencaoController::class, 'salvar'])
        ->middleware(['can:manu_roça','throttle:60,1']);
    Route::put('/rocagem/api/manutencoes/{id}', [App\Http\Controllers\RocagemManutencaoController::class, 'atualizar'])
        ->middleware(['can:manu_roça','throttle:60,1']);
    Route::delete('/rocagem/api/manutencoes/{id}', [App\Http\Controllers\RocagemManutencaoController::class, 'excluir'])
        ->middleware(['can:manu_roça','throttle:60,1']);

    // Relatório: Funcionários Ativos/Inativos (perm: rel_ati_ina)
    Route::middleware(['can:rel_ati-ina','throttle:60,1'])->group(function () {
        Route::get('/relatorios/funcionarios-ativos-inativos', function(){
            return view('relatorios.funcionarios-ativos-inativos');
        })->name('relatorios.funcionarios-ativos-inativos');

        Route::post('/api/relatorios/funcionarios-ativos-inativos', [App\Http\Controllers\RelatorioDPController::class, 'funcionariosAtivosInativos']);
    });

    // Rotas de Pedidos de Compras - protegidas por suas respectivas permissões
    Route::middleware(['can:solicitacao-pedidos','throttle:120,1'])->group(function () {
        Route::get('/pedidos/solicitacao', [App\Http\Controllers\PedidoComprasController::class, 'solicitacao'])->name('pedidos.solicitacao');
        Route::post('/api/pedidos', [App\Http\Controllers\PedidoComprasController::class, 'store']);
        Route::get('/api/minhas-solicitacoes', [App\Http\Controllers\PedidoComprasController::class, 'minhasSolicitacoes']);
        Route::get('/api/produtos/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarProdutos']);
        Route::get('/api/centro-custos', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCusto']);
        Route::get('/api/centro-custos/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCustoAutocomplete']);
        Route::get('/api/rotas/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarTodasRotas']);
        Route::get('/api/rotas/por-centro-custo', [App\Http\Controllers\PedidoComprasController::class, 'buscarRotasPorCentroCusto']);
        Route::get('/api/roteirizacoes/por-rota', [App\Http\Controllers\PedidoComprasController::class, 'buscarRoteirizacoesPorRota']);
        // Tela restrita: Bloquear Itens (perm: bloq_ite)
        Route::middleware('can:bloq_ite')->group(function(){
            Route::get('/pedidos/bloquear-itens', [App\Http\Controllers\PedidoComprasController::class, 'bloquearItensView'])->name('pedidos.bloquear-itens');
            // APIs para a tela
            Route::get('/api/bloq/usuarios', [App\Http\Controllers\PedidoComprasController::class, 'bloqBuscarUsuarios']);
            Route::get('/api/bloq/produtos', [App\Http\Controllers\PedidoComprasController::class, 'bloqBuscarProdutos']);
            Route::get('/api/bloq/{userId}/listar', [App\Http\Controllers\PedidoComprasController::class, 'bloqListarPorUsuario']);
            Route::post('/api/bloq', [App\Http\Controllers\PedidoComprasController::class, 'bloqAdicionar']);
            Route::delete('/api/bloq/{userId}/{produtoId}', [App\Http\Controllers\PedidoComprasController::class, 'bloqRemover']);
        });
    });

    Route::middleware(['can:autorizacao-pedidos','throttle:120,1'])->group(function () {
        Route::get('/pedidos/autorizacao', [App\Http\Controllers\PedidoComprasController::class, 'autorizacao'])->name('pedidos.autorizacao');
        Route::get('/pedidos/autorizacao/pendentes', [App\Http\Controllers\PedidoComprasController::class, 'autorizacoesPendentesView'])->name('pedidos.autorizacao.pendentes');
        Route::get('/pedidos/autorizacao/aprovadas', [App\Http\Controllers\PedidoComprasController::class, 'autorizacoesAprovadasView'])->name('pedidos.autorizacao.aprovadas');
        Route::get('/pedidos/autorizacao/rejeitadas', [App\Http\Controllers\PedidoComprasController::class, 'autorizacoesRejeitadasView'])->name('pedidos.autorizacao.rejeitadas');

        Route::get('/api/pedidos-pendentes', [App\Http\Controllers\PedidoComprasController::class, 'pedidosPendentes']);
        Route::get('/api/pedidos-pendentes-agrupados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosPendentesAgrupados']);
        Route::get('/api/pedidos-agrupado/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'detalhesPedidoAgrupado']);
        Route::put('/api/pedidos-agrupado/{hash}/aprovar', [App\Http\Controllers\PedidoComprasController::class, 'aprovarGrupo']);
        Route::put('/api/pedidos-agrupado/{hash}/rejeitar', [App\Http\Controllers\PedidoComprasController::class, 'rejeitarGrupo']);
        Route::delete('/api/pedidos-agrupado/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'excluirGrupo']);
        Route::post('/api/pedidos-agrupado/{hash}/mensagem', [App\Http\Controllers\PedidoComprasController::class, 'mensagemGrupo']);
        // Atualização de itens (apenas Admin)
        Route::put('/api/pedidos-agrupado/{hash}/itens', [App\Http\Controllers\PedidoComprasController::class, 'atualizarItensGrupo']);
        Route::post('/api/pedidos-agrupado/{hash}/itens', [App\Http\Controllers\PedidoComprasController::class, 'adicionarItemGrupo']);
        // Consulta de preço unitário por nome (para recálculo imediato no modal)
        Route::get('/api/estoque/preco', [App\Http\Controllers\PedidoComprasController::class, 'precoProduto']);
        // Autocomplete de produtos (nome ou código) – endpoint específico do módulo de Pedidos de Compras
        Route::get('/api/estoque-pedido/produtos/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarProdutosEstoque']);
        Route::get('/api/pedidos-aprovados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosAprovados']);
        Route::get('/api/pedidos-aprovados-agrupados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosAprovadosAgrupados']);
        Route::get('/api/pedidos-rejeitados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosRejeitados']);
        Route::get('/api/pedidos-rejeitados-agrupados', [App\Http\Controllers\PedidoComprasController::class, 'pedidosRejeitadosAgrupados']);
        // Permissões do usuário logado (para controlar botões no front-end)
        Route::get('/api/usuario/permissoes', [App\Http\Controllers\PedidoComprasController::class, 'usuarioPermissoes']);
        Route::put('/api/pedidos/{id}/aprovar', [App\Http\Controllers\PedidoComprasController::class, 'aprovar']);
        Route::put('/api/pedidos/{id}/rejeitar', [App\Http\Controllers\PedidoComprasController::class, 'rejeitar']);
        // Excluir item específico (somente Admin)
        Route::delete('/api/pedidos/{id}', [App\Http\Controllers\PedidoComprasController::class, 'excluirItem']);
    });

    // Endpoints utilitários disponíveis para telas autenticadas (ex.: Controle de Estoque)
    Route::middleware(['auth','throttle:120,1'])->group(function () {
        Route::get('/api/centros-custo', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCusto']);
        Route::get('/api/centros-custo/buscar', [App\Http\Controllers\PedidoComprasController::class, 'buscarCentrosCustoAutocomplete']);
    });

    // Página de histórico e interações dos pedidos do próprio usuário
    Route::middleware(['can:Pedidos de Compras'])->group(function () {
        Route::get('/pedidos/minhas-interacoes', [App\Http\Controllers\PedidoComprasController::class, 'minhasInteracoesView'])->name('pedidos.minhas.interacoes');
        Route::get('/api/pedidos/minhas-interacoes', [App\Http\Controllers\PedidoComprasController::class, 'minhasInteracoesData']);
        Route::get('/api/pedidos/{id}/interacoes', [App\Http\Controllers\PedidoComprasController::class, 'interacoesPorPedido']);
        Route::post('/api/pedidos/{id}/interagir', [App\Http\Controllers\PedidoComprasController::class, 'enviarInteracaoSolicitante']);
    });

    // Acompanhar Pedido (somente leitura) – permissão: Acompanhar Pedido
    Route::middleware(['can:Acompanhar Pedido','throttle:60,1'])->group(function () {
        Route::get('/pedidos/acompanhar', [App\Http\Controllers\PedidoComprasController::class, 'acompanharView'])->name('pedidos.acompanhar');
        Route::get('/pedidos/acompanhar/pendentes', function(){ return view('pedidos.acompanhar_pendentes'); })->name('pedidos.acompanhar.pendentes');
        Route::get('/pedidos/acompanhar/aprovadas', function(){ return view('pedidos.acompanhar_aprovadas'); })->name('pedidos.acompanhar.aprovadas');
        Route::get('/pedidos/acompanhar/rejeitadas', function(){ return view('pedidos.acompanhar_rejeitadas'); })->name('pedidos.acompanhar.rejeitadas');
        Route::get('/api/pedidos/acompanhar/lista', [App\Http\Controllers\PedidoComprasController::class, 'acompanharLista']);
        Route::get('/api/pedidos/acompanhar/{hash}', [App\Http\Controllers\PedidoComprasController::class, 'acompanharDetalhes']);
    });

    // Produtos (estoque_pedido) – permissão: ati_prod
    Route::middleware(['can:ati_prod','throttle:60,1'])->group(function () {
        Route::get('/pedidos/produtos', [App\Http\Controllers\PedidoComprasController::class, 'produtosView'])->name('pedidos.produtos');
        Route::get('/api/estoque-pedido', [App\Http\Controllers\PedidoComprasController::class, 'produtosListar']);
        Route::post('/api/estoque-pedido', [App\Http\Controllers\PedidoComprasController::class, 'produtosCriar']);
        Route::put('/api/estoque-pedido/{id}/toggle', [App\Http\Controllers\PedidoComprasController::class, 'produtosToggleAtivo']);
        Route::put('/api/estoque-pedido/{id}', [App\Http\Controllers\PedidoComprasController::class, 'produtosAtualizar']);
        Route::put('/api/pedidos-agrupado/{hash}/cabecalho', [App\Http\Controllers\PedidoComprasController::class, 'atualizarCabecalhoGrupo']);
    });

    // Duplicar Pedido – permissão: dup_ped
    Route::middleware(['can:dup_ped','throttle:60,1'])->group(function () {
        Route::get('/pedidos/duplicar', [App\Http\Controllers\PedidoComprasController::class, 'duplicarView'])->name('pedidos.duplicar');
        Route::get('/api/pedidos/meus-pedidos', [App\Http\Controllers\PedidoComprasController::class, 'meusPedidos']);
        Route::post('/api/pedidos/duplicar/{numPedido}', [App\Http\Controllers\PedidoComprasController::class, 'duplicarPedido']);
        Route::get('/api/pedidos/itens/{numPedido}', [App\Http\Controllers\PedidoComprasController::class, 'itensPedidoUsuario']);
    });

    // Rotas de Permissões
    Route::middleware(['auth'])->group(function () {
        // Rota para a página de permissões - usando o controller
        Route::get('/permissoes', [App\Http\Controllers\PermissoesController::class, 'index'])->name('admin.permissoes');

        // API para gerenciar permissões
        Route::prefix('api')->group(function () {
            Route::get('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'show']);
            Route::post('/permissoes', [App\Http\Controllers\PermissoesController::class, 'store']);
            Route::put('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'update']);
            Route::delete('/permissoes/{id}', [App\Http\Controllers\PermissoesController::class, 'destroy']);
        });

        // Rota para a página de usuários (listagem)
        Route::get('/gerenciar-perfis', function () {
            // Buscar usuários diretamente do banco de dados com join
            $usuarios = DB::table('users')
                ->select('users.*', 'profiles.name as profile_name')
                ->leftJoin('profiles', 'users.profile_id', '=', 'profiles.id')
                ->get();
            $perfis = DB::table('profiles')->get();
            return view('admin.gerenciar-perfis', compact('usuarios', 'perfis'));
        })->name('admin.usuarios');

        // TESTE DE CONEXÃO FORÇADA (apenas ambiente local)
        if (app()->environment('local')) {
        Route::get('/perfis', function () {
            // Testar conexão forçada para o banco correto
            try {
                $pdo = new PDO(
                    'mysql:host=127.0.0.1;dbname=laravel_beta2',
                    'root',
                    '',
                    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
                );
                
                $stmt = $pdo->query('SELECT COUNT(*) as count FROM permissions');
                $permissionsCount = $stmt->fetch()['count'];
                
                $stmt = $pdo->query('SELECT COUNT(*) as count FROM profiles');
                $profilesCount = $stmt->fetch()['count'];
                
                $stmt = $pdo->query('SELECT * FROM permissions LIMIT 5');
                $permissions = $stmt->fetchAll(PDO::FETCH_OBJ);
                
                dd([
                    'CONEXAO_DIRETA_PDO' => 'SUCESSO',
                    'BANCO_CONECTADO' => 'laravel_beta2',
                    'PERMISSIONS_COUNT_PDO' => $permissionsCount,
                    'PROFILES_COUNT_PDO' => $profilesCount,
                    'PERMISSIONS_DATA_PDO' => $permissions,
                    'VS_LARAVEL_DB_NAME' => DB::connection()->getDatabaseName(),
                    'VS_LARAVEL_PERMISSIONS_COUNT' => DB::table('permissions')->count(),
                    'ENV_DB_DATABASE' => env('DB_DATABASE', 'NAO_DEFINIDO'),
                ]);
                
            } catch (Exception $e) {
                dd([
                    'ERRO_CONEXAO_PDO' => $e->getMessage(),
                    'LARAVEL_DB_NAME' => DB::connection()->getDatabaseName(),
                    'ENV_DB_DATABASE' => env('DB_DATABASE', 'NAO_DEFINIDO'),
                ]);
            }
        });
        }
        
        // Rota para exibir um perfil específico - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::get('/perfis/{id}', function ($id) {
            // Buscar perfis, perfil selecionado e permissões diretamente do banco de dados
            $perfis = DB::table('profiles')->get();
            $perfilSelecionado = DB::table('profiles')->where('id', $id)->first();
            
            if (!$perfilSelecionado) {
                return redirect('/perfis')->with('error', 'Perfil não encontrado');
            }
            
            $permissoes = DB::table('permissions')->get();
            $permissoesSelecionadas = DB::table('profile_permissions')
                ->where('profile_id', $id)
                ->pluck('permission_id')
                ->toArray();
                
            return view('admin.perfis', compact('perfis', 'perfilSelecionado', 'permissoes', 'permissoesSelecionadas'));
        });
        }
        
        // Rota para criar um novo perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::post('/perfis', function (Request $request) {
            $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name',
                'description' => 'nullable|string',
            ]);
            
            // Inserir diretamente no banco de dados
            DB::table('profiles')->insert([
                'name' => $request->name,
                'description' => $request->description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            return redirect('/perfis')->with('success', 'Perfil criado com sucesso');
        });
        }
        
        // Rota para atualizar um perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::put('/perfis/{id}', function (Request $request, $id) {
            $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name,' . $id,
                'description' => 'nullable|string',
            ]);
            
            // Atualizar diretamente no banco de dados
            DB::table('profiles')->where('id', $id)->update([
                'name' => $request->name,
                'description' => $request->description,
                'updated_at' => now(),
            ]);
            
            // Sincronizar permissões diretamente no banco de dados
            if ($request->has('permissions')) {
                // Remover permissões existentes
                DB::table('profile_permissions')->where('profile_id', $id)->delete();
                
                // Adicionar novas permissões
                foreach ($request->permissions as $permissionId) {
                    DB::table('profile_permissions')->insert([
                        'profile_id' => $id,
                        'permission_id' => $permissionId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                // Se não enviou permissões, remover todas
                DB::table('profile_permissions')->where('profile_id', $id)->delete();
            }
            
            return redirect('/perfis/' . $id)->with('success', 'Perfil atualizado com sucesso');
        });
        }
        
        // Rota para excluir um perfil - acesso direto ao banco (apenas local)
        if (app()->environment('local')) {
        Route::delete('/perfis/{id}', function ($id) {
            // Remover relacionamentos primeiro diretamente do banco de dados
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            
            // Remover perfil diretamente do banco de dados
            DB::table('profiles')->where('id', $id)->delete();
            
            return redirect('/perfis')->with('success', 'Perfil excluído com sucesso');
        });
        }
        
        // API para gerenciar permissões
        Route::prefix('api')->group(function () {
            // Criar permissão
            Route::post('/permissoes', function (Request $request) {
                $request->validate([
                    'name' => 'required|string|max:255|unique:permissions,name',
                    'code' => 'nullable|string|max:255|unique:permissions,code',
                    'description' => 'nullable|string'
                ]);
                
                $id = DB::table('permissions')->insertGetId([
                    'name' => $request->name,
                    'code' => $request->code ?? strtolower(str_replace(' ', '_', $request->name)),
                    'description' => $request->description,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Permissão criada com sucesso',
                    'data' => $permissao
                ], 201);
            });
            
            // Atualizar permissão
            Route::put('/permissoes/{id}', function (Request $request, $id) {
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                if (!$permissao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Permissão não encontrada'
                    ], 404);
                }
                
                $request->validate([
                    'name' => 'required|string|max:255|unique:permissions,name,' . $id,
                    'code' => 'nullable|string|max:255|unique:permissions,code,' . $id,
                    'description' => 'nullable|string'
                ]);
                
                DB::table('permissions')->where('id', $id)->update([
                    'name' => $request->name,
                    'code' => $request->code ?? $permissao->code,
                    'description' => $request->description,
                    'updated_at' => now()
                ]);
                
                $permissaoAtualizada = DB::table('permissions')->where('id', $id)->first();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Permissão atualizada com sucesso',
                    'data' => $permissaoAtualizada
                ]);
            });
            
            // Excluir permissão
            Route::delete('/permissoes/{id}', function ($id) {
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                if (!$permissao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Permissão não encontrada'
                    ], 404);
                }
                
                // Remover relacionamentos primeiro
                DB::table('profile_permissions')->where('permission_id', $id)->delete();
                
                // Remover permissão
                DB::table('permissions')->where('id', $id)->delete();
                
                return response()->json([
                    'success' => true,
                    'message' => 'Permissão excluída com sucesso'
                ]);
            });
            
            // Obter permissão específica
            Route::get('/permissoes/{id}', function ($id) {
                $permissao = DB::table('permissions')->where('id', $id)->first();
                
                if (!$permissao) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Permissão não encontrada'
                    ], 404);
                }
                
                return response()->json([
                    'success' => true,
                    'data' => $permissao
                ]);
            });
        });
    });


    // Rota para listar todos os usuários
    Route::get('/api/usuarios/listar', function() {
        $usuarios = \App\Models\User::with('profile')->get();
        return response()->json([
            'success' => true,
            'data' => $usuarios
        ]);
    })->middleware(['can:gerenciar-usuarios','throttle:60,1']);
    // Rota para obter dados de usuários via API
    Route::get('/api/usuarios/{id}', [UsuariosController::class, 'show'])->middleware(['auth','throttle:60,1']);
    // API para atualizar usuário
    Route::put('/api/usuarios/{id}', function(\Illuminate\Http\Request $request, $id) {
        try {
            $usuario = \App\Models\User::findOrFail($id);
            
            $validacao = [
                'name' => 'required|string|max:255',
                'profile_id' => 'nullable|exists:profiles,id'
            ];
            
            // Adicionar validação de senha apenas se foi enviada
            if ($request->filled('password')) {
                $validacao['password'] = 'required|string|min:6|confirmed';
            }
            
            $request->validate($validacao);
            
            // Atualizar dados básicos
            $usuario->name = $request->name;
            $usuario->profile_id = $request->profile_id;
            
            // Atualizar senha apenas se foi enviada
            if ($request->filled('password')) {
                $usuario->password = bcrypt($request->password);
            }
            
            $usuario->save();
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $usuario
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário: ' . $e->getMessage()
            ], 500);
        }
    });

    // Rota para atualizar usuário
    Route::post('/atualizar-usuario', [UsuariosController::class, 'update']);

    // Rota para ativar/desativar usuário
    Route::post('/toggle-user-status', [UsuariosController::class, 'toggleStatus']);

    // Rota para atualizar o perfil do usuário
    Route::post('/atualizar-perfil-usuario', function (Request $request) {
        try {
            // Validar dados
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'profile_id' => 'required|exists:profiles,id'
            ]);
            
            // Atualizar o usuário diretamente no banco
            $resultado = DB::table('users')
                ->where('id', $validated['user_id'])
                ->update(['profile_id' => $validated['profile_id']]);
            
            if ($resultado) {
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Nenhuma alteração foi realizada'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar perfil: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    });

    // APIs para gerenciamento de perfis
    Route::middleware(['auth:sanctum'])->prefix('api')->group(function () {
        // Permissões
        Route::get('/permissoes/listar', [PermissoesController::class, 'listar']);
        Route::get('/permissoes', [PermissoesController::class, 'listar']);
        Route::get('/permissoes/{id}', [PermissoesController::class, 'obter']);
        Route::post('/permissoes', [PermissoesController::class, 'store']);
        Route::put('/permissoes/{id}', [PermissoesController::class, 'update']);
        Route::delete('/permissoes/{id}', [PermissoesController::class, 'destroy']);

        // Perfis
        Route::get('/perfis', [App\Http\Controllers\PermissoesController::class, 'listarPerfis']);
        Route::get('/perfis/{id}', [App\Http\Controllers\PermissoesController::class, 'obterPerfil']);
        Route::post('/perfis', function (Request $request) {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255|unique:profiles,name',
                'description' => 'nullable|string'
            ]);
            
            $perfil = \App\Models\Profile::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Perfil criado com sucesso',
                'data' => $perfil
            ]);
        });
        Route::put('/perfis/{id}', function ($id) {
            try {
                $perfil = \App\Models\Profile::findOrFail($id);
                
                request()->validate([
                    'name' => 'required|string|max:255|unique:profiles,name,' . $id,
                    'description' => 'nullable|string',
                    'permissions' => 'nullable|array',
                    'permissions.*' => 'integer'
                ]);
                
                $perfil->update([
                    'name' => request('name'),
                    'description' => request('description')
                ]);
                
                if (request()->has('permissions')) {
                    DB::table('profile_permissions')
                        ->where('profile_id', $id)
                        ->delete();
                    
                    $permissions = request('permissions');
                    
                    $data = array_map(function($permissionId) use ($id) {
                        return [
                            'profile_id' => $id,
                            'permission_id' => (int)$permissionId
                        ];
                    }, $permissions);
                    
                    DB::table('profile_permissions')->insert($data);
                }
                
                return response()->json([
                    'success' => true,
                    'message' => 'Perfil atualizado com sucesso',
                    'data' => $perfil->load('permissions')
                ]);
                
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao atualizar perfil: ' . $e->getMessage()
                ], 500);
            }
        });
        Route::delete('/perfis/{id}', function ($id) {
            $perfil = \App\Models\Profile::findOrFail($id);
            DB::table('users')->where('profile_id', $id)->update(['profile_id' => null]);
            DB::table('profile_permissions')->where('profile_id', $id)->delete();
            $perfil->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Perfil excluído com sucesso'
            ]);
        });
    });


});

Auth::routes();

// Redirecionamentos para URLs sem o prefixo /admin
Route::get('/gerenciar-permissoes', function () {
    return redirect('/permissoes');
});

// Rota de perfis
Route::get('/perfis', function () {
    $perfis = \App\Models\Profile::all();
    $permissoes = \App\Models\Permission::all();
    return view('admin.perfis', compact('perfis', 'permissoes'));
})->middleware(['auth']);

// Exibir um perfil específico (produção) – rota dedicada para evitar conflito com métodos não-GET
Route::get('/perfis/show/{id}', function ($id) {
    $perfis = DB::table('profiles')->get();
    $perfilSelecionado = DB::table('profiles')->where('id', $id)->first();
    if (!$perfilSelecionado) {
        return redirect('/perfis')->with('error', 'Perfil não encontrado');
    }
    $permissoes = DB::table('permissions')->get();
    $permissoesSelecionadas = DB::table('profile_permissions')
        ->where('profile_id', $id)
        ->pluck('permission_id')
        ->toArray();
    return view('admin.perfis', compact('perfis', 'perfilSelecionado', 'permissoes', 'permissoesSelecionadas'));
})->middleware(['auth'])->name('perfis.show');

// Rotas de criação/edição/exclusão de perfis para produção (fora do bloco "local")
Route::post('/perfis', [\App\Http\Controllers\Admin\PerfilController::class, 'store'])
    ->middleware(['auth'])
    ->name('perfis.store');

Route::put('/perfis/{id}', [\App\Http\Controllers\Admin\PerfilController::class, 'update'])
    ->middleware(['auth'])
    ->name('perfis.update');

// Compat: aceitar POST para atualização quando _method spoofing não for aplicado no servidor
Route::post('/perfis/{id}', [\App\Http\Controllers\Admin\PerfilController::class, 'update'])
    ->middleware(['auth'])
    ->name('perfis.update.post');

Route::delete('/perfis/{id}', [\App\Http\Controllers\Admin\PerfilController::class, 'destroy'])
    ->middleware(['auth'])
    ->name('perfis.destroy');

// Licenciamento (Plano B) - protegido por autenticação
Route::middleware(['auth'])->group(function () {
    Route::get('/license', [LicenseController::class, 'index'])->name('license.index');
    Route::post('/license/upload', [LicenseController::class, 'upload'])->name('license.upload');
});

// Rotas de administração
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::middleware(['can:gerenciar-permissoes'])->group(function () {
        Route::get('/gerenciar-permissoes', function () {
            $users = \App\Models\User::with('profile')->get();
            $profiles = \App\Models\Profile::all();
            return view('admin.gerenciar-permissoes', compact('users', 'profiles'));
        })->name('gerenciar-permissoes');
    });
    
    // Rotas de conflito removidas
});

// APIs para gerenciamento de permissões
Route::middleware(['auth','throttle:60,1'])->prefix('api')->group(function () {
    // Obter todas as permissões
    Route::get('/permissoes/listar', function() {
        $permissoes = \App\Models\Permission::all();
        return response()->json([
            'success' => true,
            'data' => $permissoes
        ]);
    });
    
    // Obter uma permissão específica
    Route::get('/permissoes/{id}', function($id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $permissao
        ]);
    });
    
    // Criar uma nova permissão
    Route::post('/permissoes', function(\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
            'code' => 'nullable|string',
            'description' => 'nullable|string'
        ]);
        
        $permissao = \App\Models\Permission::create($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Permissão criada com sucesso',
            'data' => $permissao
        ]);
    });
    
    // Atualizar uma permissão
    Route::put('/permissoes/{id}', function(\Illuminate\Http\Request $request, $id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        
        $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name,' . $id,
            'code' => 'nullable|string',
            'description' => 'nullable|string'
        ]);
        
        $permissao->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Permissão atualizada com sucesso',
            'data' => $permissao
        ]);
    });
    
    // Excluir uma permissão
    Route::delete('/permissoes/{id}', function($id) {
        $permissao = \App\Models\Permission::findOrFail($id);
        $permissao->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Permissão excluída com sucesso'
        ]);
    });
});

// API para usuários (usado no select do formulário) — protegido
Route::get('/api/usuarios', function() {
    return App\Models\User::select('id', 'name')->get();
})->middleware(['auth','throttle:60,1'])->name('api.usuarios');

// (removida duplicidade) API para obter dados de usuário já definida anteriormente via UsuariosController

/* Rota temporária para corrigir status
Route::get('/corrigir-status-rh', function () {
    // Buscar registros com status "Concluída" (antigo)
    $registros = App\Models\RHProblema::where('status', 'Concluída')->get();
    
    $count = $registros->count();
    echo "Encontrados {$count} registros com status 'Concluída'<br>";
    
    if ($count > 0) {
        // Atualizar todos para "Concluído" (novo)
        foreach ($registros as $registro) {
            echo "Atualizando ID {$registro->id}<br>";
            $registro->status = 'Concluído';
            $registro->save();
        }
        
        echo 'Correção concluída com sucesso!<br>';
    } else {
        echo 'Nenhum registro precisava ser corrigido.<br>';
    }
    
    // Verificar os status existentes
    $status = DB::table('rh_problemas')
        ->select('status')
        ->distinct()
        ->orderBy('status')
        ->get()
        ->pluck('status');
        
    echo 'Status disponíveis no banco:<br>';
    foreach ($status as $st) {
        echo " - {$st}<br>";
    }
    
    return "Processo concluído!";
})->middleware('auth');*/

// Rota para atualizar diretamente o status
/*Route::get('/atualizar-status/{id}/{status}', function ($id, $status) {
    try {
        // Verificar se o status é válido
        if (!in_array($status, ['Pendente', 'Em andamento', 'Concluído', 'No prazo'])) {
            return "Status inválido. Use: Pendente, Em andamento, Concluído ou No prazo";
        }
        
        // Buscar o registro
        $problema = \App\Models\RHProblema::findOrFail($id);
        $statusAntigo = $problema->status;
        
        // Atualizar o status
        $problema->status = $status;
        $resultado = $problema->save();
        
        if ($resultado) {
            // Verificar se a atualização foi realmente aplicada
            $problemaAtualizado = \App\Models\RHProblema::findOrFail($id);
            
            if ($problemaAtualizado->status === $status) {
                return "Status atualizado com sucesso de '{$statusAntigo}' para '{$status}'!";
            } else {
                return "Falha na verificação. Status no banco: '{$problemaAtualizado->status}'";
            }
        } else {
            return "Erro ao salvar: operação de save() retornou false";
        }
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
})->middleware('auth');*/

// Rota de emergência para corrigir o registro ID 70
Route::get('/corrigir-registro-70', function() {
    try {
        // Verificar status atual
        $statusAtual = DB::table('rh_problemas')->where('id', 70)->value('status');
        echo "Status atual do registro #70: " . $statusAtual . "<br>";
        
        // Forçar atualização direta no banco
        $atualizado = DB::table('rh_problemas')
            ->where('id', 70)
            ->update(['status' => 'Pendente']);
        
        echo "Atualização forçada: " . ($atualizado ? "Sim" : "Não") . "<br>";
        
        // Verificar status após atualização
        $novoStatus = DB::table('rh_problemas')->where('id', 70)->value('status');
        echo "Novo status do registro #70: " . $novoStatus . "<br>";
        
        // Limpar cache do registro
        Cache::forget('rh_problema_70');
        echo "Cache limpo para o registro #70";
    } catch (\Exception $e) {
        return "Erro: " . $e->getMessage();
    }
})->middleware(['auth']);



// Rotas de Debug para depuração
Route::get('/debug/enable-sql-log', function() {
    \DB::enableQueryLog();
    \Log::info('Logging SQL ativado via AJAX');
    return response()->json(['status' => 'success', 'message' => 'SQL logging enabled']);
})->middleware(['auth','throttle:30,1']);

// Rota de teste específica para diagnóstico de problemas com datas
Route::get('/debug/teste-data/{data?}', function($data = null) {
    // Se não for fornecida uma data, usar um valor padrão
    if (!$data) {
        $data = "31/03/2025 11:54";
    }
    
    \Log::info("Iniciando teste de data com: " . $data);
    
    // Ativar log de consultas
    \DB::enableQueryLog();
    
    // Array para armazenar os resultados dos testes
    $resultados = [];
    
    try {
        // 1. Teste direto com Carbon
        try {
            $carbon = \Carbon\Carbon::createFromFormat('d/m/Y H:i', $data);
            $resultados['carbon_direto'] = [
                'status' => 'sucesso',
                'resultado' => $carbon->format('Y-m-d H:i:s')
            ];
        } catch (\Exception $e) {
            $resultados['carbon_direto'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // 2. Teste com regex e construção manual
        try {
            // Sanitizar a data
            $dataSanitizada = preg_replace('/[^\d\/: ]/', '', trim($data));
            
            if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})$/', $dataSanitizada, $matches)) {
                $dia = (int)$matches[1];
                $mes = (int)$matches[2];
                $ano = (int)$matches[3];
                $hora = (int)$matches[4];
                $minuto = (int)$matches[5];
                
                // Formatação direta para SQL
                $dataSQL = sprintf('%04d-%02d-%02d %02d:%02d:00', $ano, $mes, $dia, $hora, $minuto);
                
                $resultados['regex_manual'] = [
                    'status' => 'sucesso',
                    'dados_extraidos' => [
                        'dia' => $dia,
                        'mes' => $mes,
                        'ano' => $ano,
                        'hora' => $hora,
                        'minuto' => $minuto
                    ],
                    'sql_formatado' => $dataSQL
                ];
                
                // Verificar se o Carbon aceita esta string
                try {
                    $carbonTeste = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $dataSQL);
                    $resultados['regex_manual']['verificacao_carbon'] = [
                        'status' => 'sucesso',
                        'resultado' => $carbonTeste->format('Y-m-d H:i:s')
                    ];
                } catch (\Exception $e) {
                    $resultados['regex_manual']['verificacao_carbon'] = [
                        'status' => 'erro',
                        'mensagem' => $e->getMessage()
                    ];
                }
            } else {
                $resultados['regex_manual'] = [
                    'status' => 'erro',
                    'mensagem' => 'A data não corresponde ao formato esperado',
                    'data_sanitizada' => $dataSanitizada
                ];
            }
        } catch (\Exception $e) {
            $resultados['regex_manual'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // 3. Teste com o banco de dados
        try {
            // Criar um problema de teste
            $problema = new \App\Models\RHProblema();
            $problema->descricao = 'Teste de data - ' . now();
            $problema->status = 'Pendente';
            $problema->prioridade = 'media';
            $problema->id_usuario = 1;
            
            // Definir a data diretamente como string no formato SQL
            $dataSQL = $resultados['regex_manual']['sql_formatado'] ?? null;
            
            if ($dataSQL) {
                $problema->prazo_entrega = $dataSQL;
                $resultado = $problema->save();
                
                $resultados['teste_bd'] = [
                    'status' => $resultado ? 'sucesso' : 'erro',
                    'id_problema' => $problema->id,
                    'data_salva' => $problema->prazo_entrega
                ];
                
                // Limpar o teste (excluir o registro)
                if ($resultado) {
                    $problema->delete();
                }
            } else {
                $resultados['teste_bd'] = [
                    'status' => 'pulado',
                    'motivo' => 'Não foi possível obter uma data SQL válida'
                ];
            }
        } catch (\Exception $e) {
            $resultados['teste_bd'] = [
                'status' => 'erro',
                'mensagem' => $e->getMessage()
            ];
        }
        
        // Log das consultas SQL
        $resultados['consultas_sql'] = \DB::getQueryLog();
        
        return response()->json([
            'status' => 'sucesso',
            'data_original' => $data,
            'resultados' => $resultados
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'erro',
            'mensagem' => $e->getMessage(),
            'data_original' => $data,
            'resultados' => $resultados ?? []
        ], 500);
    }
})->middleware(['auth','throttle:30,1']);



// APIs para gerenciamento de perfis
Route::middleware(['auth'])->prefix('api')->group(function () {
    // Obter todos os perfis
    Route::get('/perfis/listar', function() {
        $perfis = \App\Models\Profile::all();
        return response()->json([
            'success' => true,
            'data' => $perfis
        ]);
    });
    
    // Obter um perfil específico
    Route::get('/perfis/{id}', function($id) {
        $perfil = \App\Models\Profile::with('permissions')->findOrFail($id);
        return response()->json([
            'success' => true,
            'data' => $perfil
        ]);
    });
});
