<?php

namespace App\Http\Controllers;

use App\Models\Receita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceitaController extends Controller
{
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

    public function show(Request $request, Receita $receita): JsonResponse
    {
        $this->authorizeReceita($request, $receita);
        $receita->load('categoria:id,nome');

        return response()->json($receita);
    }

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
