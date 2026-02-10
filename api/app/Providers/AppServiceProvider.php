<?php

namespace App\Providers;

use App\Contracts\AuthService;
use App\Contracts\CategoriaService;
use App\Contracts\ReceitaService;
use App\Services\Auth\EloquentAuthService;
use App\Services\Categorias\EloquentCategoriaService;
use App\Services\Receitas\EloquentReceitaService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthService::class, EloquentAuthService::class);
        $this->app->bind(CategoriaService::class, EloquentCategoriaService::class);
        $this->app->bind(ReceitaService::class, EloquentReceitaService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
