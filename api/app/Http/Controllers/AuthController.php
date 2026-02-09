<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate(
            [
                'nome' => ['required', 'string', 'max:100'],
                'login' => ['required', 'string', 'max:100', 'unique:usuarios,login'],
                'senha' => ['required', 'string', 'min:6', 'confirmed'],
            ],
            [
                'login.unique' => 'Usuário já existe',
            ]
        );

        $user = User::create([
            'nome' => $validated['nome'],
            'login' => $validated['login'],
            'senha' => $validated['senha'],
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'usuario' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'login' => $user->login,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'login' => ['required', 'string'],
            'senha' => ['required', 'string'],
        ]);

        if (! Auth::attempt([
            'login' => $request->input('login'),
            'password' => $request->input('senha'),
        ])) {
            throw ValidationException::withMessages([
                'login' => [__('auth.failed')],
            ]);
        }

        $user = Auth::user();

        /** @var User $user */
        $user->tokens()->delete();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'usuario' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'login' => $user->login,
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function user(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'nome' => $user->nome,
            'login' => $user->login,
        ]);
    }
}
