<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class Cronograma extends Model
{
    use HasFactory;

    /**
     * A tabela associada ao modelo.
     *
     * @var string
     */
    protected $table = 'cronogramas';

    /**
     * Os atributos que são atribuíveis em massa.
     *
     * @var array
     */
    protected $fillable = [
        'descricao',
        'status',
        'usuario_id',
    ];

    /**
     * Atributos que devem ser convertidos para tipos nativos
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Sempre carregar esses relacionamentos
     */
    protected $with = ['usuarios', 'datas'];

    /**
     * Relacionamento com o usuário responsável
     */
    public function usuario()
    {
        try {
            return $this->belongsTo(User::class, 'usuario_id');
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento usuario: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Relacionamento com as datas associadas
     */
    public function datas()
    {
        try {
            return $this->hasMany(CronogramaData::class, 'cronograma_id');
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento datas: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Relacionamento com os usuários responsáveis
     */
    public function usuarios()
    {
        try {
            return $this->belongsToMany(User::class, 'cronograma_usuarios', 'cronograma_id', 'usuario_id')
                ->withTimestamps();
        } catch (\Exception $e) {
            Log::error("Erro no relacionamento usuarios: " . $e->getMessage());
            return collect();
        }
    }

    /**
     * Método para sincronizar com problemas do RH
     */
    public static function sincronizarComProblemas()
    {
        try {
            // Verificar se as tabelas existem
            if (!Schema::hasTable('cronogramas') || 
                !Schema::hasTable('cronograma_usuarios') || 
                !Schema::hasTable('cronograma_datas')) {
                Log::error("Uma ou mais tabelas necessárias não existem");
                return 0;
            }
            
            // Limpar todas as tarefas existentes
            try {
                self::truncate();
                DB::table('cronograma_usuarios')->truncate();
                DB::table('cronograma_datas')->truncate();
                
                Log::info("Tabelas de cronograma limpas para nova sincronização");
            } catch (\Exception $e) {
                Log::error("Erro ao limpar tabelas: " . $e->getMessage());
                // Continuar mesmo com erro
            }
            
            // Usar consulta direta para garantir que busque os dados corretos
            try {
                // Consulta direta para garantir a obtenção de todos os problemas
                $problemas = DB::select("SELECT * FROM rh_problemas");
                Log::info("Consulta SQL recuperou " . count($problemas) . " problemas");
                
                if (!$problemas || count($problemas) == 0) {
                    Log::warning("Nenhum problema encontrado na tabela rh_problemas. Verificando a tabela...");
                    
                    // Verificar quantos registros existem na tabela
                    $totalRegistros = DB::selectOne("SELECT COUNT(*) as total FROM rh_problemas");
                    
                    if ($totalRegistros && $totalRegistros->total > 0) {
                        Log::warning("A tabela tem {$totalRegistros->total} registros, mas a consulta não retornou resultados");
                        
                        // Tentar consulta alternativa mais simples para obter pelo menos alguns dados
                        $problemas = DB::select("SELECT id, descricao, COALESCE(prioridade, 'media') as prioridade, 
                                               usuario_id, responsavel_id, status FROM rh_problemas");
                                               
                        Log::info("Consulta alternativa recuperou " . count($problemas) . " problemas");
                        
                        if (count($problemas) == 0) {
                            // Se não houver resultados, não criar exemplos, apenas retornar 0
                            Log::warning("Não foi possível recuperar dados da tabela rh_problemas.");
                            return 0;
                        }
                    } else {
                        // Se não houver dados, retornar 0 em vez de criar exemplos
                        Log::warning("A tabela rh_problemas está vazia.");
                        return 0;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Erro ao buscar problemas: " . $e->getMessage());
                Log::error("Stack trace: " . $e->getTraceAsString());
                
                // Tentar consulta alternativa com campos mínimos
                try {
                    $problemas = DB::select("SELECT id, descricao, COALESCE(prioridade, 'media') as prioridade, 
                                           usuario_id, responsavel_id, status FROM rh_problemas");
                    
                    if (!$problemas || count($problemas) == 0) {
                        // Se não houver resultados, não criar exemplos, apenas retornar 0
                        Log::warning("Consulta alternativa também não retornou resultados.");
                        return 0;
                    }
                    
                    Log::info("Consulta alternativa recuperou " . count($problemas) . " problemas");
                } catch (\Exception $e2) {
                    Log::error("Erro na consulta alternativa: " . $e2->getMessage());
                    return 0;
                }
            }
            
            Log::info("Encontrados " . count($problemas) . " problemas para sincronizar");
            $count = 0;
            
            foreach ($problemas as $problema) {
                try {
                    // Verificar se a descrição existe
                    if (empty($problema->descricao)) {
                        Log::warning("Problema ID {$problema->id} não tem descrição. Ignorando...");
                        continue;
                    }
                    
                    // Mapeamento de prioridade
                    $prioridade = 'media'; // padrão
                    
                    // Verificar se tem o campo prioridade e processá-lo
                    if (isset($problema->prioridade) && $problema->prioridade) {
                        $prioridadeLower = strtolower($problema->prioridade);
                        
                        if (in_array($prioridadeLower, ['baixa', 'média', 'media', 'alta'])) {
                            if ($prioridadeLower == 'média') {
                                $prioridade = 'media';
                            } else {
                                $prioridade = $prioridadeLower;
                            }
                        } else if (strpos($prioridadeLower, 'baixa') !== false) {
                            $prioridade = 'baixa';
                        } else if (strpos($prioridadeLower, 'alta') !== false) {
                            $prioridade = 'alta';
                        } else if (strpos($prioridadeLower, 'média') !== false || strpos($prioridadeLower, 'media') !== false) {
                            $prioridade = 'media';
                        }
                    } 
                    // Verificar o campo status também que pode conter a prioridade
                    else if (isset($problema->status) && $problema->status) {
                        $statusLower = strtolower($problema->status);
                        
                        if (in_array($statusLower, ['baixa', 'média', 'media', 'alta'])) {
                            if ($statusLower == 'média') {
                                $prioridade = 'media';
                            } else {
                                $prioridade = $statusLower;
                            }
                        } else if (strpos($statusLower, 'baixa') !== false) {
                            $prioridade = 'baixa';
                        } else if (strpos($statusLower, 'alta') !== false) {
                            $prioridade = 'alta';
                        } else if (strpos($statusLower, 'média') !== false || strpos($statusLower, 'media') !== false) {
                            $prioridade = 'media';
                        }
                    }
                    
                    Log::info("Criando tarefa de cronograma para problema ID {$problema->id}: '{$problema->descricao}' com prioridade '{$prioridade}'");
                    
                    // Criar novo cronograma com a descrição do problema
                    $cronograma = self::create([
                        'descricao' => $problema->descricao,
                        'status' => $prioridade
                    ]);
                    
                    // Se tiver usuário associado, criar o relacionamento
                    if (isset($problema->usuario_id) && $problema->usuario_id) {
                        try {
                            DB::table('cronograma_usuarios')->insert([
                                'cronograma_id' => $cronograma->id,
                                'usuario_id' => $problema->usuario_id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                        } catch (\Exception $e) {
                            Log::warning("Erro ao associar usuário {$problema->usuario_id} ao cronograma {$cronograma->id}: " . $e->getMessage());
                        }
                    }
                    
                    // Se tiver responsável associado, criar o relacionamento se for diferente do usuário
                    if (isset($problema->responsavel_id) && $problema->responsavel_id) {
                        try {
                            // Verificar se o usuário já está associado
                            $jaAssociado = DB::table('cronograma_usuarios')
                                ->where('cronograma_id', $cronograma->id)
                                ->where('usuario_id', $problema->responsavel_id)
                                ->exists();
                                
                            if (!$jaAssociado) {
                                DB::table('cronograma_usuarios')->insert([
                                    'cronograma_id' => $cronograma->id,
                                    'usuario_id' => $problema->responsavel_id,
                                    'created_at' => now(),
                                    'updated_at' => now()
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::warning("Erro ao associar responsável {$problema->responsavel_id} ao cronograma {$cronograma->id}: " . $e->getMessage());
                        }
                    }
                    
                    $count++;
                    if ($count % 10 == 0) {
                        Log::info("Sincronizadas {$count} tarefas até o momento");
                    }
                } catch (\Exception $e) {
                    Log::error("Erro ao sincronizar problema ID {$problema->id}: " . $e->getMessage());
                }
            }
            
            Log::info("Sincronização concluída. {$count} tarefas importadas.");
            return $count;
        } catch (\Exception $e) {
            Log::error("Erro na sincronização: " . $e->getMessage());
            Log::error("Stack trace: " . $e->getTraceAsString());
            return -1;
        }
    }

    /**
     * Método para criar dados de exemplo quando não há problemas do RH
     * NOTA: Este método foi desativado para evitar a criação de dados de exemplo.
     */
    private static function criarDadosExemplo()
    {
        Log::warning("Método criarDadosExemplo foi chamado, mas está desativado.");
        return 0;
    }

    /**
     * Método para migrar usuários antigos para o relacionamento muitos-para-muitos
     */
    public static function migrarUsuariosAntigos()
    {
        try {
            // Verificar se a coluna usuario_id ainda existe
            if (Schema::hasColumn('cronogramas', 'usuario_id')) {
                // Buscar todos os cronogramas com usuario_id preenchido
                $cronogramas = self::whereNotNull('usuario_id')->get();
                $count = 0;
                
                foreach ($cronogramas as $cronograma) {
                    // Verificar se já existe o relacionamento na tabela pivot
                    $existe = DB::table('cronograma_usuarios')
                        ->where('cronograma_id', $cronograma->id)
                        ->where('usuario_id', $cronograma->usuario_id)
                        ->exists();
                    
                    if (!$existe) {
                        // Adicionar à tabela pivot
                        DB::table('cronograma_usuarios')->insert([
                            'cronograma_id' => $cronograma->id,
                            'usuario_id' => $cronograma->usuario_id,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                        
                        $count++;
                    }
                }
                
                Log::info("Migração de usuarios_id concluída. {$count} usuários migrados.");
                return $count;
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error("Erro na migração de usuários: " . $e->getMessage());
            return -1;
        }
    }

    /**
     * Override do método toArray para garantir que os usuários sejam incluídos
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Garantir que o relacionamento usuarios esteja carregado
        if (!$this->relationLoaded('usuarios')) {
            $this->load('usuarios');
        }
        
        // Adicionar os usuários ao array
        $array['usuarios'] = $this->usuarios;
        
        return $array;
    }
} 