<?php

namespace App\Contracts;

interface CategoriaService
{
    /**
     * @return array<int, array{id:int,nome:string}>
     */
    public function listAll(): array;
}
