<?php

namespace App\Services\Auth;

use App\Contracts\AuthService;
use App\Support\OfflineJsonStore;
use Illuminate\Validation\ValidationException;

class FakeAuthService implements AuthService
{
    public function register(array $data): array
    {
        if (OfflineJsonStore::hasLogin($data['login'])) {
            throw ValidationException::withMessages([
                'login' => ['Usuário já existe'],
            ]);
        }

        $user = OfflineJsonStore::createUser($data);
        $token = OfflineJsonStore::createToken((int) $user['id']);

        return [
            'usuario' => [
                'id' => (int) $user['id'],
                'nome' => (string) $user['nome'],
                'login' => (string) $user['login'],
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function login(string $login, string $senha): array
    {
        $user = OfflineJsonStore::findUserByLogin($login);

        if (! $user || ! OfflineJsonStore::checkUserPassword($user, $senha)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        $token = OfflineJsonStore::createToken((int) $user['id']);

        return [
            'usuario' => [
                'id' => (int) $user['id'],
                'nome' => (string) $user['nome'],
                'login' => (string) $user['login'],
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
