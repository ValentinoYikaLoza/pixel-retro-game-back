<?php

namespace App\Repositories\Contracts;

use App\Models\UserGameStatModel;
use Illuminate\Support\Collection;

interface UserGameStatRepositoryInterface
{
    /** Agregado del usuario para un juego, o una instancia nueva sin persistir. */
    public function firstOrNew(int $userId, int $gameId): UserGameStatModel;

    /** Agregado existente del usuario para un juego (null si nunca jugó). */
    public function find(int $userId, int $gameId): ?UserGameStatModel;

    public function save(UserGameStatModel $stat): void;

    /**
     * Top por high_score de un juego, asegurando que el usuario aparezca.
     * Cada fila: { user_id, name, high_score, total_games, flag }.
     *
     * @return Collection<int, object>
     */
    public function leaderboard(int $gameId, int $ensureUserId): Collection;
}
