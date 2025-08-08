<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use App\Livewire\GerenciarPermissoes;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(
            \App\Filters\MenuPermissionFilter::class,
            function ($app) {
                return new \App\Filters\MenuPermissionFilter(
                    $app->make(\Illuminate\Contracts\Auth\Access\Gate::class)
                );
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            // Verificação de segurança para o menu
            // Isso garante que não haverá erros se alguma tabela não existir
            if (!Schema::hasTable('profile_permissions')) {
                Log::warning('Tabela profile_permissions não encontrada, verificações de permissões podem não funcionar corretamente.');
            }
            
            if (!Schema::hasTable('user_profiles') && !Schema::hasTable('users') && !Schema::hasColumn('users', 'profile_id')) {
                Log::warning('Relacionamento entre usuários e perfis pode não estar configurado corretamente.');
            }
        } catch (\Exception $e) {
            Log::error('Erro ao verificar tabelas no boot do AppServiceProvider: ' . $e->getMessage());
        }

        Livewire::component('gerenciar-permissoes', GerenciarPermissoes::class);
        // Removido cadastro-diarias do boot para ocultar módulo de Diárias/RH
    }
}
