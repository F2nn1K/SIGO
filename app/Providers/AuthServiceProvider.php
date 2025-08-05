<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Registra todas as permissões como Gates
        try {
            $permissions = Permission::all();
            
            foreach ($permissions as $permission) {
                // Registra o Gate usando o name da permissão
                Gate::define($permission->name, function ($user) use ($permission) {
                    return $user->temPermissao($permission->name);
                });
                
                // Se o code for diferente do name, registra também o code
                if ($permission->code && $permission->code !== $permission->name) {
                    Gate::define($permission->code, function ($user) use ($permission) {
                        return $user->temPermissao($permission->code);
                    });
                }
            }
        } catch (\Exception $e) {
            // Em caso de erro (como tabela não existir durante migrations), não faz nada
        }
    }
}
