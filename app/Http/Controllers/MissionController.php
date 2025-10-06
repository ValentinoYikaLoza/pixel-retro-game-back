<?php

namespace App\Http\Controllers;

use App\Events\MissionUpdated;
use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\StatusModel;
use App\Models\UserMissionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    /**
     * Avanza progreso en las misiones activas de un usuario.
     */
    public function progress(UserMissionModel $userMission, int $value = 0): void
    {
        $updated = $this->handleMissionProgress($userMission, $value);

        if ($updated) {
            broadcast(new MissionUpdated($userMission))->toOthers();
        }
    }

    /**
     * Maneja la lógica de progreso según el tipo de misión.
     */
    private function handleMissionProgress(UserMissionModel $userMission, int $value): bool
    {
        $mission = $userMission->mission;

        return match ($mission->mission_type_id) {
            MissionTypeModel::POINTS =>
            $this->updateProgress($userMission, $value),

            MissionTypeModel::MATCHES =>
            $this->updateProgress($userMission, 1),

            MissionTypeModel::STREAK =>
            $value > 0
                ? $this->updateProgress($userMission, $value, true)
                : false,

            MissionTypeModel::EXACT =>
            $value == $mission->total_value
                ? $this->completeMission($userMission)
                : false,

            default => false,
        };
    }

    /**
     * Actualiza el progreso de una misión.
     */
    private function updateProgress(UserMissionModel $userMission, int $increment, bool $replace = false): bool
    {
        $userMission->current_value = $replace
            ? $increment
            : $userMission->current_value + $increment;

        $userMission->status_id = StatusModel::IN_PROGRESS;

        if ($userMission->current_value >= $userMission->mission->total_value) {
            return $this->completeMission($userMission);
        }

        return $userMission->save();
    }

    /**
     * Completa misión.
     */
    private function completeMission(UserMissionModel $userMission): bool
    {
        $userMission->status_id = StatusModel::COMPLETED;
        $userMission->completed_at = Carbon::now();
        $userMission->current_value = $userMission->mission->total_value;

        return $userMission->save();
    }
}
