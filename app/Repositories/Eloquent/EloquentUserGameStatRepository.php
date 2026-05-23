<?php

namespace App\Repositories\Eloquent;

use App\Models\UserGameStatModel;
use App\Repositories\Contracts\UserGameStatRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentUserGameStatRepository implements UserGameStatRepositoryInterface
{
    private const TOP_LIMIT = 20;

    public function firstOrNew(int $userId, int $gameId): UserGameStatModel
    {
        return UserGameStatModel::firstOrNew(
            ['user_id' => $userId, 'game_id' => $gameId],
        );
    }

    public function find(int $userId, int $gameId): ?UserGameStatModel
    {
        return UserGameStatModel::query()
            ->where('user_id', $userId)
            ->where('game_id', $gameId)
            ->first();
    }

    public function save(UserGameStatModel $stat): void
    {
        $stat->save();
    }

    public function leaderboard(int $gameId, int $ensureUserId): Collection
    {
        $rows = $this->leaderboardQuery($gameId)
            ->orderBy('ugs.high_score', 'DESC')
            ->limit(self::TOP_LIMIT)
            ->get();

        // Garantiza que el solicitante esté presente aunque no entre en el top.
        if (!$rows->contains('user_id', $ensureUserId)) {
            $current = $this->leaderboardQuery($gameId)
                ->where('ugs.user_id', $ensureUserId)
                ->first();

            if ($current) {
                $rows = $rows->slice(0, self::TOP_LIMIT - 1)->values();
                $rows->push($current);
            }
        }

        return $rows->values();
    }

    private function leaderboardQuery(int $gameId)
    {
        return UserGameStatModel::query()
            ->from('user_game_stat as ugs')
            ->leftJoin('user as u', 'ugs.user_id', '=', 'u.id')
            ->leftJoin('country', 'u.country_id', '=', 'country.id')
            ->where('ugs.game_id', $gameId)
            ->select(
                'ugs.user_id',
                'u.name',
                'ugs.high_score',
                'ugs.total_games',
                'country.flag',
            );
    }
}
