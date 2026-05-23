<?php

namespace App\Repositories\Contracts;

use App\Models\GameLevelModel;
use App\Models\UserGameLevelModel;
use Illuminate\Support\Collection;

interface GameLevelRepositoryInterface
{
    /**
     * Niveles de un juego ordenados por número de nivel.
     *
     * @return Collection<int, GameLevelModel>
     */
    public function forGame(int $gameId): Collection;

    /** Un nivel concreto de un juego. */
    public function find(int $gameId, int $level): ?GameLevelModel;

    /**
     * Progreso del usuario en los niveles de un juego.
     *
     * @return Collection<int, UserGameLevelModel>
     */
    public function progressForUser(int $userId, int $gameId): Collection;

    /** Progreso de un nivel para el usuario, o una instancia nueva sin persistir. */
    public function firstOrNewProgress(int $userId, int $gameLevelId): UserGameLevelModel;

    public function saveProgress(UserGameLevelModel $progress): void;
}
