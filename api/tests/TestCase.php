<?php

namespace Tests;

use App\Contracts\AuthService;
use App\Contracts\CategoriaService;
use App\Contracts\ReceitaService;
use App\Models\User;
use App\Services\Auth\FakeAuthService;
use App\Services\Categorias\FakeCategoriaService;
use App\Support\OfflineJsonStore;
use App\Services\Receitas\FakeReceitaService;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        if ((bool) config('testing.offline_mode', false)) {
            $this->app->bind(AuthService::class, FakeAuthService::class);
            $this->app->bind(CategoriaService::class, FakeCategoriaService::class);
            $this->app->bind(ReceitaService::class, FakeReceitaService::class);
            OfflineJsonStore::reset();
        }
    }

    /**
     * Carrega um fixture JSON (dados mockados) de tests/fixtures/.
     *
     * @param  string  $name  Nome do arquivo (ex: 'user.json', 'receita.json')
     * @return array<string, mixed>
     */
    protected function loadFixture(string $name): array
    {
        $path = __DIR__.'/fixtures/'.ltrim($name, '/');

        if (! is_file($path)) {
            $this->fail("Fixture não encontrado: {$path}");
        }

        $json = file_get_contents($path);
        $decoded = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->fail('Fixture JSON inválido: '.json_last_error_msg());
        }

        return $decoded;
    }

    protected function actingAsOfflineUser(int $id = 1, string $nome = 'Usuário Teste', string $login = 'teste'): User
    {
        $user = new User();
        $user->forceFill([
            'id' => $id,
            'nome' => $nome,
            'login' => $login,
        ]);

        Sanctum::actingAs($user);

        return $user;
    }
}
