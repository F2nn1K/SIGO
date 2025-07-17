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
        'departamento',
        'funcao',
        'valor',
        'observacao',
        'empresa',
    ];
    
    // Relacionamento com as diárias
    public function diarias()
    {
        return $this->hasMany(Diaria::class, 'nome', 'nome');
    }
}