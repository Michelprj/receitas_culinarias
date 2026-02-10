<?php

namespace Tests\Unit;

use App\Models\Receita;
use App\Models\User;
use Tests\TestCase;

class ReceitaModelTest extends TestCase
{
    public function test_receita_pertence_a_usuario(): void
    {
        // Arrange
        $user = new User();
        $user->id = 10;
        $receita = new Receita();
        $receita->id_usuarios = 10;
        $receita->setRelation('usuario', $user);

        // Assert
        $this->assertInstanceOf(User::class, $receita->usuario);
        $this->assertSame(10, (int) $receita->usuario->id);
    }

    public function test_receita_usa_timestamps_customizados(): void
    {
        // Arrange
        $receita = new Receita();

        // Assert
        $this->assertSame('criado_em', $receita->getCreatedAtColumn());
        $this->assertSame('alterado_em', $receita->getUpdatedAtColumn());
    }
}
