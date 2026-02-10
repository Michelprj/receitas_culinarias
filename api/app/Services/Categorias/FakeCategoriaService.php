<?php

namespace App\Services\Categorias;

use App\Contracts\CategoriaService;
use App\Support\OfflineJsonStore;

class FakeCategoriaService implements CategoriaService
{
    public function listAll(): array
    {
        return OfflineJsonStore::categorias();
    }
}
