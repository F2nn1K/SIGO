<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CronogramaEvento extends Model
{
    use HasFactory;

    protected $table = 'cronograma_eventos';
    
    protected $fillable = [
        'titulo',
        'descricao',
        'data_inicio',
        'data_fim',
        'status',
        'criado_por',
        'responsavel_id'
    ];

    protected $casts = [
        'data_inicio' => 'datetime',
        'data_fim' => 'datetime'
    ];

    public function criador()
    {
        return $this->belongsTo(User::class, 'criado_por');
    }

    public function responsavel()
    {
        return $this->belongsTo(User::class, 'responsavel_id');
    }
} 