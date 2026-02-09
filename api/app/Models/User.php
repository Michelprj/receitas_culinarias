<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuarios';

    const CREATED_AT = 'criado_em';
    const UPDATED_AT = 'alterado_em';

    public function getAuthPasswordName(): string
    {
        return 'senha';
    }

    protected $rememberTokenName = null;

    protected $fillable = [
        'nome',
        'login',
        'senha',
    ];

    protected $hidden = [
        'senha',
    ];

    protected $casts = [
        'senha' => 'hashed',
        'criado_em' => 'datetime',
        'alterado_em' => 'datetime',
    ];

    public function receitas(): HasMany
    {
        return $this->hasMany(Receita::class, 'id_usuarios');
    }
}
