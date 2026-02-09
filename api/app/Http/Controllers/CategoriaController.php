<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categorias",
     *     tags={"Categorias"},
     *     summary="Listar categorias",
     *     description="Retorna todas as categorias para uso em formulários de receita.",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Lista de categorias")
     * )
     */
    public function index(): JsonResponse
    {
        $categorias = Categoria::orderBy('nome')->get(['id', 'nome']);

        return response()->json($categorias);
    }
}
