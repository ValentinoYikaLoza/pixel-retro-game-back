<?php

namespace App\Http\Controllers;

use App\Events\MissionUpdated;
use App\Models\MissionTypeModel;
use App\Models\StatusModel;
use App\Models\UserMissionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MissionController extends Controller
{
    /**
     * Avanza progreso en las misiones activas de un usuario
     */
    public function progress($userId, string $eventType, string $game, int $value = 1)
    {
        // 🔎 Cargamos misiones activas del usuario con su misión relacionada
        $userMissions = UserMissionModel::with('mission')
            ->where('user_id', $userId)
            ->whereIn('status_id', [StatusModel::PENDING, StatusModel::IN_PROGRESS])
            ->get();

        foreach ($userMissions as $userMission) {
            $mission = $userMission->mission;

            // ⚡ Verificar si la misión corresponde al juego actual o si es "todos los modos"
            if ($mission->game !== $game && $mission->game !== 'todos los modos') {
                continue;
            }

            $updated = false;

            switch ($mission->mission_type_id) {
                case MissionTypeModel::POINTS:
                    if ($eventType === 'earned_points') {
                        $updated = $this->updateProgress($userMission, $value);
                    }
                    break;

                case MissionTypeModel::MATCHES:
                    if ($eventType === 'played_match') {
                        $updated = $this->updateProgress($userMission, 1);
                    }
                    break;

                case MissionTypeModel::STREAK:
                    if ($eventType === 'match_streak' && $value > 0) {
                        $updated = $this->updateProgress($userMission, $value, true);
                    }
                    break;

                case MissionTypeModel::MULTIGAME:
                    if ($eventType === 'played_match') {
                        // MULTIGAME son varios juegos, aquí puedes validar lista
                        if (in_array($game, ['Tetris Game', 'Snake Game'])) {
                            $updated = $this->updateProgress($userMission, 1);
                        }
                    }
                    break;

                case MissionTypeModel::EXACT:
                    if ($eventType === 'earned_points' && $value == $mission->total_value) {
                        $updated = $this->completeMission($userMission);
                    }
                    break;
            }

            // 🔥 Enviar evento en tiempo real si hubo actualización
            if ($updated) {
                broadcast(new MissionUpdated($userMission))->toOthers();
            }
        }
    }

    /**
     * Actualiza el progreso de una misión
     */
    private function updateProgress(UserMissionModel $userMission, int $increment, bool $replace = false): bool
    {
        if ($replace) {
            $userMission->current_value = $increment;
        } else {
            $userMission->current_value += $increment;
        }

        $userMission->status_id = StatusModel::IN_PROGRESS;

        if ($userMission->current_value >= $userMission->mission->total_value) {
            return $this->completeMission($userMission);
        }

        return $userMission->save();
    }

    /**
     * Completa misión
     */
    private function completeMission(UserMissionModel $userMission): bool
    {
        $userMission->status_id = StatusModel::COMPLETED;
        $userMission->completed_at = Carbon::now();
        $userMission->current_value = $userMission->mission->total_value;
        return $userMission->save();
    }
}
