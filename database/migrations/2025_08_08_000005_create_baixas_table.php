<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('baixas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('funcionario_id');
            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->unsignedBigInteger('produto_id');
            $table->integer('quantidade');
            $table->text('observacoes')->nullable();
            $table->timestamp('data_baixa');
            $table->unsignedBigInteger('usuario_id');
            $table->timestamps();

            $table->index(['funcionario_id','data_baixa']);
            $table->index(['produto_id','data_baixa']);
            $table->index('centro_custo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('baixas');
    }
};
