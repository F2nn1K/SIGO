<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        // Permitir envio do formulário de inclusão sem verificar CSRF
        // Mantém as demais rotas protegidas
        'documentos-dp/inclusao'
    ];
}
