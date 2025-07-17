<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RHProblema extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'rh_problemas';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'descricao',
        'status',
        'prioridade',
        'horario',
        'inicio_contagem',
        'usuario_id',
        'usuario_nome',
        'detalhes',
        'resposta',
        'respondido_por',
        'data_resposta',
        'responsavel_id',
        'finalizado_em',
        'prazo_entrega'
    ];

    /**
     * Os atributos que devem ser convertidos para tipos nativos.
     *
     * @var array
     */
    protected $casts = [
        'horario' => 'datetime',
        'inicio_contagem' => 'datetime',
        'data_resposta' => 'datetime',
        'finalizado_em' => 'datetime',
        'prazo_entrega' => 'datetime',
        'detalhes' => 'json'
    ];

    /**
     * Obtém o usuário que criou o problema.
     */
    public function usuario()
    {
        return $this->belongsTo(\App\Models\User::class, 'usuario_id');
    }

    /**
     * Obtém o usuário que respondeu ao problema.
     */
    public function respondente()
    {
        return $this->belongsTo(User::class, 'respondido_por');
    }

    /**
     * Obtém o usuário responsável pelo problema.
     */
    public function responsavel()
    {
        return $this->belongsTo(\App\Models\User::class, 'responsavel_id');
    }

    /**
     * Obtém as anotações relacionadas ao problema.
     */
    public function anotacoes()
    {
        return $this->hasMany(RHAnotacao::class, 'problema_id');
    }
} 