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
        // Tabela de grupos de permissões
        Schema::create('permission_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Tabela de permissões
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('group_id')->nullable()->constrained('permission_groups')->onDelete('set null');
            $table->timestamps();
        });

        // Tabela pivô entre perfis e permissões
        Schema::create('profile_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('permissions')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('permission_groups');
    }
}; 