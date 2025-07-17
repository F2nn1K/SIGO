<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioRH1002 extends Component
{
    public $data_inicial;
    public $data_final;
    public $diarias = [];
    public $mensagem = '';
    public $tipo_mensagem = '';
    public $totalDiarias = 0;
    public $resumoPorDepartamento = [];

    public function mount()
    {
        $this->data_inicial = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->data_final = Carbon::now()->format('Y-m-d');
    }

    public function buscarDiarias()
    {
        $this->validate([
            'data_inicial' => 'required|date',
            'data_final' => 'required|date|after_or_equal:data_inicial'
        ], [
            'data_inicial.required' => 'A data inicial é obrigatória',
            'data_final.required' => 'A data final é obrigatória',
            'data_final.after_or_equal' => 'A data final deve ser maior ou igual à data inicial'
        ]);

        try {
            $dataInicialFormatada = Carbon::parse($this->data_inicial)->startOfDay();
            $dataFinalFormatada = Carbon::parse($this->data_final)->endOfDay();

            $this->diarias = DB::table('diarias')
                ->select('nome', 'departamento', 'funcao', 'diaria', 'referencia', 'observacao', 'data_inclusao')
                ->whereBetween('data_inclusao', [$dataInicialFormatada, $dataFinalFormatada])
                ->orderBy('data_inclusao', 'desc')
                ->get()
                ->toArray();

            if (empty($this->diarias)) {
                $this->mensagem = 'Nenhum registro encontrado para o período selecionado.';
                $this->tipo_mensagem = 'info';
            } else {
                $this->mensagem = '';
                $this->calcularTotais();
            }
        } catch (\Exception $e) {
            $this->mensagem = 'Erro ao buscar dados: ' . $e->getMessage();
            $this->tipo_mensagem = 'error';
        }
    }

    private function calcularTotais()
    {
        $this->totalDiarias = collect($this->diarias)->sum('diaria');
        
        $this->resumoPorDepartamento = collect($this->diarias)
            ->groupBy('departamento')
            ->map(function ($grupo) {
                return [
                    'quantidade' => count($grupo),
                    'total' => collect($grupo)->sum('diaria')
                ];
            })
            ->toArray();
    }

    public function imprimir()
    {
        $this->dispatch('imprimir-relatorio');
    }

    public function render()
    {
        return view('livewire.relatorio-r-h');
    }
} 