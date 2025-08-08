<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitacao', function (Blueprint $table) {
            $table->id();
            $table->string('num_pedido')->nullable()->index();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->string('produto_nome');
            $table->integer('quantidade');
            $table->enum('prioridade', ['baixa','media','alta'])->default('media');
            $table->text('observacao')->nullable();
            $table->enum('aprovacao', ['pendente','aprovado','rejeitado'])->default('pendente');
            $table->timestamp('data_solicitacao')->nullable();
            $table->timestamp('data_aprovacao')->nullable();
            $table->unsignedBigInteger('id_aprovador')->nullable();
            $table->timestamps();

            $table->index(['usuario_id','aprovacao']);
            $table->index(['centro_custo_id','aprovacao']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitacao');
    }
};
