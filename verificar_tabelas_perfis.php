<?php
// Script para verificar as tabelas de perfis e permissões
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "=== Verificando tabelas de perfis e permissões ===\n\n";

// Verificar se as tabelas existem
$tabelas = ['profiles', 'permissions', 'profile_permissions'];
foreach ($tabelas as $tabela) {
    echo "Tabela '{$tabela}': " . (Schema::hasTable($tabela) ? "EXISTE" : "NÃO EXISTE") . "\n";
}

echo "\n=== Estrutura das tabelas ===\n";

// Verificar estrutura da tabela profiles
if (Schema::hasTable('profiles')) {
    echo "\nColunas da tabela 'profiles':\n";
    $colunas = Schema::getColumnListing('profiles');
    foreach ($colunas as $coluna) {
        echo "- {$coluna}\n";
    }
    
    // Contar registros
    $count = DB::table('profiles')->count();
    echo "\nTotal de registros na tabela 'profiles': {$count}\n";
    
    // Listar registros
    if ($count > 0) {
        echo "\nRegistros na tabela 'profiles':\n";
        $perfis = DB::table('profiles')->get();
        foreach ($perfis as $perfil) {
            echo "- ID: {$perfil->id}, Nome: {$perfil->name}\n";
        }
    }
}

// Verificar estrutura da tabela permissions
if (Schema::hasTable('permissions')) {
    echo "\nColunas da tabela 'permissions':\n";
    $colunas = Schema::getColumnListing('permissions');
    foreach ($colunas as $coluna) {
        echo "- {$coluna}\n";
    }
    
    // Contar registros
    $count = DB::table('permissions')->count();
    echo "\nTotal de registros na tabela 'permissions': {$count}\n";
    
    // Listar registros
    if ($count > 0) {
        echo "\nRegistros na tabela 'permissions':\n";
        $permissoes = DB::table('permissions')->get();
        foreach ($permissoes as $permissao) {
            echo "- ID: {$permissao->id}, Nome: {$permissao->name}\n";
        }
    }
}

// Verificar estrutura da tabela profile_permissions
if (Schema::hasTable('profile_permissions')) {
    echo "\nColunas da tabela 'profile_permissions':\n";
    $colunas = Schema::getColumnListing('profile_permissions');
    foreach ($colunas as $coluna) {
        echo "- {$coluna}\n";
    }
    
    // Contar registros
    $count = DB::table('profile_permissions')->count();
    echo "\nTotal de registros na tabela 'profile_permissions': {$count}\n";
    
    // Listar registros
    if ($count > 0) {
        echo "\nRegistros na tabela 'profile_permissions':\n";
        $relacoes = DB::table('profile_permissions')->get();
        foreach ($relacoes as $relacao) {
            echo "- ID: {$relacao->id}, Profile ID: {$relacao->profile_id}, Permission ID: {$relacao->permission_id}\n";
        }
    }
}

// Verificar se os modelos estão carregando corretamente os relacionamentos
echo "\n=== Verificando relacionamentos nos modelos ===\n";

try {
    $perfil = \App\Models\Profile::first();
    if ($perfil) {
        echo "\nPerfil encontrado: {$perfil->name} (ID: {$perfil->id})\n";
        
        $permissoes = $perfil->permissions;
        echo "Permissões associadas: " . $permissoes->count() . "\n";
        
        if ($permissoes->count() > 0) {
            echo "Lista de permissões:\n";
            foreach ($permissoes as $permissao) {
                echo "- {$permissao->name} (ID: {$permissao->id})\n";
            }
        }
    } else {
        echo "\nNenhum perfil encontrado no banco de dados.\n";
    }
} catch (Exception $e) {
    echo "\nERRO ao carregar relacionamentos: " . $e->getMessage() . "\n";
}

echo "\n=== Verificação concluída ===\n"; 