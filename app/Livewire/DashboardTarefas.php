<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\RHProblema;

class DashboardTarefas extends Component
{
    public $usuarios = [];
    
    // Ativar polling a cada 60 segundos
    public function getPollingIntervalProperty()
    {
        return 60000; // 60 segundos em milissegundos
    }

    public function mount()
    {
        $this->carregarTarefasUsuarios();
    }

    public function carregarTarefasUsuarios()
    {
        $usuariosComTarefas = DB::table('users')
            ->leftJoin('rh_problemas', 'users.id', '=', 'rh_problemas.respondido_por')
            ->select('users.id', 'users.name')
            ->whereNotNull('rh_problemas.id')
            ->groupBy('users.id', 'users.name')
            ->take(5)
            ->get();
        
        // Limpar array antes de adicionar novos dados
        $this->usuarios = [];
        
        foreach ($usuariosComTarefas as $usuario) {
            $emAndamento = RHProblema::where('respondido_por', $usuario->id)
                ->where('status', 'Em andamento')
                ->count();
            
            $concluidas = RHProblema::where('respondido_por', $usuario->id)
                ->where('status', 'Concluído')
                ->count();
            
            $total = RHProblema::where('respondido_por', $usuario->id)->count();
            
            $this->usuarios[] = [
                'id' => $usuario->id,
                'nome' => $usuario->name,
                'emAndamento' => $emAndamento,
                'concluidas' => $concluidas,
                'total' => $total
            ];
        }
    }
    
    // Método para atualização polled
    public function poll()
    {
        $this->carregarTarefasUsuarios();
    }

    public function render()
    {
        return view('livewire.dashboard-tarefas');
    }
} 