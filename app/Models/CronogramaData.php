<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CronogramaData extends Model
{
    use HasFactory;

    protected $table = 'cronograma_datas';

    protected $fillable = [
        'cronograma_id',
        'data',
        'mes'
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'data' => 'date',
        'mes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Para garantir que as datas sejam convertidas para instâncias do Carbon
     */
    protected $dates = [
        'data',
        'created_at',
        'updated_at'
    ];
    
    /**
     * Mutator para o atributo data
     */
    public function setDataAttribute($value)
    {
        if (is_string($value) && strpos($value, '/') !== false) {
            // Converter de DD/MM/YYYY para YYYY-MM-DD
            $parts = explode('/', $value);
            if (count($parts) === 3) {
                $this->attributes['data'] = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            } else {
                $this->attributes['data'] = $value;
            }
        } else {
            $this->attributes['data'] = $value;
        }
    }

    /**
     * Relacionamento com o cronograma
     */
    public function cronograma()
    {
        try {
            return $this->belongsTo(Cronograma::class, 'cronograma_id');
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento cronograma: " . $e->getMessage());
            return null;
        }
    }
} 