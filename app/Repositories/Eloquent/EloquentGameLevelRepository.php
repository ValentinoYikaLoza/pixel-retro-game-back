<?php

namespace App\Repositories\Eloquent;

use App\Models\GameLevelModel;
use App\Models\UserGameLevelModel;
use App\Repositories\Contracts\GameLevelRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentGameLevelRepository implements GameLevelRepositoryInterface
{
    public function forGame(int $gameId): Collection
    {
        return GameLevelModel::query()
            ->where('game_id', $gameId)
            ->orderBy('level')
            ->get();
    }

    public function find(int $gameId, int $level): ?GameLevelModel
    {
        return GameLevelModel::query()
            ->where('game_id', $gameId)
            ->where('level', $level)
            ->first();
    }

    public function progressForUser(int $userId, int $gameId): Collection
    {
        return UserGameLevelModel::query()
            ->join('game_level', 'user_game_level.game_level_id', '=', 'game_level.id')
            ->where('game_level.game_id', $gameId)
            ->where('user_game_level.user_id', $userId)
            ->select('user_game_level.*')
            ->get();
    }

    public function firstOrNewProgress(int $userId, int $gameLevelId): UserGameLevelModel
    {
        return UserGameLevelModel::firstOrNew([
            'user_id' => $userId,
            'game_level_id' => $gameLevelId,
        ]);
    }

    public function saveProgress(UserGameLevelModel $progress): void
    {
        $progress->save();
    }
}
