<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *     title="Receitas Culinárias API",
 *     version="1.0.0",
 *     description="API de cadastro e gestão de receitas."
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="API local"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="sanctum",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Token obtido em POST /login ou POST /register. Use: Bearer {token}"
 * )
 * @OA\Tag(name="Auth", description="Cadastro, login e logoff")
 * @OA\Tag(name="Categorias", description="Listagem de categorias")
 * @OA\Tag(name="Receitas", description="CRUD de receitas do usuário")
 */
class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;
}
