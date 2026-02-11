<?php

namespace App\Http\Controllers;

use App\Contracts\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

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
                'login' => ['required', 'string', 'max:100'],
                'senha' => ['required', 'string', 'min:6', 'confirmed'],
            ],
            [
                'nome.required' => 'O nome é obrigatório.',
                'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
                'login.required' => 'O e-mail ou login é obrigatório.',
                'login.max' => 'O login deve ter no máximo 100 caracteres.',
                'senha.required' => 'A senha é obrigatória.',
                'senha.min' => 'A senha deve ter pelo menos 6 caracteres.',
                'senha.confirmed' => 'A confirmação de senha não confere.',
            ]
        );

        return response()->json($this->authService->register($validated), 201);
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
        $request->validate(
            [
                'login' => ['required', 'string'],
                'senha' => ['required', 'string'],
            ],
            [
                'login.required' => 'O e-mail ou login é obrigatório.',
                'senha.required' => 'A senha é obrigatória.',
            ]
        );

        return response()->json(
            $this->authService->login(
                (string) $request->input('login'),
                (string) $request->input('senha')
            )
        );
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
        $token = $request->user()?->currentAccessToken();
        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }

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
