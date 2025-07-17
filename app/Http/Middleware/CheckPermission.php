<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        Log::debug('Middleware CheckPermission: Verificando permissão ' . $permission . ' para o usuário ' . $user->id);
        
        if ($user->profile_id) {
            $temPermissao = DB::table('profile_permissions')
                ->join('permissions', 'permissions.id', '=', 'profile_permissions.permission_id')
                ->where('profile_permissions.profile_id', $user->profile_id)
                ->where(function($query) use ($permission) {
                    $query->where('permissions.name', $permission)
                          ->orWhere('permissions.code', $permission);
                })
                ->exists();
                
            Log::debug('Middleware CheckPermission: Resultado da verificação: ' . ($temPermissao ? 'true' : 'false'));
            
            if ($temPermissao) {
                return $next($request);
            }
        } else {
            Log::debug('Middleware CheckPermission: Usuário sem perfil definido');
        }
        
        abort(403, 'Acesso não autorizado');
    }
} 