<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentoDPItem extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     */
    protected $table = 'documentos_dp_itens';

    /**
     * Os atributos que são atribuíveis em massa (SEGURANÇA).
     */
    protected $fillable = [
        'documento_dp_id',
        'tipo_documento',
        'selecionado',
        'arquivo_nome',
        'arquivo_path',
        'arquivo_extensao',
        'arquivo_tamanho',
        'arquivo_hash'
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'selecionado' => 'boolean',
        'arquivo_tamanho' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com o documento principal (PROTEGIDO).
     */
    public function documentoDP()
    {
        try {
            return $this->belongsTo(DocumentoDP::class, 'documento_dp_id');
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento documentoDP em DocumentoDPItem: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se o arquivo existe no storage (SEGURANÇA).
     */
    public function arquivoExiste()
    {
        try {
            if (!$this->arquivo_path) {
                return false;
            }
            return Storage::disk('local')->exists($this->arquivo_path);
        } catch (\Exception $e) {
            Log::error("Erro ao verificar existência do arquivo: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Obtém a URL segura do arquivo (SEGURANÇA).
     */
    public function getArquivoUrlAttribute()
    {
        try {
            if (!$this->arquivo_path || !$this->arquivoExiste()) {
                return null;
            }
            // Retorna URL protegida - apenas usuários autenticados podem acessar
            return route('documentos-dp.download', ['item' => $this->id]);
        } catch (\Exception $e) {
            Log::error("Erro ao gerar URL do arquivo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Formata o tamanho do arquivo (UTILITÁRIO).
     */
    public function getTamanhoFormatadoAttribute()
    {
        if (!$this->arquivo_tamanho) {
            return null;
        }
        
        $bytes = $this->arquivo_tamanho;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Boot do modelo para auditoria e limpeza.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($item) {
            try {
                // Remove o arquivo físico quando o item é deletado (SEGURANÇA)
                if ($item->arquivo_path && Storage::disk('local')->exists($item->arquivo_path)) {
                    Storage::disk('local')->delete($item->arquivo_path);
                    Log::info("Arquivo removido do storage", [
                        'item_id' => $item->id,
                        'arquivo_path' => $item->arquivo_path
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Erro ao remover arquivo do storage: " . $e->getMessage());
            }
        });

        static::created(function ($item) {
            try {
                Log::info("DocumentoDPItem criado", [
                    'id' => $item->id,
                    'documento_dp_id' => $item->documento_dp_id,
                    'tipo_documento' => $item->tipo_documento,
                    'selecionado' => $item->selecionado
                ]);
            } catch (\Exception $e) {
                Log::warning('Erro ao logar criação de DocumentoDPItem: ' . $e->getMessage());
            }
        });
    }
}
