<?php

namespace Tests\Feature;

use Tests\TestCase;

class CategoriaApiTest extends TestCase
{
    public function test_lista_categorias_retorna_lista_para_usuario_autenticado(): void
    {
        // Arrange
        $this->actingAsOfflineUser();

        // Act
        $response = $this->getJson('/api/categorias');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([['id', 'nome']])
            ->assertJsonFragment(['id' => 1, 'nome' => 'Bolos e tortas doces']);
    }

    public function test_lista_categorias_exige_autenticacao(): void
    {
        // Act
        $response = $this->getJson('/api/categorias');

        // Assert
        $response->assertStatus(401);
    }
}
