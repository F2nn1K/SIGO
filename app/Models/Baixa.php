<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baixa extends Model
{
    use HasFactory;
    
    protected $table = 'baixas';
    
    protected $fillable = [
        'funcionario_id',
        'centro_custo_id',
        'produto_id',
        'quantidade',
        'observacoes',
        'data_baixa',
        'usuario_id'
    ];
    
    protected $casts = [
        'data_baixa' => 'datetime',
    ];
    
    // Relacionamentos
    public function funcionario()
    {
        return $this->belongsTo(Funcionario::class);
    }
    
    public function produto()
    {
        return $this->belongsTo(Estoque::class, 'produto_id', 'id');
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class);
    }
    
    public function centroCusto()
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id');
    }
}
