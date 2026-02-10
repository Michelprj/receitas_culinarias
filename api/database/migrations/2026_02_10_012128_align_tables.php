<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('receitas', function (Blueprint $table) {
            $table->dropForeign(['id_usuarios']);
            $table->dropForeign(['id_categorias']);
        });

        if (Schema::hasColumns('categorias', ['created_at', 'updated_at'])) {
            Schema::table('categorias', function (Blueprint $table) {
                $table->dropTimestamps();
            });
        }
        DB::statement('ALTER TABLE categorias MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE categorias MODIFY nome VARCHAR(100) NOT NULL');

        DB::statement('ALTER TABLE usuarios MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE usuarios MODIFY senha VARCHAR(100) NOT NULL');

        DB::statement('ALTER TABLE receitas MODIFY id INT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE receitas MODIFY id_usuarios INT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE receitas MODIFY id_categorias INT UNSIGNED NOT NULL');

        Schema::table('receitas', function (Blueprint $table) {
            $table->foreign('id_usuarios')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('id_categorias')->references('id')->on('categorias')->cascadeOnDelete();
        });
    }

    public function down(): void
    {

        Schema::table('receitas', function (Blueprint $table) {
            $table->dropForeign(['id_usuarios']);
            $table->dropForeign(['id_categorias']);
        });

        DB::statement('ALTER TABLE receitas MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE receitas MODIFY id_usuarios BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE receitas MODIFY id_categorias BIGINT UNSIGNED NOT NULL');

        DB::statement('ALTER TABLE usuarios MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        DB::statement('ALTER TABLE usuarios MODIFY senha VARCHAR(255) NOT NULL');

        DB::statement('ALTER TABLE categorias MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');

        Schema::table('receitas', function (Blueprint $table) {
            $table->foreign('id_usuarios')->references('id')->on('usuarios')->cascadeOnDelete();
            $table->foreign('id_categorias')->references('id')->on('categorias')->cascadeOnDelete();
        });
    }
};
