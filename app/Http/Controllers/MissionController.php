<?php

namespace App\Http\Controllers;

use App\Events\UserMissionsUpdated;
use App\Models\StatusModel;
use App\Models\UserModel;
use App\Models\UserDailyMissionModel;
use App\Models\UserWeeklyMissionModel;
use App\Models\UserMonthlyMissionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function list(Request $request)
    {
        $missions = $this->listBase($request);
        return $this->ok("Listado de misiones", $missions);
    }

    public function listBase(Request $request)
    {
        $user_id = $request->user_id;
        $user = UserModel::find($user_id);

        if (!$user) {
            return $this->error("Usuario no encontrado");
        }

        // === DAILY ===
        $dailyMissions = UserDailyMissionModel::query()
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
            ->where('user_daily_mission.user_id', $user_id)
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

        // === WEEKLY ===
        $weeklyMissions = UserWeeklyMissionModel::query()
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
            ->where('user_weekly_mission.user_id', $user_id)
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

        // === MONTHLY ===
        $monthlyMissions = UserMonthlyMissionModel::query()
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
            ->where('user_monthly_mission.user_id', $user_id)
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

        return [
            'dailyMissions' => $dailyMissions,
            'weeklyMissions' => $weeklyMissions,
            'monthlyMissions' => $monthlyMissions,
        ];
    }

    public function updateProgress(Request $request)
    {
        DB::beginTransaction();
        try {
            $user_id = $request->user_id;
            $mission_type = $request->mission_type; // 'daily', 'weekly' o 'monthly'
            $mission_id = $request->mission_id;
            $progress = $request->progress;

            $user = UserModel::find($user_id);
            if (!$user) {
                return $this->error('Usuario no encontrado');
            }

            switch ($mission_type) {
                case 'daily':
                    $mission = UserDailyMissionModel::where('user_id', $user_id)
                        ->where('daily_mission_id', $mission_id)
                        ->first();
                    break;

                case 'weekly':
                    $mission = UserWeeklyMissionModel::where('user_id', $user_id)
                        ->where('weekly_mission_id', $mission_id)
                        ->first();
                    break;

                case 'monthly':
                    $mission = UserMonthlyMissionModel::where('user_id', $user_id)
                        ->where('monthly_mission_id', $mission_id)
                        ->first();
                    break;
            }

            if (!$mission) {
                return $this->error('Misión no encontrada');
            }

            // Actualizar progreso
            $mission->current_value += $progress;
            $mission->save();

            // Obtener el listado actualizado
            $missions = $this->listBase($request);

            // Emitir evento de actualización de misiones
            broadcast(new UserMissionsUpdated($user_id, $missions))->toOthers();

            DB::commit();
            return $this->ok('Progreso de misión actualizado', $missions);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error($e->getMessage());
        }
    }
}
