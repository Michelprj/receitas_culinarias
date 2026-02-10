<?php

namespace App\Services\Receitas;

use App\Contracts\ReceitaService;
use App\Support\OfflineJsonStore;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class FakeReceitaService implements ReceitaService
{
    public function listForUser(int $userId, ?string $q, ?int $categoriaId): LengthAwarePaginator|array
    {
        return OfflineJsonStore::listReceitas($userId, $q, $categoriaId);
    }

    public function createForUser(int $userId, array $data): array
    {
        return OfflineJsonStore::createReceita($userId, $data);
    }

    public function findForUser(int $userId, int $receitaId): ?array
    {
        return OfflineJsonStore::findReceitaForUser($userId, $receitaId);
    }

    public function updateForUser(int $userId, int $receitaId, array $data): ?array
    {
        return OfflineJsonStore::updateReceitaForUser($userId, $receitaId, $data);
    }

    public function deleteForUser(int $userId, int $receitaId): bool
    {
        return OfflineJsonStore::deleteReceitaForUser($userId, $receitaId);
    }

    public function assertCategoriaExists(int $categoriaId): void
    {
        if (! OfflineJsonStore::categoriaExists($categoriaId)) {
            throw ValidationException::withMessages([
                'id_categorias' => ['O id_categorias selecionado é inválido.'],
            ]);
        }
    }
}
