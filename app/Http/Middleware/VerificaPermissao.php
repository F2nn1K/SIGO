<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Access\AuthorizationException;

class VerificaPermissao
{
    public function handle($request, Closure $next, $permissao)
    {
        if (!Auth::check() || !Auth::user()->temPermissao($permissao)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Você não tem permissão para realizar esta ação'
                ], 403);
            }
            
            throw new AuthorizationException('Você não tem permissão para realizar esta ação.');
        }
        
        return $next($request);
    }
} 