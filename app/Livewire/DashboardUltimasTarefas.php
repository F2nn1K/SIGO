<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RHProblema;

class DashboardUltimasTarefas extends Component
{
    public $ultimasTarefas = [];
    
    // Ativar polling a cada 60 segundos
    public function getPollingIntervalProperty()
    {
        return 60000; // 60 segundos em milissegundos
    }

    public function mount()
    {
        $this->carregarUltimasTarefas();
    }

    public function carregarUltimasTarefas()
    {
        $this->ultimasTarefas = RHProblema::orderBy('created_at', 'desc')
            ->take(5)
            ->get();
    }
    
    // Método para atualização polled
    public function poll()
    {
        $this->carregarUltimasTarefas();
    }

    public function render()
    {
        return view('livewire.dashboard-ultimas-tarefas');
    }
} 