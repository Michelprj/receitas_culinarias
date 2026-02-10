<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReceitaApiTest extends TestCase
{
    public function test_lista_receitas_retorna_apenas_do_usuario(): void
    {
        // Arrange
        $this->actingAsOfflineUser(1, 'Usuário A', 'usuario_a');
        $payload = [
            'id_categorias' => 1,
            'tempo_preparo_minutos' => 30,
            'porcoes' => 2,
            'modo_preparo' => 'Teste',
            'ingredientes' => 'Teste',
        ];
        $this->postJson('/api/receitas', array_merge($payload, ['nome' => 'Minha receita 1']))->assertStatus(201);
        $this->postJson('/api/receitas', array_merge($payload, ['nome' => 'Minha receita 2']))->assertStatus(201);
        $this->actingAsOfflineUser(2, 'Usuário B', 'usuario_b');
        $this->postJson('/api/receitas', array_merge($payload, ['nome' => 'Receita do outro']))->assertStatus(201);
        $this->actingAsOfflineUser(1, 'Usuário A', 'usuario_a');

        // Act
        $response = $this->getJson('/api/receitas');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $nomes = array_column($data, 'nome');
        $this->assertContains('Minha receita 1', $nomes);
        $this->assertContains('Minha receita 2', $nomes);
    }

    public function test_lista_receitas_aceita_filtro_q(): void
    {
        // Arrange
        $this->actingAsOfflineUser();
        $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Bolo de chocolate',
            'tempo_preparo_minutos' => 45,
            'porcoes' => 8,
            'modo_preparo' => 'Misture',
            'ingredientes' => 'Chocolate',
        ])->assertStatus(201);
        $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Torta de limão',
            'tempo_preparo_minutos' => 45,
            'porcoes' => 8,
            'modo_preparo' => 'Misture',
            'ingredientes' => 'Limão',
        ])->assertStatus(201);

        // Act
        $response = $this->getJson('/api/receitas?q=chocolate');

        // Assert
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertStringContainsString('chocolate', $data[0]['nome']);
    }

    public function test_lista_receitas_exige_autenticacao(): void
    {
        // Act
        $response = $this->getJson('/api/receitas');

        // Assert
        $response->assertStatus(401);
    }

    public function test_cadastra_receita_com_sucesso(): void
    {
        // Arrange
        $this->actingAsOfflineUser();
        $payload = [
            'id_categorias' => 1,
            'nome' => 'Bolo de chocolate',
            'tempo_preparo_minutos' => 45,
            'porcoes' => 8,
            'modo_preparo' => 'Misture tudo e asse.',
            'ingredientes' => 'Farinha, ovos, chocolate.',
        ];

        // Act
        $response = $this->postJson('/api/receitas', $payload);

        // Assert
        $response->assertStatus(201)
            ->assertJsonPath('nome', 'Bolo de chocolate')
            ->assertJsonPath('id_categorias', 1)
            ->assertJsonStructure(['id', 'categoria']);
    }

    public function test_cadastra_receita_falha_sem_autenticacao(): void
    {
        // Arrange
        $payload = [
            'id_categorias' => 1,
            'nome' => 'Bolo',
            'tempo_preparo_minutos' => 30,
            'porcoes' => 4,
            'modo_preparo' => '...',
            'ingredientes' => '...',
        ];

        // Act
        $response = $this->postJson('/api/receitas', $payload);

        // Assert
        $response->assertStatus(401);
    }

    public function test_cadastra_receita_falha_por_validacao(): void
    {
        // Arrange
        $this->actingAsOfflineUser();
        $payload = [
            'id_categorias' => 999,
            'nome' => '',
            'tempo_preparo_minutos' => 0,
            'porcoes' => 0,
            'modo_preparo' => '',
            'ingredientes' => '',
        ];

        // Act
        $response = $this->postJson('/api/receitas', $payload);

        // Assert
        $response->assertStatus(400)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_exibe_receita_retorna_receita_do_usuario(): void
    {
        // Arrange
        $this->actingAsOfflineUser();
        $created = $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Minha receita',
            'tempo_preparo_minutos' => 10,
            'porcoes' => 1,
            'modo_preparo' => 'Teste',
            'ingredientes' => 'Teste',
        ])->assertStatus(201)->json();
        $receitaId = $created['id'];

        // Act
        $response = $this->getJson('/api/receitas/'.$receitaId);

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('id', $receitaId)
            ->assertJsonPath('nome', 'Minha receita');
    }

    public function test_exibe_receita_retorna_404_para_receita_de_outro_usuario(): void
    {
        // Arrange
        $this->actingAsOfflineUser(2, 'Outro', 'outro');
        $created = $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Receita outro',
            'tempo_preparo_minutos' => 10,
            'porcoes' => 1,
            'modo_preparo' => 'Teste',
            'ingredientes' => 'Teste',
        ])->assertStatus(201)->json();
        $receitaId = $created['id'];
        $this->actingAsOfflineUser(1, 'Principal', 'principal');

        // Act
        $response = $this->getJson('/api/receitas/'.$receitaId);

        // Assert
        $response->assertStatus(404);
    }

    public function test_atualiza_receita_com_sucesso(): void
    {
        // Arrange
        $this->actingAsOfflineUser();
        $created = $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Nome antigo',
            'tempo_preparo_minutos' => 10,
            'porcoes' => 1,
            'modo_preparo' => 'Teste',
            'ingredientes' => 'Teste',
        ])->assertStatus(201)->json();
        $receitaId = $created['id'];
        $payload = ['nome' => 'Nome novo', 'porcoes' => 10];

        // Act
        $response = $this->putJson('/api/receitas/'.$receitaId, $payload);

        // Assert
        $response->assertStatus(200)
            ->assertJsonPath('nome', 'Nome novo')
            ->assertJsonPath('porcoes', 10);
    }

    public function test_atualiza_receita_retorna_404_para_receita_de_outro_usuario(): void
    {
        // Arrange
        $this->actingAsOfflineUser(2, 'Outro', 'outro');
        $created = $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Receita outro',
            'tempo_preparo_minutos' => 10,
            'porcoes' => 1,
            'modo_preparo' => 'Teste',
            'ingredientes' => 'Teste',
        ])->assertStatus(201)->json();
        $receitaId = $created['id'];
        $this->actingAsOfflineUser(1, 'Principal', 'principal');

        // Act
        $response = $this->putJson('/api/receitas/'.$receitaId, ['nome' => 'Hack']);

        // Assert
        $response->assertStatus(404);
    }

    public function test_exclui_receita_com_sucesso(): void
    {
        // Arrange
        $this->actingAsOfflineUser();
        $created = $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Receita para excluir',
            'tempo_preparo_minutos' => 10,
            'porcoes' => 1,
            'modo_preparo' => 'Teste',
            'ingredientes' => 'Teste',
        ])->assertStatus(201)->json();
        $receitaId = $created['id'];

        // Act
        $response = $this->deleteJson('/api/receitas/'.$receitaId);

        // Assert
        $response->assertStatus(200)
            ->assertJson(['message' => 'Receita excluída com sucesso.']);
        $this->getJson('/api/receitas/'.$receitaId)->assertStatus(404);
    }

    public function test_exclui_receita_retorna_404_para_receita_de_outro_usuario(): void
    {
        // Arrange
        $this->actingAsOfflineUser(2, 'Outro', 'outro');
        $created = $this->postJson('/api/receitas', [
            'id_categorias' => 1,
            'nome' => 'Receita outro',
            'tempo_preparo_minutos' => 10,
            'porcoes' => 1,
            'modo_preparo' => 'Teste',
            'ingredientes' => 'Teste',
        ])->assertStatus(201)->json();
        $receitaId = $created['id'];
        $this->actingAsOfflineUser(1, 'Principal', 'principal');

        // Act
        $response = $this->deleteJson('/api/receitas/'.$receitaId);

        // Assert
        $response->assertStatus(404);
        $this->actingAsOfflineUser(2, 'Outro', 'outro');
        $this->getJson('/api/receitas/'.$receitaId)->assertStatus(200);
    }
}
