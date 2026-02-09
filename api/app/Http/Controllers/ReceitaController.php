<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceitaController extends Controller
{
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
        $query = Receita::query()
            ->where('id_usuarios', $request->user()->id)
            ->with('categoria:id,nome');

        if ($request->filled('q')) {
            $termo = $request->input('q');
            $query->where(function ($q) use ($termo) {
                $q->where('nome', 'like', "%{$termo}%")
                    ->orWhere('ingredientes', 'like', "%{$termo}%")
                    ->orWhere('modo_preparo', 'like', "%{$termo}%");
            });
        }

        if ($request->filled('categoria_id')) {
            $query->where('id_categorias', $request->input('categoria_id'));
        }

        $receitas = $query->orderByDesc('criado_em')->paginate(15);

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
            'id_categorias' => ['required', 'integer', 'exists:categorias,id'],
            'nome' => ['required', 'string', 'max:45'],
            'tempo_preparo_minutos' => ['required', 'integer', 'min:1'],
            'porcoes' => ['required', 'integer', 'min:1'],
            'modo_preparo' => ['required', 'string'],
            'ingredientes' => ['required', 'string'],
        ]);

        $receita = Receita::create([
            ...$validated,
            'id_usuarios' => $request->user()->id,
        ]);

        $receita->load('categoria:id,nome');

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
    public function show(Request $request, Receita $receita): JsonResponse
    {
        $this->authorizeReceita($request, $receita);
        $receita->load('categoria:id,nome');

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
    public function update(Request $request, Receita $receita): JsonResponse
    {
        $this->authorizeReceita($request, $receita);

        $validated = $request->validate([
            'id_categorias' => ['sometimes', 'integer', 'exists:categorias,id'],
            'nome' => ['sometimes', 'string', 'max:45'],
            'tempo_preparo_minutos' => ['sometimes', 'integer', 'min:1'],
            'porcoes' => ['sometimes', 'integer', 'min:1'],
            'modo_preparo' => ['sometimes', 'string'],
            'ingredientes' => ['sometimes', 'string'],
        ]);

        $receita->update($validated);
        $receita->load('categoria:id,nome');

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
    public function destroy(Request $request, Receita $receita): JsonResponse
    {
        $this->authorizeReceita($request, $receita);
        $receita->delete();

        return response()->json(['message' => 'Receita excluída com sucesso.']);
    }

    private function authorizeReceita(Request $request, Receita $receita): void
    {
        if ($receita->id_usuarios !== (int) $request->user()->id) {
            abort(404);
        }
    }
}
