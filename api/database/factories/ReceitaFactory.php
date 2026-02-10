<?php

namespace Database\Factories;

use App\Models\Receita;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Receita>
 */
class ReceitaFactory extends Factory
{
    protected $model = Receita::class;

    public function definition(): array
    {
        return [
            'id_usuarios' => User::factory(),
            'id_categorias' => 1,
            'nome' => fake()->words(3, true),
            'tempo_preparo_minutos' => fake()->numberBetween(15, 120),
            'porcoes' => fake()->numberBetween(1, 12),
            'modo_preparo' => fake()->paragraphs(2, true),
            'ingredientes' => fake()->paragraph(),
        ];
    }
}
