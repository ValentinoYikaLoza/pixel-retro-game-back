<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Repositories\Contracts\StreakRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Vista y acciones de la racha (estilo Duolingo): resumen con calendario, metas
 * mensuales (reclamables, se reinician por mes UTC), hitos consecutivos y compra
 * de congeladores. El check-in diario en sí lo hace UserService::checkInDaily.
 */
class StreakService
{
    /** Costo en monedas de un congelador y tope que puede acumular el usuario. */
    private const FREEZE_COST = 150;
    private const MAX_FREEZES = 3;

    public function __construct(
        private readonly StreakRepositoryInterface $streak,
        private readonly UserService $users,
    ) {}

    /**
     * Resumen de la racha para un mes (UTC; por defecto el actual): racha,
     * congeladores, días del mes con check-in (calendario), metas mensuales con
     * su progreso/estado e hitos consecutivos.
     *
     * @return array<string, mixed>
     */
    public function overview(int $userId, ?string $month = null): array
    {
        $user = $this->users->getById($userId);

        $monthDate = $month
            ? Carbon::createFromFormat('Y-m', $month, 'UTC')->startOfMonth()
            : now('UTC')->startOfMonth();
        $start = $monthDate->copy()->startOfMonth()->toDateString();
        $end = $monthDate->copy()->endOfMonth()->toDateString();
        $periodKey = $monthDate->format('Y-m');

        $dates = $this->streak->checkInDatesBetween($userId, $start, $end);
        $checkedInDays = array_map(fn ($d) => (int) Carbon::parse($d)->day, $dates);
        $monthCount = count($dates);

        $claims = $this->streak->goalClaimsForPeriod($userId, $periodKey);
        $goals = $this->streak->allMonthlyGoals()->map(function ($g) use ($claims, $monthCount) {
            $claim = $claims->get($g->id);
            $claimed = $claim && $claim->claimed_at !== null;

            return [
                'id' => (int) $g->id,
                'days_required' => (int) $g->days_required,
                'reward_coins' => (int) $g->reward_coins,
                'reward_freezes' => (int) $g->reward_freezes,
                'reward_exp' => (int) $g->reward_exp,
                'progress' => $monthCount,
                'claimed' => $claimed,
                'claimable' => !$claimed && $monthCount >= (int) $g->days_required,
            ];
        })->all();

        $milestones = [];
        foreach (UserService::STREAK_MILESTONES as $threshold => $reward) {
            $milestones[] = [
                'days' => $threshold,
                'reward_coins' => $reward,
                'reached' => (int) $user->streak >= $threshold,
            ];
        }

        return [
            'streak' => (int) $user->streak,
            'freezes' => (int) $user->streak_freezes,
            'last_streak_date' => $user->last_streak_date?->toDateString(),
            'freeze_cost' => self::FREEZE_COST,
            'max_freezes' => self::MAX_FREEZES,
            'month' => $periodKey,
            'days_in_month' => (int) $monthDate->daysInMonth,
            'checked_in_days' => $checkedInDays,
            'month_count' => $monthCount,
            'goals' => $goals,
            'milestones' => $milestones,
        ];
    }

    /**
     * Reclama una meta mensual: valida progreso y que no esté reclamada este
     * mes, marca el reclamo y otorga su recompensa. Devuelve el resumen.
     *
     * @return array<string, mixed>
     */
    public function claimMonthlyGoal(int $userId, int $goalId): array
    {
        DB::transaction(function () use ($userId, $goalId) {
            $goal = $this->streak->findMonthlyGoal($goalId);
            if (!$goal) {
                throw ApiException::notFound('Meta no encontrada');
            }

            $now = now('UTC');
            $periodKey = $now->format('Y-m');
            $start = $now->copy()->startOfMonth()->toDateString();
            $end = $now->copy()->endOfMonth()->toDateString();

            $monthCount = $this->streak->countCheckInDaysBetween($userId, $start, $end);
            if ($monthCount < (int) $goal->days_required) {
                throw ApiException::unprocessable('Aún no alcanzas esta meta');
            }

            $claim = $this->streak->findOrNewGoalClaim($userId, $goalId, $periodKey);
            if ($claim->claimed_at !== null) {
                throw ApiException::unprocessable('Ya reclamaste esta meta este mes');
            }
            $claim->claimed_at = $now;
            $this->streak->saveGoalClaim($claim);

            if ((int) $goal->reward_coins > 0) {
                $this->users->addCoins($userId, (int) $goal->reward_coins);
            }
            if ((int) $goal->reward_exp > 0) {
                $this->users->addExp($userId, (int) $goal->reward_exp);
            }
            if ((int) $goal->reward_freezes > 0) {
                $this->users->addFreezes($userId, (int) $goal->reward_freezes, self::MAX_FREEZES);
            }
        });

        return $this->overview($userId);
    }

    /**
     * Compra un congelador con monedas. Devuelve el resumen actualizado.
     *
     * @return array<string, mixed>
     */
    public function buyFreeze(int $userId): array
    {
        $this->users->purchaseFreeze($userId, self::FREEZE_COST, self::MAX_FREEZES);

        return $this->overview($userId);
    }
}
