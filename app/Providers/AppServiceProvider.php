<?php

namespace App\Providers;

use App\Repositories\Contracts\DivisionRepositoryInterface;
use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\GameSessionRepositoryInterface;
use App\Repositories\Contracts\MissionRepositoryInterface;
use App\Repositories\Contracts\ShopRepositoryInterface;
use App\Repositories\Contracts\UserGameStatRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentDivisionRepository;
use App\Repositories\Eloquent\EloquentGameRepository;
use App\Repositories\Eloquent\EloquentGameSessionRepository;
use App\Repositories\Eloquent\EloquentMissionRepository;
use App\Repositories\Eloquent\EloquentShopRepository;
use App\Repositories\Eloquent\EloquentUserGameStatRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bindings interfaz → implementación (Dependency Inversion).
     * Services y Controllers se autoresuelven por el contenedor.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        UserRepositoryInterface::class => EloquentUserRepository::class,
        DivisionRepositoryInterface::class => EloquentDivisionRepository::class,
        MissionRepositoryInterface::class => EloquentMissionRepository::class,
        GameRepositoryInterface::class => EloquentGameRepository::class,
        ShopRepositoryInterface::class => EloquentShopRepository::class,
        GameSessionRepositoryInterface::class => EloquentGameSessionRepository::class,
        UserGameStatRepositoryInterface::class => EloquentUserGameStatRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
