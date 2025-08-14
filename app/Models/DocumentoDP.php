<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DocumentoDP extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     */
    protected $table = 'documentos_dp';

    /**
     * Os atributos que são atribuíveis em massa (SEGURANÇA).
     */
    protected $fillable = [
        'nome_funcionario',
        'funcao',
        'cpf',
        'sexo',
        'usuario_id',
        'status',
        'observacoes'
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relacionamento com o usuário que cadastrou (PROTEGIDO).
     */
    public function usuario()
    {
        try {
            return $this->belongsTo(User::class, 'usuario_id');
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento usuario em DocumentoDP: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Relacionamento com os itens de documentos (PROTEGIDO).
     */
    public function itens()
    {
        try {
            return $this->hasMany(DocumentoDPItem::class, 'documento_dp_id');
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento itens em DocumentoDP: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Relacionamento com os itens selecionados apenas.
     */
    public function itensSelecionados()
    {
        try {
            return $this->hasMany(DocumentoDPItem::class, 'documento_dp_id')
                        ->where('selecionado', true);
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento itensSelecionados em DocumentoDP: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Boot do modelo para limpeza de cache (AUDITORIA).
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($documentoDP) {
            try {
                Log::info("DocumentoDP criado", [
                    'id' => $documentoDP->id,
                    'nome_funcionario' => $documentoDP->nome_funcionario,
                    'usuario_id' => $documentoDP->usuario_id
                ]);
            } catch (\Exception $e) {
                Log::warning('Erro ao logar criação de DocumentoDP: ' . $e->getMessage());
            }
        });

        static::updated(function ($documentoDP) {
            try {
                Log::info("DocumentoDP atualizado", [
                    'id' => $documentoDP->id,
                    'status' => $documentoDP->status,
                    'usuario_id' => $documentoDP->usuario_id
                ]);
            } catch (\Exception $e) {
                Log::warning('Erro ao logar atualização de DocumentoDP: ' . $e->getMessage());
            }
        });
    }
}
