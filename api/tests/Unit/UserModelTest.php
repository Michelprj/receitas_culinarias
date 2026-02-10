<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    public function test_senha_e_hasheada_automaticamente(): void
    {
        // Arrange
        $senhaPlana = 'minhasenha123';

        // Act
        $user = new User();
        $user->senha = $senhaPlana;

        // Assert
        $this->assertNotSame($senhaPlana, $user->senha);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check($senhaPlana, $user->senha));
    }

    public function test_usuario_tem_muitas_receitas(): void
    {
        // Arrange
        $user = new User();

        // Assert
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $user->receitas());
    }
}
