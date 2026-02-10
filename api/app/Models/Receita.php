<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receita extends Model
{
    use HasFactory;
    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'alterado_em';

    protected $fillable = [
        'id_usuarios',
        'id_categorias',
        'nome',
        'tempo_preparo_minutos',
        'porcoes',
        'modo_preparo',
        'ingredientes',
    ];

    protected $casts = [
        'criado_em' => 'datetime',
        'alterado_em' => 'datetime',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuarios');
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class, 'id_categorias');
    }
}
