<?php

namespace App\Repositories\Eloquent;

use App\Models\StreakMonthlyGoalModel;
use App\Models\UserCheckInModel;
use App\Models\UserStreakMonthlyGoalModel;
use App\Repositories\Contracts\StreakRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EloquentStreakRepository implements StreakRepositoryInterface
{
    public function recordCheckIn(int $userId, string $date): void
    {
        // insertOrIgnore respeta el unique(user_id, checked_in_on): un registro
        // por día aunque getUser se llame varias veces.
        DB::table('user_check_in')->insertOrIgnore([
            'user_id' => $userId,
            'checked_in_on' => $date,
        ]);
    }

    public function checkInDatesBetween(int $userId, string $start, string $end): array
    {
        return UserCheckInModel::query()
            ->where('user_id', $userId)
            ->whereBetween('checked_in_on', [$start, $end])
            ->orderBy('checked_in_on')
            ->pluck('checked_in_on')
            ->map(fn ($d) => $d instanceof \Carbon\Carbon ? $d->toDateString() : (string) $d)
            ->all();
    }

    public function countCheckInDaysBetween(int $userId, string $start, string $end): int
    {
        return UserCheckInModel::query()
            ->where('user_id', $userId)
            ->whereBetween('checked_in_on', [$start, $end])
            ->count();
    }

    public function allMonthlyGoals(): Collection
    {
        return StreakMonthlyGoalModel::query()->orderBy('days_required')->get();
    }

    public function findMonthlyGoal(int $goalId): ?StreakMonthlyGoalModel
    {
        return StreakMonthlyGoalModel::find($goalId);
    }

    public function goalClaimsForPeriod(int $userId, string $periodKey): Collection
    {
        return UserStreakMonthlyGoalModel::query()
            ->where('user_id', $userId)
            ->where('period_key', $periodKey)
            ->get()
            ->keyBy('streak_monthly_goal_id');
    }

    public function findOrNewGoalClaim(int $userId, int $goalId, string $periodKey): UserStreakMonthlyGoalModel
    {
        return UserStreakMonthlyGoalModel::firstOrNew([
            'user_id' => $userId,
            'streak_monthly_goal_id' => $goalId,
            'period_key' => $periodKey,
        ]);
    }

    public function saveGoalClaim(UserStreakMonthlyGoalModel $claim): void
    {
        $claim->save();
    }
}
