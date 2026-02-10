<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthApiTest extends TestCase
{
    public function test_register_creates_user_and_returns_token(): void
    {
        // Arrange
        $payload = [
            'nome' => 'Maria Silva',
            'login' => 'maria',
            'senha' => 'senha123',
            'senha_confirmation' => 'senha123',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(201)
            ->assertJsonStructure([
                'usuario' => ['id', 'nome', 'login'],
                'token',
                'token_type',
            ])
            ->assertJson([
                'usuario' => [
                    'nome' => 'Maria Silva',
                    'login' => 'maria',
                ],
                'token_type' => 'Bearer',
            ]);
    }

    public function test_register_fails_with_duplicate_login(): void
    {
        // Arrange
        $this->postJson('/api/register', [
            'nome' => 'Maria',
            'login' => 'maria',
            'senha' => 'senha123',
            'senha_confirmation' => 'senha123',
        ])->assertStatus(201);

        $payload = [
            'nome' => 'Outra Maria',
            'login' => 'maria',
            'senha' => 'senha123',
            'senha_confirmation' => 'senha123',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(400)
            ->assertJsonPath('errors.login.0', 'Usuário já existe');
    }

    public function test_register_fails_with_validation_errors(): void
    {
        // Arrange
        $payload = [
            'nome' => '',
            'login' => '',
            'senha' => '123',
            'senha_confirmation' => 'different',
        ];

        // Act
        $response = $this->postJson('/api/register', $payload);

        // Assert
        $response->assertStatus(400)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_login_returns_token_for_valid_credentials(): void
    {
        // Arrange
        $this->postJson('/api/register', [
            'nome' => 'Maria',
            'login' => 'maria',
            'senha' => 'senha123',
            'senha_confirmation' => 'senha123',
        ])->assertStatus(201);

        // Act
        $response = $this->postJson('/api/login', [
            'login' => 'maria',
            'senha' => 'senha123',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'usuario' => ['id', 'nome', 'login'],
                'token',
                'token_type',
            ])
            ->assertJson([
                'usuario' => ['login' => 'maria', 'nome' => 'Maria'],
                'token_type' => 'Bearer',
            ]);
    }

    public function test_login_fails_for_invalid_credentials(): void
    {
        // Arrange
        $this->postJson('/api/register', [
            'nome' => 'Maria',
            'login' => 'maria',
            'senha' => 'senha123',
            'senha_confirmation' => 'senha123',
        ])->assertStatus(201);

        // Act
        $response = $this->postJson('/api/login', [
            'login' => 'maria',
            'senha' => 'wrong',
        ]);

        // Assert
        $response->assertStatus(400)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_logout_invalidates_token(): void
    {
        // Arrange
        $this->actingAsOfflineUser();

        // Act
        $response = $this->postJson('/api/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson(['message' => 'Logout realizado com sucesso.']);
    }

    public function test_logout_requires_authentication(): void
    {
        // Act
        $response = $this->postJson('/api/logout');

        // Assert
        $response->assertStatus(401);
    }

    public function test_user_returns_authenticated_user_data(): void
    {
        // Arrange
        $user = $this->actingAsOfflineUser(10, 'Maria', 'maria');

        // Act
        $response = $this->getJson('/api/user');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'id' => $user->id,
                'nome' => 'Maria',
                'login' => 'maria',
            ]);
    }

    public function test_user_requires_authentication(): void
    {
        // Act
        $response = $this->getJson('/api/user');

        // Assert
        $response->assertStatus(401);
    }
}
