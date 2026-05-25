<?php

namespace App\Repositories\Eloquent;

use App\Models\DailyMissionModel;
use App\Models\MonthlyMissionModel;
use App\Models\StatusModel;
use App\Models\UserDailyMissionModel;
use App\Models\UserMonthlyMissionModel;
use App\Models\UserWeeklyMissionModel;
use App\Models\WeeklyMissionModel;
use App\Repositories\Contracts\MissionRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EloquentMissionRepository implements MissionRepositoryInterface
{
    public function dailyForUser(int $userId): Collection
    {
        return UserDailyMissionModel::query()
            ->leftJoin('daily_mission', 'user_daily_mission.daily_mission_id', '=', 'daily_mission.id')
            ->leftJoin('reward', 'daily_mission.reward_id', '=', 'reward.id')
            ->selectRaw('
                user_daily_mission.id,
                user_daily_mission.current_value,
                daily_mission.description,
                daily_mission.total_value,
                reward.id as reward_id,
                user_daily_mission.status_id
            ')
            ->where('user_daily_mission.user_id', $userId)
            ->where(function ($query) {
                $query
                    ->whereIn('user_daily_mission.status_id', [StatusModel::PENDING, StatusModel::IN_PROGRESS])
                    ->orWhere(function ($q) {
                        $q->where('user_daily_mission.status_id', StatusModel::COMPLETED)
                            ->whereDate('user_daily_mission.completed_at', Carbon::today());
                    });
            })
            ->orderBy('user_daily_mission.id', 'ASC')
            ->get();
    }

    public function weeklyForUser(int $userId): Collection
    {
        return UserWeeklyMissionModel::query()
            ->leftJoin('weekly_mission', 'user_weekly_mission.weekly_mission_id', '=', 'weekly_mission.id')
            ->leftJoin('reward', 'weekly_mission.reward_id', '=', 'reward.id')
            ->selectRaw('
                user_weekly_mission.id,
                user_weekly_mission.current_value,
                weekly_mission.description,
                weekly_mission.total_value,
                reward.id as reward_id,
                user_weekly_mission.status_id
            ')
            ->where('user_weekly_mission.user_id', $userId)
            ->where(function ($query) {
                $query
                    ->whereIn('user_weekly_mission.status_id', [StatusModel::PENDING, StatusModel::IN_PROGRESS])
                    ->orWhere(function ($q) {
                        $q->where('user_weekly_mission.status_id', StatusModel::COMPLETED)
                            ->whereBetween('user_weekly_mission.completed_at', [
                                Carbon::now()->startOfWeek(),
                                Carbon::now()->endOfWeek(),
                            ]);
                    });
            })
            ->orderBy('user_weekly_mission.id', 'ASC')
            ->get();
    }

    public function monthlyForUser(int $userId): Collection
    {
        return UserMonthlyMissionModel::query()
            ->leftJoin('monthly_mission', 'user_monthly_mission.monthly_mission_id', '=', 'monthly_mission.id')
            ->leftJoin('reward', 'monthly_mission.reward_id', '=', 'reward.id')
            ->selectRaw('
                user_monthly_mission.id,
                user_monthly_mission.current_value,
                monthly_mission.description,
                monthly_mission.total_value,
                reward.id as reward_id,
                user_monthly_mission.status_id
            ')
            ->where('user_monthly_mission.user_id', $userId)
            ->where(function ($query) {
                $query
                    ->whereIn('user_monthly_mission.status_id', [StatusModel::PENDING, StatusModel::IN_PROGRESS])
                    ->orWhere(function ($q) {
                        $q->where('user_monthly_mission.status_id', StatusModel::COMPLETED)
                            ->whereBetween('user_monthly_mission.completed_at', [
                                Carbon::now()->startOfMonth(),
                                Carbon::now()->endOfMonth(),
                            ]);
                    });
            })
            ->orderBy('user_monthly_mission.id', 'ASC')
            ->get();
    }

    public function findUserMission(string $type, int $userId, int $missionId): ?Model
    {
        // $missionId es el id de la fila usuario-misión (lo que el cliente recibe
        // como MissionEntity.id), no el id de la definición de la misión.
        return match ($type) {
            'daily' => UserDailyMissionModel::query()
                ->where('user_id', $userId)
                ->where('id', $missionId)
                ->first(),
            'weekly' => UserWeeklyMissionModel::query()
                ->where('user_id', $userId)
                ->where('id', $missionId)
                ->first(),
            'monthly' => UserMonthlyMissionModel::query()
                ->where('user_id', $userId)
                ->where('id', $missionId)
                ->first(),
            default => null,
        };
    }

    public function advanceableForGame(int $userId, int $gameId): Collection
    {
        $active = [StatusModel::PENDING, StatusModel::IN_PROGRESS];

        $daily = UserDailyMissionModel::with('dailyMission')
            ->where('user_id', $userId)
            ->whereIn('status_id', $active)
            ->whereHas('dailyMission', fn ($q) => $q->where('game_id', $gameId))
            ->get()
            ->map(fn ($m) => [
                'model' => $m,
                'mission_type_id' => (int) $m->dailyMission->mission_type_id,
                'total_value' => (int) $m->dailyMission->total_value,
                'reward_id' => (int) $m->dailyMission->reward_id,
            ]);

        $weekly = UserWeeklyMissionModel::with('weeklyMission')
            ->where('user_id', $userId)
            ->whereIn('status_id', $active)
            ->whereHas('weeklyMission', fn ($q) => $q->where('game_id', $gameId))
            ->get()
            ->map(fn ($m) => [
                'model' => $m,
                'mission_type_id' => (int) $m->weeklyMission->mission_type_id,
                'total_value' => (int) $m->weeklyMission->total_value,
                'reward_id' => (int) $m->weeklyMission->reward_id,
            ]);

        $monthly = UserMonthlyMissionModel::with('monthlyMission')
            ->where('user_id', $userId)
            ->whereIn('status_id', $active)
            ->whereHas('monthlyMission', fn ($q) => $q->where('game_id', $gameId))
            ->get()
            ->map(fn ($m) => [
                'model' => $m,
                'mission_type_id' => (int) $m->monthlyMission->mission_type_id,
                'total_value' => (int) $m->monthlyMission->total_value,
                'reward_id' => (int) $m->monthlyMission->reward_id,
            ]);

        return $daily->concat($weekly)->concat($monthly)->values();
    }

    public function rolloverUserMissions(int $userId, array $keys): void
    {
        $this->rolloverType(
            UserDailyMissionModel::class,
            'daily_mission_id',
            DailyMissionModel::class,
            $userId,
            $keys['daily'],
        );
        $this->rolloverType(
            UserWeeklyMissionModel::class,
            'weekly_mission_id',
            WeeklyMissionModel::class,
            $userId,
            $keys['weekly'],
        );
        $this->rolloverType(
            UserMonthlyMissionModel::class,
            'monthly_mission_id',
            MonthlyMissionModel::class,
            $userId,
            $keys['monthly'],
        );
    }

    /**
     * Para un tipo de misión: las filas con período guardado distinto al actual
     * se reasignan (nueva misión base aleatoria) y reinician; las que tienen
     * período nulo (sembradas) solo se inicializan con la clave actual.
     *
     * @param  class-string<Model>  $userModel
     * @param  class-string<Model>  $baseModel
     */
    private function rolloverType(
        string $userModel,
        string $fk,
        string $baseModel,
        int $userId,
        string $currentKey,
    ): void {
        $rows = $userModel::where('user_id', $userId)->get();
        if ($rows->isEmpty()) {
            return;
        }

        // Mapa misión base => juego, para no asignar dos misiones del mismo
        // juego al usuario en el mismo período (variedad).
        $poolGames = $baseModel::pluck('game_id', 'id')->all();

        // Primero: filas que se conservan (período actual o init). Sus juegos
        // quedan "usados" para el dedupe.
        $usedGames = [];
        $toReassign = [];
        foreach ($rows as $row) {
            if ($row->period_key === $currentKey) {
                $usedGames[$poolGames[$row->{$fk}] ?? -1] = true;
                continue;
            }
            if ($row->period_key === null) {
                $row->period_key = $currentKey; // init sin reiniciar progreso
                $row->save();
                $usedGames[$poolGames[$row->{$fk}] ?? -1] = true;
                continue;
            }
            $toReassign[] = $row; // período vencido
        }

        // Reasigna las vencidas eligiendo, si se puede, una misión de un juego
        // aún no usado por el usuario este período.
        foreach ($toReassign as $row) {
            $candidates = array_keys(array_filter(
                $poolGames,
                fn ($game) => !isset($usedGames[$game]),
            ));
            if (empty($candidates)) {
                $candidates = array_keys($poolGames); // fallback: cualquiera
            }
            if (!empty($candidates)) {
                $chosen = $candidates[array_rand($candidates)];
                $row->{$fk} = $chosen;
                $usedGames[$poolGames[$chosen]] = true;
            }
            $row->current_value = 0;
            $row->status_id = StatusModel::PENDING;
            $row->completed_at = null;
            $row->period_key = $currentKey;
            $row->save();
        }
    }

    public function save(Model $mission): void
    {
        $mission->save();
    }
}
