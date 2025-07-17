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
        // Tabela de funcionários
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('departamento')->nullable();
            $table->string('funcao')->nullable();
            $table->decimal('valor', 10, 2)->default(0);
            $table->text('observacao')->nullable();
            $table->string('empresa')->nullable();
            $table->timestamps();
        });

        // Tabela de diárias
        Schema::create('diarias', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('departamento')->nullable();
            $table->string('funcao')->nullable();
            $table->decimal('diaria', 10, 2)->default(0);
            $table->string('referencia')->nullable();
            $table->text('observacao')->nullable();
            $table->string('gerente')->nullable();
            $table->string('chave')->nullable();
            $table->timestamp('data_inclusao')->nullable();
            $table->timestamp('visualizado')->nullable();
            $table->string('empresa')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diarias');
        Schema::dropIfExists('funcionarios');
    }
}; 