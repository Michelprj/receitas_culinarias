<?php

namespace App\Http\Controllers;

use App\Contracts\CategoriaService;
use Illuminate\Http\JsonResponse;

class CategoriaController extends Controller
{
    public function __construct(private readonly CategoriaService $categoriaService)
    {
    }

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
        return response()->json($this->categoriaService->listAll());
    }
}
