<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_usuarios')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('id_categorias')->constrained('categorias')->cascadeOnDelete();
            $table->string('nome', 45);
            $table->unsignedInteger('tempo_preparo_minutos');
            $table->unsignedInteger('porcoes');
            $table->text('modo_preparo');
            $table->text('ingredientes');
            $table->dateTime('criado_em')->nullable();
            $table->dateTime('alterado_em')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receitas');
    }
};
