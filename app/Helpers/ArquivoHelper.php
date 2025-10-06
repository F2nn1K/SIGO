<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ArquivoHelper
{
    /**
     * Download universal que suporta BLOB e Storage
     * 
     * @param object $registro Objeto com campos arquivo_path, arquivo_conteudo, etc
     * @param string $tipo Tipo de arquivo (para log)
     * @return \Illuminate\Http\Response
     */
    public static function download($registro, $tipo = 'documento')
    {
        try {
            // PRIORIDADE 1: Tentar storage (rápido)
            if (!empty($registro->arquivo_path)) {
                $fullPath = storage_path('app/public/' . $registro->arquivo_path);
                
                if (file_exists($fullPath)) {
                    $nome = $registro->arquivo_nome ?? 'arquivo.pdf';
                    $mime = $registro->arquivo_mime 
                        ?? ($registro->arquivo_mime_type 
                        ?? (function_exists('mime_content_type') ? (mime_content_type($fullPath) ?: 'application/pdf') : 'application/pdf'));

                    return response()->file($fullPath, [
                        'Content-Type' => $mime,
                        'Content-Disposition' => 'inline; filename="' . $nome . '"',
                        'Cache-Control' => 'public, max-age=31536000, immutable',
                    ]);
                }
            }

            // PRIORIDADE 2: Fallback para BLOB (lento mas funciona)
            if (!empty($registro->arquivo_conteudo)) {
                $mime = $registro->arquivo_mime ?? ($registro->arquivo_mime_type ?? 'application/pdf');
                $nome = $registro->arquivo_nome ?? 'arquivo.pdf';
                
                return response($registro->arquivo_conteudo)
                    ->header('Content-Type', $mime)
                    ->header('Content-Disposition', 'inline; filename="' . $nome . '"')
                    ->header('Cache-Control', 'public, max-age=31536000, immutable');
            }

            // Arquivo não encontrado
            abort(404, ucfirst($tipo) . ' não encontrado');
            
        } catch (\Exception $e) {
            Log::error("Erro ao baixar {$tipo}", [
                'id' => $registro->id ?? null,
                'erro' => $e->getMessage()
            ]);
            abort(500, 'Erro ao carregar arquivo');
        }
    }

    /**
     * Salva arquivo em storage + BLOB (temporário)
     * 
     * @param \Illuminate\Http\UploadedFile $file
     * @param int $funcionarioId
     * @param string $subpasta Ex: 'documentos', 'atestados', 'ferias'
     * @return array ['conteudo' => binary, 'path' => string]
     */
    public static function salvar($file, $funcionarioId, $subpasta = 'documentos')
    {
        $conteudo = file_get_contents($file->getRealPath());
        
        // Gerar nome único
        $nomeArquivo = time() . '_' . uniqid() . '_' . $file->getClientOriginalName();
        $caminhoRelativo = "{$subpasta}/funcionarios/{$funcionarioId}/{$nomeArquivo}";
        $caminhoCompleto = storage_path('app/public/' . $caminhoRelativo);
        
        // Criar diretório se não existir
        $diretorio = dirname($caminhoCompleto);
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0755, true);
        }
        
        // Salvar arquivo
        file_put_contents($caminhoCompleto, $conteudo);
        
        // Retornar ambos
        return [
            'conteudo' => $conteudo, // para BLOB (temporário)
            'path' => $caminhoRelativo, // para storage (permanente)
            'nome' => $file->getClientOriginalName(),
            'extensao' => $file->getClientOriginalExtension(),
            'mime' => $file->getMimeType(),
            'tamanho' => $file->getSize(),
        ];
    }
}

