<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RHProblema;
use Illuminate\Support\Facades\DB;

class CorrigirStatusRh extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rh:corrigir-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige os status com "Concluída" para "Concluído" na tabela rh_problemas';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Iniciando correção de status...');
        
        // Buscar registros com status "Concluída" (antigo)
        $registros = RHProblema::where('status', 'Concluída')->get();
        
        $count = $registros->count();
        $this->info("Encontrados {$count} registros com status 'Concluída'");
        
        if ($count > 0) {
            // Atualizar todos para "Concluído" (novo)
            foreach ($registros as $registro) {
                $this->line("Atualizando ID {$registro->id}");
                $registro->status = 'Concluído';
                $registro->save();
            }
            
            $this->info('Correção concluída com sucesso!');
        } else {
            $this->info('Nenhum registro precisava ser corrigido.');
        }
        
        // Verificar os status existentes
        $status = DB::table('rh_problemas')
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->get()
            ->pluck('status');
            
        $this->info('Status disponíveis no banco:');
        foreach ($status as $st) {
            $this->line(" - {$st}");
        }
        
        return 0;
    }
} 