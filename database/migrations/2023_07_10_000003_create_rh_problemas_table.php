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
        Schema::create('rh_problemas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->string('status')->default('Pendente');
            $table->enum('prioridade', ['baixa', 'media', 'alta'])->default('media');
            $table->timestamp('horario')->nullable();
            $table->timestamp('inicio_contagem')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('usuario_nome')->nullable();
            $table->json('detalhes')->nullable();
            $table->text('resposta')->nullable();
            $table->foreignId('respondido_por')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('data_resposta')->nullable();
            $table->foreignId('responsavel_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('finalizado_em')->nullable();
            $table->timestamp('prazo_entrega')->nullable();
            $table->timestamps();
        });

        // Tabela de anotações relacionadas aos problemas
        Schema::create('rh_anotacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('problema_id')->constrained('rh_problemas')->onDelete('cascade');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('conteudo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rh_anotacoes');
        Schema::dropIfExists('rh_problemas');
    }
}; 