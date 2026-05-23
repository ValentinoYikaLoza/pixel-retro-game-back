<?php

namespace App\Repositories\Contracts;

use App\Models\GameSessionModel;

interface GameSessionRepositoryInterface
{
    /** @param array<string, mixed> $attributes */
    public function create(array $attributes): GameSessionModel;

    /** Sesión por id asegurando que pertenezca al usuario. */
    public function findForUser(int $sessionId, int $userId): ?GameSessionModel;

    public function save(GameSessionModel $session): void;
}
