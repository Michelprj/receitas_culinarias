<?php

namespace App\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReceitaService
{
    public function listForUser(int $userId, ?string $q, ?int $categoriaId): LengthAwarePaginator|array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function createForUser(int $userId, array $data): array;

    /**
     * @return array<string, mixed>|null
     */
    public function findForUser(int $userId, int $receitaId): ?array;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>|null
     */
    public function updateForUser(int $userId, int $receitaId, array $data): ?array;

    public function deleteForUser(int $userId, int $receitaId): bool;

    public function assertCategoriaExists(int $categoriaId): void;
}
