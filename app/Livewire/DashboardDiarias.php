<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class DashboardDiarias extends Component
{
    public $departamentos = [];
    public $labels = [];
    public $totais = [];
    public $valores = [];
    public $cores = [];
    
    // Ativar polling a cada 30 segundos
    public function getPollingIntervalProperty()
    {
        return 30000; // 30 segundos em milissegundos
    }

    public function mount()
    {
        $this->carregarDadosDiarias();
    }

    public function carregarDadosDiarias()
    {
        try {
            $departamentosQuery = DB::table('diarias')
                ->select('departamento', DB::raw('COUNT(*) as total'), DB::raw('SUM(diaria) as valor_total'))
                ->whereNotNull('departamento')
                ->where('departamento', '<>', '')
                ->groupBy('departamento')
                ->orderBy('total', 'desc')
                ->take(5)
                ->get();
            
            $this->departamentos = $departamentosQuery->toArray();
            
            // Limpar os arrays antes de adicioná-los
            $this->labels = [];
            $this->totais = [];
            $this->valores = [];
            $this->cores = [];
            
            foreach ($departamentosQuery as $dep) {
                $this->labels[] = $dep->departamento ?? 'Não informado';
                $this->totais[] = $dep->total;
                $this->valores[] = $dep->valor_total;
                
                // Gerar cor aleatória para o gráfico
                $this->cores[] = 'rgb(' . rand(0, 200) . ',' . rand(50, 200) . ',' . rand(50, 200) . ')';
            }
            
            // Emitir evento para atualizar o gráfico
            $this->dispatch('diariasDadosAtualizados', [
                'labels' => $this->labels,
                'totais' => $this->totais,
                'cores' => $this->cores
            ]);
        } catch (\Exception $e) {
            // Em caso de erro, definir valores vazios
            $this->departamentos = [];
            $this->labels = [];
            $this->totais = [];
            $this->valores = [];
            $this->cores = [];
        }
    }
    
    // Método para atualização polled
    public function poll()
    {
        $this->carregarDadosDiarias();
    }

    public function render()
    {
        // Garantir que $departamentos seja sempre definido como um array
        if (empty($this->departamentos)) {
            $this->departamentos = [];
        }
        
        return view('livewire.dashboard-diarias');
    }
} 