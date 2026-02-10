<?php

namespace App\Services\Categorias;

use App\Contracts\CategoriaService;
use App\Models\Categoria;

class EloquentCategoriaService implements CategoriaService
{
    public function listAll(): array
    {
        return Categoria::query()
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->map(fn (Categoria $categoria) => [
                'id' => (int) $categoria->id,
                'nome' => (string) $categoria->nome,
            ])
            ->values()
            ->all();
    }
}
