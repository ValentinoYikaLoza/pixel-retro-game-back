<?php

namespace App\Repositories\Eloquent;

use App\Models\GameSessionModel;
use App\Repositories\Contracts\GameSessionRepositoryInterface;

class EloquentGameSessionRepository implements GameSessionRepositoryInterface
{
    public function create(array $attributes): GameSessionModel
    {
        return GameSessionModel::create($attributes);
    }

    public function findForUser(int $sessionId, int $userId): ?GameSessionModel
    {
        return GameSessionModel::query()
            ->where('id', $sessionId)
            ->where('user_id', $userId)
            ->first();
    }

    public function save(GameSessionModel $session): void
    {
        $session->save();
    }
}
