<?php

namespace App\Services\Auth;

use App\Contracts\AuthService;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class EloquentAuthService implements AuthService
{
    public function register(array $data): array
    {
        if (User::query()->where('login', $data['login'])->exists()) {
            throw ValidationException::withMessages([
                'login' => ['Usuário já existe'],
            ]);
        }

        $user = User::create([
            'nome' => $data['nome'],
            'login' => $data['login'],
            'senha' => $data['senha'],
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return [
            'usuario' => [
                'id' => (int) $user->id,
                'nome' => (string) $user->nome,
                'login' => (string) $user->login,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }

    public function login(string $login, string $senha): array
    {
        $user = User::query()->where('login', $login)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'login' => ['Usuário não encontrado'],
            ]);
        }

        if (! $user || ! Hash::check($senha, (string) $user->senha)) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return [
            'usuario' => [
                'id' => (int) $user->id,
                'nome' => (string) $user->nome,
                'login' => (string) $user->login,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ];
    }
}
