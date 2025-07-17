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
        // Tabela principal de cronogramas
        Schema::create('cronogramas', function (Blueprint $table) {
            $table->id();
            $table->string('descricao');
            $table->string('status')->default('media');
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Tabela de datas do cronograma
        Schema::create('cronograma_datas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cronograma_id')->constrained('cronogramas')->onDelete('cascade');
            $table->date('data');
            $table->integer('mes');
            $table->integer('ano');
            $table->string('descricao')->nullable();
            $table->timestamps();
        });

        // Tabela pivô entre cronogramas e usuários
        Schema::create('cronograma_usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cronograma_id')->constrained('cronogramas')->onDelete('cascade');
            $table->foreignId('usuario_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Tabela de eventos do cronograma
        Schema::create('cronograma_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cronograma_id')->constrained('cronogramas')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descricao')->nullable();
            $table->dateTime('inicio');
            $table->dateTime('fim')->nullable();
            $table->string('cor')->nullable();
            $table->boolean('dia_inteiro')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cronograma_eventos');
        Schema::dropIfExists('cronograma_usuarios');
        Schema::dropIfExists('cronograma_datas');
        Schema::dropIfExists('cronogramas');
    }
}; 