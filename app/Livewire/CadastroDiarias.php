<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Funcionario;
use App\Models\Diaria;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\On;

class CadastroDiarias extends Component
{
    public $nome;
    public $departamento;
    public $funcao;
    public $valor;
    public $horasExtras;
    public $qtdDiaria;
    public $referencia;
    public $observacao;
    public $empresa;
    public $scores = [];
    
    public $funcionarioSelecionado;
    public $sugestoesFuncionarios;
    public $mostrarSugestoes = true;
    public $diariasSalvas = [];
    public $referencias = ['Compensação', 'Feriado', 'Horas acumuladas', 'Folga', 'Teste', 'Diária'];
    public $pesquisaFuncionario = '';
    public $departamentos = []; // Lista de departamentos disponíveis

    protected $rules = [
        'nome' => 'required',
        'departamento' => 'required',
        'funcao' => 'required',
        'valor' => 'required|numeric',
        'horasExtras' => 'required|integer|min:1',
        'qtdDiaria' => 'required|numeric',
        'referencia' => 'required',
        'empresa' => 'required'
    ];

    protected $messages = [
        'horasExtras.required' => 'A quantidade é obrigatória.',
        'horasExtras.integer' => 'A quantidade deve ser um número inteiro.',
        'horasExtras.min' => 'A quantidade deve ser pelo menos 1.'
    ];

