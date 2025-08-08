<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('solicitacao_id');
            $table->unsignedBigInteger('usuario_id');
            $table->enum('tipo', ['comentario','aprovacao','rejeicao','solicitacao_info']);
            $table->text('mensagem')->nullable();
            $table->json('dados_extras')->nullable();
            $table->timestamps();

            $table->index(['solicitacao_id','created_at']);
            $table->index(['usuario_id','tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interacao');
    }
};
