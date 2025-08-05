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
        // Apagar a tabela se existir e recriar
        Schema::dropIfExists('baixas');
        
        Schema::create('baixas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funcionario_id');
            $table->unsignedBigInteger('produto_id');
            $table->integer('quantidade');
            $table->text('observacoes')->nullable();
            $table->timestamp('data_baixa');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();
            
            // Indexes para performance
            $table->index(['funcionario_id', 'data_baixa']);
            $table->index(['produto_id', 'data_baixa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('baixas');
    }
};