    public function mount($departamentos = [])
    {
        try {
            logger('CadastroDiarias::mount() - Inicializando componente');
            $this->diariasSalvas = Session::get('diarias_temp', []);
            $this->sugestoesFuncionarios = collect([]);
            $this->departamentos = $departamentos;
            logger('CadastroDiarias::mount() - Componente inicializado com sucesso', [
                'qtd_diarias_salvas' => count($this->diariasSalvas),
                'qtd_departamentos' => count($this->departamentos)
            ]);
        } catch (\Exception $e) {
            logger('Erro ao inicializar componente CadastroDiarias', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public function updatedPesquisaFuncionario()
    {
        // Reativar a exibição de sugestões quando o usuário começa a digitar novamente
        $this->mostrarSugestoes = true;
        
        // Só buscar se tiver pelo menos 2 caracteres
        if (strlen($this->pesquisaFuncionario) >= 2) {
            try {
                $this->sugestoesFuncionarios = Funcionario::select('id', 'nome', 'departamento', 'funcao', 'valor', 'Empresa', 'empresa')
                    ->where('nome', 'like', '%' . $this->pesquisaFuncionario . '%')
                    ->orderBy('nome')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                logger('Erro ao buscar funcionários: ' . $e->getMessage());
                $this->sugestoesFuncionarios = collect([]);
            }
        } else {
            $this->sugestoesFuncionarios = collect([]);
        }
    }

    public function selecionarFuncionario($id)
    {
        $funcionario = Funcionario::find($id);
        if ($funcionario) {
            $this->funcionarioSelecionado = $funcionario;
            $this->nome = $funcionario->nome;
            $this->departamento = $funcionario->departamento;
            $this->funcao = $funcionario->funcao;
            $this->valor = $funcionario->valor;
            $this->empresa = $funcionario->Empresa ?? $funcionario->empresa ?? '';
            
            // Limpar sugestões e definir campo de pesquisa 
            $this->sugestoesFuncionarios = collect([]);
            $this->pesquisaFuncionario = $funcionario->nome;
            // Esconder as sugestões após seleção
            $this->mostrarSugestoes = false;
            
            // Calcular a diária se houver um valor preenchido no campo horasExtras
            if (!empty($this->horasExtras)) {
                $this->calcularDiaria();
            }
            
            // Dispatch um evento para forçar a atualização da UI
            $this->dispatch('funcionario-selecionado', [
                'nome' => $this->nome,
                'departamento' => $this->departamento,
                'funcao' => $this->funcao,
                'valor' => $this->valor,
                'empresa' => $this->empresa
            ]);
        }
    }

    public function updatedHorasExtras()
    {
        $this->calcularDiaria();
    }

    public function calcularDiaria()
    {
        // Somente calcular se horasExtras for preenchido e válido
        if (!empty($this->horasExtras) && is_numeric($this->horasExtras) && $this->horasExtras >= 1) {
            // Converter para inteiro
            $this->horasExtras = (int)$this->horasExtras;
            
            $valor = $this->valor ?: 0;
            
            // Calcular o valor da diária baseado na quantidade e valor base
            $this->qtdDiaria = $this->horasExtras * floatval($valor);
            
            // Dispatch do evento apenas se houver um valor calculado
            if ($this->qtdDiaria > 0) {
                $this->dispatch('diaria-calculada', $this->qtdDiaria);
            }
        } else {
            // Se o valor não for válido, zera o valor da diária
            $this->qtdDiaria = 0;
        }
    }

    public function salvar()
    {
        $this->validate();

        // Obter o nome do usuário logado como gerente
        $nomeUsuarioLogado = auth()->user()->name ?? 'Sistema';

        $novaDiaria = [
            'id' => uniqid(),
            'nome' => $this->nome,
            'departamento' => $this->departamento,
            'funcao' => $this->funcao,
            'valor' => $this->valor,
            'horasExtras' => $this->horasExtras,
            'qtdDiaria' => $this->qtdDiaria,
            'referencia' => $this->referencia,
            'observacao' => $this->observacao,
            'empresa' => $this->empresa,
            'gerente' => $nomeUsuarioLogado // Nome do usuário logado como gerente
        ];

        $this->diariasSalvas[] = $novaDiaria;
        Session::put('diarias_temp', $this->diariasSalvas);

        $this->reset(['nome', 'departamento', 'funcao', 'valor', 'horasExtras', 'qtdDiaria', 'referencia', 'observacao', 'empresa', 'funcionarioSelecionado', 'pesquisaFuncionario']);
        $this->mostrarSugestoes = false;
        
        // Dispara apenas o evento para exibir a notificação personalizada
        $this->dispatch('diaria-adicionada');
    }

    public function removerDiaria($id)
    {
        try {
            logger('Tentando remover diária com ID:', ['id' => $id, 'tipo' => gettype($id)]);
            logger('Lista de IDs antes da remoção:', ['ids' => array_column($this->diariasSalvas, 'id')]);
            
            // Se o ID for numérico string, converte para int para comparação
            if (is_string($id) && is_numeric($id)) {
                $id = (int)$id;
            }
            
            // Verificar se o ID existe em algum item antes de tentar remover
            $itemExiste = false;
            $itemRemovido = null;
            
            foreach ($this->diariasSalvas as $key => $item) {
                // Convertendo ambos para o mesmo tipo para comparação
                $itemId = $item['id'];
                if (is_string($itemId) && is_numeric($itemId) && is_numeric($id)) {
                    $itemId = (int)$itemId;
                }
                
                if ($itemId == $id) {
                    $itemExiste = true;
                    $itemRemovido = $item;
                    break;
                }
            }
            
            if (!$itemExiste) {
                logger('Erro: ID não encontrado na lista de diárias', ['id_buscado' => $id]);
                $this->dispatch('erro-remover-diaria', ['mensagem' => 'Item não encontrado para exclusão.']);
                return;
            }
            
            // Método mais direto para remover o item pelo ID
            $diariasFiltradas = [];
            foreach ($this->diariasSalvas as $item) {
                $itemId = $item['id'];
                // Convertendo para o mesmo tipo para comparação
                if (is_string($itemId) && is_numeric($itemId) && is_numeric($id)) {
                    $itemId = (int)$itemId;
                }
                
                if ($itemId != $id) {
                    $diariasFiltradas[] = $item;
                }
            }
            
            // Atribuir o array filtrado
            $this->diariasSalvas = $diariasFiltradas;
            
            // Atualizar a sessão
            Session::put('diarias_temp', $this->diariasSalvas);
            
            logger('Lista de IDs após a remoção:', ['ids' => array_column($this->diariasSalvas, 'id')]);
            logger('Item removido com sucesso:', ['item' => $itemRemovido ?? 'Item não encontrado']);
            
            // Disparar evento de sucesso
            $this->dispatch('diaria-removida', [
                'id' => $id,
                'nome' => $itemRemovido['nome'] ?? 'Funcionário',
                'sucesso' => true
            ]);
            
            return true;
        } catch (\Exception $e) {
            logger('Erro ao remover diária:', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id
            ]);
            
            $this->dispatch('erro-remover-diaria', ['mensagem' => 'Erro ao remover item: ' . $e->getMessage()]);
            return false;
        }
    }

    // Listener para eventos de dispatch
    #[On('remover-diaria')]
    public function handleRemoverDiaria($param = [])
    {
        try {
            // Log para depuração do parâmetro recebido
            logger('Método handleRemoverDiaria chamado com parâmetro:', ['param' => $param]);
            
            if (isset($param['id'])) {
                logger('ID encontrado: ' . $param['id']);
                $resultado = $this->removerDiaria($param['id']);
                logger('Resultado da remoção:', ['sucesso' => $resultado ? 'sim' : 'não']);
            } else {
                logger('Erro ao remover diária: ID não fornecido no parâmetro', ['param_completo' => $param]);
                $this->dispatch('erro-remover-diaria', ['mensagem' => 'ID da diária não foi fornecido corretamente.']);
            }
        } catch (\Exception $e) {
            logger('Exceção no handleRemoverDiaria:', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'param' => $param
            ]);
            $this->dispatch('erro-remover-diaria', ['mensagem' => 'Erro interno: ' . $e->getMessage()]);
        }
    }
    
    #[On('salvar-todas-diarias')]
    public function handleSalvarDiarias($param = [])
    {
        try {
            $this->salvarDiarias();
        } catch (\Exception $e) {
            logger('Erro ao salvar diárias via método handleSalvarDiarias:', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->dispatch('erro-salvar-diarias', ['mensagem' => 'Erro interno: ' . $e->getMessage()]);
        }
    }

    public function salvarDiarias()
    {
        // Verificar se há diárias para salvar
        if (empty($this->diariasSalvas)) {
            $this->dispatch('erro-salvar-diarias', ['mensagem' => 'Não há diárias para salvar.']);
            return;
        }
        
        // Proteção contra submissão múltipla
        if (Session::has('processando_diarias') && Session::get('processando_diarias') === true) {
            $this->dispatch('erro-salvar-diarias', ['mensagem' => 'Processo de salvamento já em andamento. Aguarde.']);
            return;
        }
        
        // Marcar como em processamento
        Session::put('processando_diarias', true);
        
        try {
            $dataAtual = Carbon::now();
            
            // Obter o nome do usuário logado
            $nomeUsuarioLogado = auth()->user()->name ?? 'Sistema';
            
            // Ordena as diárias pelo nome
            $diariasSalvasOrdenadas = collect($this->diariasSalvas)->sortBy('nome')->values();
            $primeiraDiaria = $diariasSalvasOrdenadas->first();
            
            // Verificar se a primeira diária existe
            if (!$primeiraDiaria) {
                Session::forget('processando_diarias');
                $this->dispatch('erro-salvar-diarias', ['mensagem' => 'Dados inválidos ao processar diárias.']);
                return;
            }
            
            // Monta a string JSON manualmente para garantir consistência total
            $jsonDados = sprintf(
                '{"nome":"%s","departamento":"%s","funcao":"%s","diaria":%d,"referencia":"%s","data":"%s"}',
                trim(mb_strtoupper($primeiraDiaria['nome'])),
                trim(mb_strtoupper($primeiraDiaria['departamento'])),
                trim(mb_strtoupper($primeiraDiaria['funcao'])),
                (int)$primeiraDiaria['qtdDiaria'],
                trim($primeiraDiaria['referencia']),
                $dataAtual->format('Y-m-d H:i:s')
            );
            
            // Log para debug
            logger('JSON para hash:', ['json' => $jsonDados]);
            
            $hashLote = Hash::make($jsonDados);

            foreach ($this->diariasSalvas as $diariaDados) {
                Diaria::create([
                    'nome' => $diariaDados['nome'],
                    'departamento' => $diariaDados['departamento'],
                    'funcao' => $diariaDados['funcao'],
                    'diaria' => $diariaDados['qtdDiaria'],
                    'referencia' => $diariaDados['referencia'],
                    'observacao' => $diariaDados['observacao'] ?? null,
                    'empresa' => $diariaDados['empresa'] ?? null,
                    'gerente' => $nomeUsuarioLogado, // Nome do usuário logado como gerente
                    'data_inclusao' => $dataAtual,
                    'chave' => $hashLote,
                    'visualizado' => null
                ]);
            }
            
            $this->diariasSalvas = [];
            Session::forget('diarias_temp');
            
            $this->dispatch('diarias-salvas');
        } catch (\Exception $e) {
            logger('Erro ao salvar diária:', [
                'erro' => $e->getMessage(),
                'dados' => $this->diariasSalvas ?? 'Sem dados'
            ]);
            $this->dispatch('erro-salvar-diarias', ['mensagem' => 'Erro ao salvar diárias: ' . $e->getMessage()]);
        } finally {
            // Remover marcação de processamento
            Session::forget('processando_diarias');
        }
    }

    public function limparFuncionario()
    {
        // Limpar todos os campos relacionados ao funcionário
        $this->reset([
            'nome', 
            'departamento', 
            'funcao', 
            'valor', 
            'horasExtras', 
            'qtdDiaria', 
            'empresa', 
            'funcionarioSelecionado', 
            'pesquisaFuncionario'
        ]);
        
        // Permitir que as sugestões sejam mostradas quando o usuário começar a digitar novamente
        $this->mostrarSugestoes = true;
        $this->sugestoesFuncionarios = collect([]);
    }

    // Método para depurar problemas
    public function debug($message, $data = [])
    {
        logger('CadastroDiarias Debug: ' . $message, $data);
    }

    public function render()
    {
        try {
            // Removendo a atualização automática no render para evitar múltiplas consultas ao banco
            // Isso será feito apenas pelo wire:model.live.debounce.300ms no campo de pesquisa
            return view('livewire.cadastro-diarias');
        } catch (\Exception $e) {
            logger('Erro na renderização do componente CadastroDiarias', [
                'erro' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro, tenta renderizar uma visão de fallback simples
            return view('livewire.cadastro-diarias-erro', [
                'erro' => $e->getMessage()
            ]);
        }
    }
}