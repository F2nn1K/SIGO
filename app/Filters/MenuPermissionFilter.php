<?php

namespace App\Filters;

use Illuminate\Contracts\Auth\Access\Gate;
use JeroenNoten\LaravelAdminLte\Menu\Filters\FilterInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MenuPermissionFilter implements FilterInterface
{
    protected $gate;

    public function __construct(Gate $gate)
    {
        $this->gate = $gate;
    }

    public function transform($item)
    {
        // Se o item tiver a chave permission definida, verifica se o usuário tem essa permissão
        if (isset($item['permission']) && !$this->checkPermission($item['permission'])) {
            // Se o usuário não tem a permissão, esconde o item
            return false;
        }
        
        // Se o item tiver a chave permission_any_of definida, verifica se o usuário tem pelo menos uma das permissões
        if (isset($item['permission_any_of']) && !$this->checkAnyPermission($item['permission_any_of'])) {
            // Se o usuário não tem nenhuma das permissões, esconde o item
            return false;
        }
        
        // Suporte ao parâmetro can_any, processando-o como permission_any_of
        if (isset($item['can_any']) && !$this->checkAnyPermission($item['can_any'])) {
            // Se o usuário não tem nenhuma das permissões, esconde o item
            return false;
        }
        
        // Suporte ao parâmetro can (compatibilidade com AdminLTE padrão)
        if (isset($item['can']) && !$this->checkPermission($item['can'])) {
            // Se o usuário não tem a permissão, esconde o item
            return false;
        }

        // Se o item tiver submenu, aplica o filtro em cada item do submenu
        if (isset($item['submenu'])) {
            $filteredSubmenu = array_filter($item['submenu'], function ($subItem) {
                return $this->transform($subItem) !== false;
            });

            // Se todos os itens do submenu foram filtrados (submenu vazio), esconde o item pai
            if (empty($filteredSubmenu)) {
                return false;
            }

            // Atualiza o submenu com apenas os itens permitidos
            $item['submenu'] = array_values($filteredSubmenu);
        }

        return $item;
    }

    /**
     * Verifica se o usuário atual tem a permissão especificada
     *
     * @param string $permission
     * @return bool
     */
    protected function checkPermission($permission)
    {
        // Verifica se o usuário está autenticado
        if (!Auth::check()) {
            \Log::debug('MenuPermissionFilter: Usuário não autenticado');
            return false;
        }

        $user = Auth::user();
        if (config('app.debug')) {
            \Log::debug('MenuPermissionFilter: Verificando permissão ' . $permission . ' para o usuário ' . $user->id);
        }
        
        // Se o usuário tem perfil 'Admin', concede acesso a tudo
        if ($user->profile && $user->profile->name === 'Admin') {
            if (config('app.debug')) {
                \Log::debug('MenuPermissionFilter: Usuário é Admin, concedendo acesso para: ' . $permission);
            }
            return true;
        }
        
        // Verifica permissão pelo profile_id principal do usuário
        if ($user->profile_id) {
            $temPermissao = DB::table('profile_permissions')
                ->join('permissions', 'permissions.id', '=', 'profile_permissions.permission_id')
                ->where('profile_permissions.profile_id', $user->profile_id)
                ->where(function($query) use ($permission) {
                    $query->where('permissions.name', $permission)
                          ->orWhere('permissions.code', $permission);
                })
                ->exists();
                
            if (config('app.debug')) {
                \Log::debug('MenuPermissionFilter: Resultado da verificação para ' . $permission . ': ' . ($temPermissao ? 'true' : 'false'));
            }
            
            if ($temPermissao) {
                return true;
            }
        } else {
            if (config('app.debug')) {
                \Log::debug('MenuPermissionFilter: Usuário sem perfil definido');
            }
        }
        
        return false;
    }

    /**
     * Verifica se o usuário atual tem pelo menos uma das permissões especificadas
     *
     * @param array $permissions
     * @return bool
     */
    protected function checkAnyPermission($permissions)
    {
        foreach ($permissions as $permission) {
            if ($this->checkPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }
} 