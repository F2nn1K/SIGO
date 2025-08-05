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
            $table->unsignedBigInteger('centro_custo_id')->nullable()->after('funcionario_id');
            $table->foreign('centro_custo_id')->references('id')->on('centro_custo')->onDelete('set null');
            $table->index('centro_custo_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('baixas', function (Blueprint $table) {
            $table->dropForeign(['centro_custo_id']);
            $table->dropIndex(['centro_custo_id']);
            $table->dropColumn('centro_custo_id');
        });
    }
};
