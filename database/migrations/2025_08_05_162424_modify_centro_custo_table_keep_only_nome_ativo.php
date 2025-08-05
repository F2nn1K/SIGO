<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('centro_custo', function (Blueprint $table) {
            // Remover índices primeiro
            $table->dropIndex(['ativo', 'nome']);
            $table->dropUnique(['codigo']);
            
            // Remover colunas desnecessárias
            $table->dropColumn(['codigo', 'descricao']);
            $table->dropTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('centro_custo', function (Blueprint $table) {
            // Restaurar colunas removidas
            $table->string('codigo', 50)->unique();
            $table->text('descricao')->nullable();
            $table->timestamps();
            
            // Restaurar índices
            $table->index(['ativo', 'nome']);
        });
    }
};
