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
        // Tabela principal - Documentos DP
        Schema::create('documentos_dp', function (Blueprint $table) {
            $table->id();
            $table->string('nome_funcionario', 255);
            $table->string('funcao', 255);
            $table->unsignedBigInteger('usuario_id'); // Quem cadastrou
            $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            
            // Foreign key com cascade
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete('cascade');
            
            // Índices para performance e consultas
            $table->index('usuario_id');
            $table->index('status');
            $table->index('created_at');
        });

        // Tabela de itens - Documentos individuais
        Schema::create('documentos_dp_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('documento_dp_id');
            $table->string('tipo_documento', 255); // Nome do documento
            $table->boolean('selecionado')->default(false); // Se foi marcado no checkbox
            $table->string('arquivo_nome', 255)->nullable(); // Nome original do arquivo
            $table->string('arquivo_path', 500)->nullable(); // Caminho seguro do arquivo
            $table->string('arquivo_extensao', 10)->nullable(); // Extensão do arquivo
            $table->integer('arquivo_tamanho')->nullable(); // Tamanho em bytes
            $table->string('arquivo_hash', 64)->nullable(); // Hash do arquivo para integridade
            $table->timestamps();
            
            // Foreign key com cascade
            $table->foreign('documento_dp_id')->references('id')->on('documentos_dp')->onDelete('cascade');
            
            // Índices para performance
            $table->index('documento_dp_id');
            $table->index('tipo_documento');
            $table->index('selecionado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_dp_itens');
        Schema::dropIfExists('documentos_dp');
    }
};
