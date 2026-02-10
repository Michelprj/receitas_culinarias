<?php

namespace App\Support;

use Illuminate\Support\Facades\Hash;

class OfflineJsonStore
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $users = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $categorias = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    private static array $receitas = [];

    private static int $nextUserId = 1;
    private static int $nextReceitaId = 1;

    public static function reset(): void
    {
        self::$users = [];
        self::$receitas = [];
        self::$nextUserId = 1;
        self::$nextReceitaId = 1;

        $categorias = self::loadJsonFixture('categorias.json');

        if (! is_array($categorias)) {
            $categorias = [
                ['id' => 1, 'nome' => 'Bolos e tortas doces'],
                ['id' => 2, 'nome' => 'Carnes'],
                ['id' => 3, 'nome' => 'Aves'],
            ];
        }

        self::$categorias = array_values($categorias);
    }

    public static function hasLogin(string $login): bool
    {
        foreach (self::$users as $user) {
            if (($user['login'] ?? null) === $login) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array{nome:string,login:string,senha:string}  $data
     * @return array<string, mixed>
     */
    public static function createUser(array $data): array
    {
        $user = [
            'id' => self::$nextUserId++,
            'nome' => $data['nome'],
            'login' => $data['login'],
            'senha' => Hash::make($data['senha']),
        ];

        self::$users[] = $user;

        return $user;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findUserByLogin(string $login): ?array
    {
        foreach (self::$users as $user) {
            if (($user['login'] ?? null) === $login) {
                return $user;
            }
        }

        return null;
    }

    public static function checkUserPassword(array $user, string $senha): bool
    {
        return Hash::check($senha, (string) ($user['senha'] ?? ''));
    }

    public static function createToken(int $userId): string
    {
        return 'offline-token-'.$userId.'-'.bin2hex(random_bytes(8));
    }

    /**
     * @return array<int, array{id:int,nome:string}>
     */
    public static function categorias(): array
    {
        $categorias = array_map(
            fn (array $c) => ['id' => (int) $c['id'], 'nome' => (string) $c['nome']],
            self::$categorias
        );

        usort(
            $categorias,
            fn (array $a, array $b) => strcmp($a['nome'], $b['nome'])
        );

        return $categorias;
    }

    public static function categoriaExists(int $id): bool
    {
        foreach (self::$categorias as $categoria) {
            if ((int) $categoria['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     */
    public static function createReceita(int $userId, array $data): array
    {
        $receita = [
            'id' => self::$nextReceitaId++,
            'id_usuarios' => $userId,
            'id_categorias' => (int) $data['id_categorias'],
            'nome' => (string) $data['nome'],
            'tempo_preparo_minutos' => (int) $data['tempo_preparo_minutos'],
            'porcoes' => (int) $data['porcoes'],
            'modo_preparo' => (string) $data['modo_preparo'],
            'ingredientes' => (string) $data['ingredientes'],
            'criado_em' => now()->toISOString(),
            'alterado_em' => now()->toISOString(),
        ];

        self::$receitas[] = $receita;

        return self::withCategoria($receita);
    }

    /**
     * @return array<string, mixed>
     */
    public static function listReceitas(int $userId, ?string $q = null, ?int $categoriaId = null): array
    {
        $items = array_values(array_filter(
            self::$receitas,
            function (array $r) use ($userId, $q, $categoriaId): bool {
                if ((int) $r['id_usuarios'] !== $userId) {
                    return false;
                }

                if ($categoriaId !== null && (int) $r['id_categorias'] !== $categoriaId) {
                    return false;
                }

                if ($q !== null && $q !== '') {
                    $needle = mb_strtolower($q);
                    $haystack = mb_strtolower(
                        (string) $r['nome'].' '.(string) $r['ingredientes'].' '.(string) $r['modo_preparo']
                    );

                    if (! str_contains($haystack, $needle)) {
                        return false;
                    }
                }

                return true;
            }
        ));

        usort(
            $items,
            fn (array $a, array $b) => strcmp((string) $b['criado_em'], (string) $a['criado_em'])
        );

        return [
            'current_page' => 1,
            'data' => array_map(fn (array $r) => self::withCategoria($r), $items),
            'first_page_url' => null,
            'from' => count($items) > 0 ? 1 : null,
            'last_page' => 1,
            'last_page_url' => null,
            'links' => [],
            'next_page_url' => null,
            'path' => null,
            'per_page' => 15,
            'prev_page_url' => null,
            'to' => count($items),
            'total' => count($items),
        ];
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function findReceitaForUser(int $userId, int $receitaId): ?array
    {
        foreach (self::$receitas as $receita) {
            if ((int) $receita['id'] === $receitaId && (int) $receita['id_usuarios'] === $userId) {
                return self::withCategoria($receita);
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>|null
     */
    public static function updateReceitaForUser(int $userId, int $receitaId, array $payload): ?array
    {
        foreach (self::$receitas as $idx => $receita) {
            if ((int) $receita['id'] !== $receitaId || (int) $receita['id_usuarios'] !== $userId) {
                continue;
            }

            $updated = array_merge($receita, $payload);
            $updated['alterado_em'] = now()->toISOString();

            if (isset($updated['id_categorias'])) {
                $updated['id_categorias'] = (int) $updated['id_categorias'];
            }
            if (isset($updated['tempo_preparo_minutos'])) {
                $updated['tempo_preparo_minutos'] = (int) $updated['tempo_preparo_minutos'];
            }
            if (isset($updated['porcoes'])) {
                $updated['porcoes'] = (int) $updated['porcoes'];
            }

            self::$receitas[$idx] = $updated;

            return self::withCategoria($updated);
        }

        return null;
    }

    public static function deleteReceitaForUser(int $userId, int $receitaId): bool
    {
        foreach (self::$receitas as $idx => $receita) {
            if ((int) $receita['id'] === $receitaId && (int) $receita['id_usuarios'] === $userId) {
                array_splice(self::$receitas, $idx, 1);

                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $receita
     * @return array<string, mixed>
     */
    private static function withCategoria(array $receita): array
    {
        $categoria = null;

        foreach (self::$categorias as $c) {
            if ((int) $c['id'] === (int) $receita['id_categorias']) {
                $categoria = ['id' => (int) $c['id'], 'nome' => (string) $c['nome']];
                break;
            }
        }

        $receita['categoria'] = $categoria;

        return $receita;
    }

    /**
     * @return mixed
     */
    private static function loadJsonFixture(string $file)
    {
        $path = base_path('tests/fixtures/'.$file);

        if (! is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if ($content === false) {
            return null;
        }

        $decoded = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $decoded;
    }
}
