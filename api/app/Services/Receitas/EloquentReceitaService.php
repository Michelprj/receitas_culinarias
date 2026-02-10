<?php

namespace App\Services\Receitas;

use App\Contracts\ReceitaService;
use App\Models\Categoria;
use App\Models\Receita;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class EloquentReceitaService implements ReceitaService
{
    public function listForUser(int $userId, ?string $q, ?int $categoriaId): LengthAwarePaginator|array
    {
        $query = Receita::query()
            ->where('id_usuarios', $userId)
            ->with('categoria:id,nome');

        if ($q !== null && $q !== '') {
            $query->where(function ($innerQuery) use ($q) {
                $innerQuery->where('nome', 'like', "%{$q}%")
                    ->orWhere('ingredientes', 'like', "%{$q}%")
                    ->orWhere('modo_preparo', 'like', "%{$q}%");
            });
        }

        if ($categoriaId !== null) {
            $query->where('id_categorias', $categoriaId);
        }

        return $query->orderByDesc('criado_em')->paginate(15);
    }

    public function createForUser(int $userId, array $data): array
    {
        $receita = Receita::create([
            ...$data,
            'id_usuarios' => $userId,
        ]);

        $receita->load('categoria:id,nome');

        return $receita->toArray();
    }

    public function findForUser(int $userId, int $receitaId): ?array
    {
        $receita = Receita::query()
            ->where('id', $receitaId)
            ->where('id_usuarios', $userId)
            ->with('categoria:id,nome')
            ->first();

        return $receita?->toArray();
    }

    public function updateForUser(int $userId, int $receitaId, array $data): ?array
    {
        $receita = Receita::query()
            ->where('id', $receitaId)
            ->where('id_usuarios', $userId)
            ->first();

        if (! $receita) {
            return null;
        }

        $receita->update($data);
        $receita->load('categoria:id,nome');

        return $receita->toArray();
    }

    public function deleteForUser(int $userId, int $receitaId): bool
    {
        $receita = Receita::query()
            ->where('id', $receitaId)
            ->where('id_usuarios', $userId)
            ->first();

        if (! $receita) {
            return false;
        }

        $receita->delete();

        return true;
    }

    public function assertCategoriaExists(int $categoriaId): void
    {
        if (! Categoria::query()->whereKey($categoriaId)->exists()) {
            throw ValidationException::withMessages([
                'id_categorias' => ['O id_categorias selecionado é inválido.'],
            ]);
        }
    }
}
