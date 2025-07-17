<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateRHPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:rh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria as permissões independentes para o módulo RH';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissions = [
            [
                'name' => 'Ver Administrador RH',
                'slug' => 'ver-administrador-rh',
                'description' => 'Permite visualizar a página de administrador do RH',
            ],
            [
                'name' => 'Ver Tarefas RH',
                'slug' => 'ver-tarefas-rh',
                'description' => 'Permite visualizar a página de tarefas do RH',
            ],
            [
                'name' => 'Ver Tarefas Usuários RH',
                'slug' => 'ver-tarefas-usuarios-rh',
                'description' => 'Permite visualizar a página de tarefas por usuários do RH',
            ],
        ];

        $count = 0;
        foreach ($permissions as $permission) {
            $exists = DB::table('permissions')->where('slug', $permission['slug'])->exists();
            
            if (!$exists) {
                DB::table('permissions')->insert($permission);
                $this->info("Permissão '{$permission['name']}' criada com sucesso.");
                $count++;
            } else {
                $this->info("Permissão '{$permission['name']}' já existe.");
            }
        }

        $this->info("Total de {$count} permissões criadas.");
        
        return Command::SUCCESS;
    }
} 