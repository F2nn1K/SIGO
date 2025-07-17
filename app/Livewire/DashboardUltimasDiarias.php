<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Diaria;

class DashboardUltimasDiarias extends Component
{
    public $ultimasDiarias = [];
    
    // Ativar polling a cada 60 segundos
    public function getPollingIntervalProperty()
    {
        return 60000; // 60 segundos em milissegundos
    }

    public function mount()
    {
        $this->carregarUltimasDiarias();
    }

    public function carregarUltimasDiarias()
    {
        $this->ultimasDiarias = Diaria::orderBy('data_inclusao', 'desc')
            ->take(5)
            ->get();
    }
    
    // Método para atualização polled
    public function poll()
    {
        $this->carregarUltimasDiarias();
    }

    public function render()
    {
        return view('livewire.dashboard-ultimas-diarias');
    }
} 