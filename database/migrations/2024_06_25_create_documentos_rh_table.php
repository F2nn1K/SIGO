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
        Schema::create('documentos_rh', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('codigo')->unique();
            $table->string('foto')->nullable();
            $table->string('doc_fotos')->nullable();
            $table->string('doc_carteira_saude')->nullable();
            $table->string('doc_exame');
            $table->string('doc_antecedente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_rh');
    }
}; 