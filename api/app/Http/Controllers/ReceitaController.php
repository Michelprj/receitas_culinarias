<?php

namespace App\Http\Controllers;

use App\Contracts\ReceitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReceitaController extends Controller
{
    public function __construct(private readonly ReceitaService $receitaService)
    {
    }

    /**
     * @OA\Get(
     *     path="/receitas",
     *     tags={"Receitas"},
     *     summary="Listar e pesquisar receitas",
     *     description="Lista receitas do usuário com paginação. Filtros: q (busca em nome/ingredientes/modo_preparo), categoria_id.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="q", in="query", required=false, @OA\Schema(type="string"), description="Termo de busca"),
     *     @OA\Parameter(name="categoria_id", in="query", required=false, @OA\Schema(type="integer"), description="ID da categoria"),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer"), description="Página"),
     *     @OA\Response(response=200, description="Lista paginada de receitas")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $receitas = $this->receitaService->listForUser(
            (int) $request->user()->id,
            $request->filled('q') ? (string) $request->input('q') : null,
            $request->filled('categoria_id') ? (int) $request->input('categoria_id') : null
        );

        return response()->json($receitas);
    }

    /**
     * @OA\Post(
     *     path="/receitas",
     *     tags={"Receitas"},
     *     summary="Cadastrar receita",
     *     description="Cria uma nova receita para o usuário autenticado.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_categorias","nome","tempo_preparo_minutos","porcoes","modo_preparo","ingredientes"},
     *             @OA\Property(property="id_categorias", type="integer", example=1),
     *             @OA\Property(property="nome", type="string", maxLength=45, example="Bolo de chocolate"),
     *             @OA\Property(property="tempo_preparo_minutos", type="integer", example=45),
     *             @OA\Property(property="porcoes", type="integer", example=8),
     *             @OA\Property(property="modo_preparo", type="string", example="Misture os ingredientes..."),
     *             @OA\Property(property="ingredientes", type="string", example="Farinha, ovos, chocolate...")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Receita criada"),
     *     @OA\Response(response=400, description="Erro de validação")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id_categorias' => ['required', 'integer'],
            'nome' => ['required', 'string', 'max:45'],
            'tempo_preparo_minutos' => ['required', 'integer', 'min:1'],
            'porcoes' => ['required', 'integer', 'min:1'],
            'modo_preparo' => ['required', 'string'],
            'ingredientes' => ['required', 'string'],
            'foto' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,bmp', 'max:2048'],
        ]);

        $this->receitaService->assertCategoriaExists((int) $validated['id_categorias']);

        unset($validated['foto']);
        $receita = $this->receitaService->createForUser(
            (int) $request->user()->id,
            $validated
        );

        if ($request->hasFile('foto')) {
            Storage::disk('public')->makeDirectory('receitas');
            $path = $request->file('foto')->store('receitas', 'public');
            $updated = $this->receitaService->updateForUser(
                (int) $request->user()->id,
                (int) $receita['id'],
                ['foto' => $path]
            );
            if ($updated) {
                $receita = $updated;
            }
        }

        return response()->json($receita, 201);
    }

    /**
     * @OA\Get(
     *     path="/receitas/{id}",
     *     tags={"Receitas"},
     *     summary="Exibir receita",
     *     description="Retorna uma receita do usuário. Usado para visualização e impressão.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Receita"),
     *     @OA\Response(response=404, description="Receita não encontrada")
     * )
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $receita = $this->receitaService->findForUser((int) $request->user()->id, $id);
        if (! $receita) {
            abort(404);
        }

        return response()->json($receita);
    }

    /**
     * @OA\Put(
     *     path="/receitas/{id}",
     *     tags={"Receitas"},
     *     summary="Editar receita",
     *     description="Atualiza uma receita do usuário. Todos os campos são opcionais.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="id_categorias", type="integer"),
     *             @OA\Property(property="nome", type="string", maxLength=45),
     *             @OA\Property(property="tempo_preparo_minutos", type="integer"),
     *             @OA\Property(property="porcoes", type="integer"),
     *             @OA\Property(property="modo_preparo", type="string"),
     *             @OA\Property(property="ingredientes", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Receita atualizada"),
     *     @OA\Response(response=400, description="Erro de validação"),
     *     @OA\Response(response=404, description="Receita não encontrada")
     * )
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'id_categorias' => ['sometimes', 'integer'],
            'nome' => ['sometimes', 'string', 'max:45'],
            'tempo_preparo_minutos' => ['sometimes', 'integer', 'min:1'],
            'porcoes' => ['sometimes', 'integer', 'min:1'],
            'modo_preparo' => ['sometimes', 'string'],
            'ingredientes' => ['sometimes', 'string'],
            'foto' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp,bmp', 'max:2048'],
        ],
        [
            'nome.max' => 'O nome da receita deve ter no máximo 45 caracteres.',
            'tempo_preparo_minutos.min' => 'O tempo de preparo deve ser maior que 0.',
            'porcoes.min' => 'O número de porções deve ser maior que 0.',
            'foto.image' => 'A foto deve ser uma imagem.',
            'foto.max' => 'A foto deve ter no máximo 2 MB.',
        ]
    );

        if (array_key_exists('id_categorias', $validated)) {
            $this->receitaService->assertCategoriaExists((int) $validated['id_categorias']);
        }

        unset($validated['foto']);
        if ($request->hasFile('foto')) {
            Storage::disk('public')->makeDirectory('receitas');
            $current = $this->receitaService->findForUser((int) $request->user()->id, $id);
            if ($current && ! empty($current['foto'])) {
                Storage::disk('public')->delete($current['foto']);
            }
            $validated['foto'] = $request->file('foto')->store('receitas', 'public');
        }

        $receita = $this->receitaService->updateForUser(
            (int) $request->user()->id,
            $id,
            $validated
        );

        if (! $receita) {
            abort(404);
        }

        return response()->json($receita);
    }

    /**
     * @OA\Delete(
     *     path="/receitas/{id}",
     *     tags={"Receitas"},
     *     summary="Excluir receita",
     *     description="Exclui uma receita do usuário.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Receita excluída"),
     *     @OA\Response(response=404, description="Receita não encontrada")
     * )
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $deleted = $this->receitaService->deleteForUser((int) $request->user()->id, $id);
        if (! $deleted) {
            abort(404);
        }

        return response()->json(['message' => 'Receita excluída com sucesso.']);
    }
}
