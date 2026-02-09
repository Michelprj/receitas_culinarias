<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Auth"},
     *     summary="Cadastro de usuário",
     *     description="Cria um novo usuário e retorna o token de acesso.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome","login","senha","senha_confirmation"},
     *             @OA\Property(property="nome", type="string", example="Maria Silva"),
     *             @OA\Property(property="login", type="string", example="maria"),
     *             @OA\Property(property="senha", type="string", format="password", example="senha123"),
     *             @OA\Property(property="senha_confirmation", type="string", format="password", example="senha123")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Usuário criado"),
     *     @OA\Response(response=400, description="Erro de validação")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"Auth"},
     *     summary="Login",
     *     description="Autentica e retorna o token Bearer.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"login","senha"},
     *             @OA\Property(property="login", type="string", example="maria"),
     *             @OA\Property(property="senha", type="string", format="password", example="senha123")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login realizado"),
     *     @OA\Response(response=400, description="Credenciais inválidas")
     * )
     */
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

    /**
     * @OA\Post(
     *     path="/logout",
     *     tags={"Auth"},
     *     summary="Logoff",
     *     description="Invalida o token do usuário autenticado.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Logout realizado")
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    /**
     * @OA\Get(
     *     path="/user",
     *     tags={"Auth"},
     *     summary="Usuário autenticado",
     *     description="Retorna os dados do usuário logado.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Dados do usuário")
     * )
     */
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
