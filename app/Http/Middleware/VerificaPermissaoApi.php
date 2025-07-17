<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class VerificaPermissaoApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permissao
     * @return mixed
     */
    public function handle($request, Closure $next, $permissao)
    {
        try {
            if (!Auth::check() || !Auth::user()->temPermissao($permissao)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para realizar esta ação',
                    'error_code' => 'permission_denied'
                ], 403);
            }
            
            return $next($request);
        } catch (\Exception $e) {
            \Log::error('Erro no middleware VerificaPermissaoApi', [
                'error' => $e->getMessage(),
                'permissao' => $permissao,
                'user_id' => Auth::id(),
                'route' => $request->path()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao verificar permissões',
                'error_code' => 'auth_error'
            ], 500);
        }
    }
} 