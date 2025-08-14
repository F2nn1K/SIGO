<?php
// app/Models/Funcionario.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;

    protected $table = 'funcionarios';
    
    protected $fillable = [
        'nome',
        'cpf',
        'sexo',
        'funcao',
        'status',
        'observacoes',
        'departamento',
        'valor',
        'empresa',
    ];
    
    // Relacionamento com as diárias
    public function diarias()
    {
        return $this->hasMany(Diaria::class, 'nome', 'nome');
    }
    
    // Relacionamentos com módulo de documentos DP
    public function documentos()
    {
        return $this->hasMany(FuncionarioDocumento::class, 'funcionario_id');
    }
    
    public function atestados()
    {
        return $this->hasMany(FuncionarioAtestado::class, 'funcionario_id');
    }
    
    public function advertencias()
    {
        return $this->hasMany(FuncionarioAdvertencia::class, 'funcionario_id');
    }
    
    public function logs()
    {
        return $this->hasMany(FuncionarioLog::class, 'funcionario_id');
    }
}