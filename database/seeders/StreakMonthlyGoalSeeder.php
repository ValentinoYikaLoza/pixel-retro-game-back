<?php

namespace Database\Seeders;

use App\Models\StreakMonthlyGoalModel;
use Illuminate\Database\Seeder;

/**
 * Metas mensuales de racha: entrar N días dentro del mes otorga la recompensa.
 * Se reinician cada mes (vía period_key en user_streak_monthly_goal).
 */
class StreakMonthlyGoalSeeder extends Seeder
{
    public function run(): void
    {
        // [days_required, coins, freezes, exp]
        $defs = [
            [7, 30, 0, 0],
            [14, 0, 1, 0],
            [21, 100, 0, 0],
            [28, 150, 1, 0],
        ];

        foreach ($defs as [$days, $coins, $freezes, $exp]) {
            StreakMonthlyGoalModel::updateOrCreate(
                ['days_required' => $days],
                ['reward_coins' => $coins, 'reward_freezes' => $freezes, 'reward_exp' => $exp],
            );
        }
    }
}
