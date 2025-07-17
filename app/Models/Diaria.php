<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diaria extends Model
{
    use HasFactory;
    
    protected $table = 'diarias';
    
    protected $fillable = [
        'nome',
        'departamento',
        'funcao', 
        'diaria',
        'referencia',
        'observacao',
        'gerente',
        'chave',
        'data_inclusao',
        'visualizado',
        'empresa'
    ];
    
    public $timestamps = false;
    
    // Se a data_inclusao deve ser tratada como um objeto Carbon
    protected $dates = [
        'data_inclusao',
    ];

    protected $casts = [
        'data_inclusao' => 'datetime',
        'visualizado' => 'datetime'
    ];
}