<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RHAnotacao extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rh_anotacoes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'problema_id',
        'usuario_id',
        'conteudo',
    ];

    /**
     * Get the problema that owns the anotação.
     */
    public function problema()
    {
        return $this->belongsTo(RHProblema::class, 'problema_id');
    }

    /**
     * Get the user that created the anotação.
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
} 