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
        Schema::table('baixas', function (Blueprint $table) {
            // Verificar se a coluna não existe e adicioná-la
            if (!Schema::hasColumn('baixas', 'quantidade')) {
                $table->integer('quantidade')->after('produto_id');
            }
            if (!Schema::hasColumn('baixas', 'funcionario_id')) {
                $table->unsignedBigInteger('funcionario_id')->after('id');
            }
            if (!Schema::hasColumn('baixas', 'produto_id')) {
                $table->unsignedBigInteger('produto_id')->after('funcionario_id');
            }
            if (!Schema::hasColumn('baixas', 'observacoes')) {
                $table->text('observacoes')->nullable()->after('quantidade');
            }
            if (!Schema::hasColumn('baixas', 'data_baixa')) {
                $table->timestamp('data_baixa')->after('observacoes');
            }
            if (!Schema::hasColumn('baixas', 'usuario_id')) {
                $table->unsignedBigInteger('usuario_id')->after('data_baixa');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('baixas', function (Blueprint $table) {
            $table->dropColumn(['quantidade', 'funcionario_id', 'produto_id', 'observacoes', 'data_baixa', 'usuario_id']);
        });
    }
};
