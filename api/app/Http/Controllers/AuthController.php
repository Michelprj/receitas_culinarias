<?php

namespace App\Http\Controllers;

use App\Contracts\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

    /**
     * @OA\Patch(
     *     path="/user",
     *     tags={"Auth"},
     *     summary="Atualizar perfil",
     *     description="Atualiza nome, login e/ou senha do usuário autenticado.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="nome", type="string", example="Maria Silva"),
     *             @OA\Property(property="login", type="string", example="maria"),
     *             @OA\Property(property="senha_atual", type="string", format="password"),
     *             @OA\Property(property="nova_senha", type="string", format="password"),
     *             @OA\Property(property="nova_senha_confirmation", type="string", format="password")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Perfil atualizado"),
     *     @OA\Response(response=400, description="Erro de validação")
     * )
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $rules = [
            'nome' => ['sometimes', 'required', 'string', 'max:100'],
            'login' => ['sometimes', 'required', 'string', 'max:100', 'unique:usuarios,login,'.$user->id],
        ];

        $messages = [
            'nome.required' => 'O nome é obrigatório.',
            'nome.max' => 'O nome deve ter no máximo 100 caracteres.',
            'login.required' => 'O login é obrigatório.',
            'login.max' => 'O login deve ter no máximo 100 caracteres.',
            'login.unique' => 'Este login já está em uso.',
        ];

        if ($request->filled('nova_senha')) {
            $rules['senha_atual'] = ['required', 'string'];
            $rules['nova_senha'] = ['required', 'string', 'min:6', 'confirmed'];
            $messages['senha_atual.required'] = 'A senha atual é obrigatória para alterar a senha.';
            $messages['nova_senha.required'] = 'A nova senha é obrigatória.';
            $messages['nova_senha.min'] = 'A nova senha deve ter pelo menos 6 caracteres.';
            $messages['nova_senha.confirmed'] = 'A confirmação da nova senha não confere.';
        }

        $validated = $request->validate($rules, $messages);

        if (! empty($validated['nova_senha'])) {
            if (! Hash::check((string) $request->input('senha_atual'), (string) $user->senha)) {
                return response()->json([
                    'message' => 'Dados inválidos.',
                    'errors' => ['senha_atual' => ['A senha atual está incorreta.']],
                ], 422);
            }
        }

        if (array_key_exists('nome', $validated)) {
            $user->nome = $validated['nome'];
        }
        if (array_key_exists('login', $validated)) {
            $user->login = $validated['login'];
        }
        if (! empty($validated['nova_senha'] ?? null)) {
            $user->senha = $validated['nova_senha'];
        }

        $user->save();

        return response()->json([
            'id' => $user->id,
            'nome' => $user->nome,
            'login' => $user->login,
        ]);
    }
}
