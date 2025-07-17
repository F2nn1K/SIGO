<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RHPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar grupo de permissões
        $groups = [
            ['name' => 'Administração', 'description' => 'Permissões administrativas'],
            ['name' => 'RH', 'description' => 'Permissões do módulo de RH'],
            ['name' => 'Diárias', 'description' => 'Permissões do módulo de Diárias'],
            ['name' => 'Relatórios', 'description' => 'Permissões de relatórios'],
        ];

        foreach ($groups as $group) {
            DB::table('permission_groups')->updateOrInsert(
                ['name' => $group['name']],
                $group
            );
        }

        // Obter IDs dos grupos
        $adminGroupId = DB::table('permission_groups')->where('name', 'Administração')->value('id');
        $rhGroupId = DB::table('permission_groups')->where('name', 'RH')->value('id');
        $diariasGroupId = DB::table('permission_groups')->where('name', 'Diárias')->value('id');
        $relatoriosGroupId = DB::table('permission_groups')->where('name', 'Relatórios')->value('id');

        // Definição das permissões
        $permissions = [
            // Permissões administrativas
            [
                'name' => 'Administrador',
                'code' => 'admin',
                'description' => 'Acesso total ao sistema',
                'group_id' => $adminGroupId
            ],
            [
                'name' => 'Gerenciar Usuários',
                'code' => 'gerenciar-usuarios',
                'description' => 'Permite gerenciar usuários do sistema',
                'group_id' => $adminGroupId
            ],
            [
                'name' => 'Gerenciar Permissões',
                'code' => 'gerenciar-permissoes',
                'description' => 'Permite gerenciar permissões do sistema',
                'group_id' => $adminGroupId
            ],
            
            // Permissões de RH
            [
                'name' => 'RH',
                'code' => 'rh',
                'description' => 'Acesso ao módulo de RH',
                'group_id' => $rhGroupId
            ],
            [
                'name' => 'Ver Tarefas RH',
                'code' => 'ver-tarefas-rh',
                'description' => 'Permite visualizar tarefas do RH',
                'group_id' => $rhGroupId
            ],
            [
                'name' => 'Cronograma',
                'code' => 'cronograma',
                'description' => 'Acesso ao cronograma',
                'group_id' => $rhGroupId
            ],
            
            // Permissões de Diárias
            [
                'name' => 'Ver Diárias',
                'code' => 'ver-diarias',
                'description' => 'Permite visualizar diárias',
                'group_id' => $diariasGroupId
            ],
            [
                'name' => 'Cadastrar Diárias',
                'code' => 'cadastrar-diarias',
                'description' => 'Permite cadastrar diárias',
                'group_id' => $diariasGroupId
            ],
            
            // Permissões de Relatórios
            [
                'name' => 'Ver Relatório 1000',
                'code' => 'ver-relatorio-1000',
                'description' => 'Permite visualizar o relatório 1000',
                'group_id' => $relatoriosGroupId
            ],
            [
                'name' => 'Ver Relatório 1001',
                'code' => 'ver-relatorio-1001',
                'description' => 'Permite visualizar o relatório 1001',
                'group_id' => $relatoriosGroupId
            ],
            [
                'name' => 'Ver Relatório 1002',
                'code' => 'ver-relatorio-1002',
                'description' => 'Permite visualizar o relatório 1002',
                'group_id' => $relatoriosGroupId
            ],
        ];

        // Inserir cada permissão na tabela
        foreach ($permissions as $permission) {
            DB::table('permissions')->updateOrInsert(
                ['code' => $permission['code']],
                $permission
            );
        }
    }
} 