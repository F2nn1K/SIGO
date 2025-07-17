<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Registrar erros específicos de formatação de data
            if (str_contains($e->getMessage(), 'Could not parse') || 
                str_contains($e->getMessage(), 'Failed to parse time')) {
                \Log::error('Erro de formatação de data: ' . $e->getMessage());
                \Log::error('Stack trace: ' . $e->getTraceAsString());
                
                // Registrar a consulta SQL que estava sendo executada, se disponível
                if (\DB::getQueryLog()) {
                    \Log::error('Últimas consultas SQL: ' . json_encode(array_slice(\DB::getQueryLog(), -3)));
                }
            }
        });
    }
}
