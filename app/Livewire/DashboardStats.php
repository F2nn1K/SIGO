<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RHProblema;

class DashboardStats extends Component
{
    public $tarefasPendentes;
    public $tarefasEmAndamento;
    public $tarefasConcluidas;
    public $taxaConclusao;
    
    // Ativar polling a cada 60 segundos
    public function getPollingIntervalProperty()
    {
        return 60000; // 60 segundos em milissegundos
    }

    public function mount()
    {
        $this->carregarEstatisticas();
    }

    public function carregarEstatisticas()
    {
        $this->tarefasPendentes = RHProblema::where('status', 'Pendente')->count();
        $this->tarefasEmAndamento = RHProblema::where('status', 'Em andamento')->count();
        $this->tarefasConcluidas = RHProblema::where('status', 'Concluída')->count();
        
        $total = RHProblema::count();
        $this->taxaConclusao = $total > 0 ? round(($this->tarefasConcluidas / $total) * 100) : 0;
    }
    
    // Método para atualização polled
    public function poll()
    {
        $this->carregarEstatisticas();
    }

    public function render()
    {
        return view('livewire.dashboard-stats');
    }
} 