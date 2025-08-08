<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estoque_pedido', function (Blueprint $table) {
            $table->id();
            $table->string('produto');
            $table->text('descricao')->nullable();
            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->timestamps();

            $table->index('produto');
            $table->index('centro_custo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estoque_pedido');
    }
};
