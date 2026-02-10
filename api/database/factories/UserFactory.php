<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'nome' => fake()->name(),
            'login' => fake()->unique()->userName(),
            'senha' => 'password',
        ];
    }

    public function withPassword(string $password): static
    {
        return $this->state(fn () => [
            'senha' => $password,
        ]);
    }
}
