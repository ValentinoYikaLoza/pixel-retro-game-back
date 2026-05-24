<?php

namespace App\Repositories\Contracts;

use App\Models\StreakMonthlyGoalModel;
use App\Models\UserStreakMonthlyGoalModel;
use Illuminate\Support\Collection;

interface StreakRepositoryInterface
{
    /** Registra el check-in del día (idempotente por user_id + fecha). */
    public function recordCheckIn(int $userId, string $date): void;

    /**
     * Fechas ('Y-m-d') con check-in en el rango [start, end] (inclusive).
     *
     * @return array<int, string>
     */
    public function checkInDatesBetween(int $userId, string $start, string $end): array;

    /** Cantidad de días distintos con check-in en el rango [start, end]. */
    public function countCheckInDaysBetween(int $userId, string $start, string $end): int;

    /** @return Collection<int, StreakMonthlyGoalModel> */
    public function allMonthlyGoals(): Collection;

    public function findMonthlyGoal(int $goalId): ?StreakMonthlyGoalModel;

    /**
     * Reclamos del usuario para un período, indexados por streak_monthly_goal_id.
     *
     * @return Collection<int, UserStreakMonthlyGoalModel>
     */
    public function goalClaimsForPeriod(int $userId, string $periodKey): Collection;

    public function findOrNewGoalClaim(int $userId, int $goalId, string $periodKey): UserStreakMonthlyGoalModel;

    public function saveGoalClaim(UserStreakMonthlyGoalModel $claim): void;
}
