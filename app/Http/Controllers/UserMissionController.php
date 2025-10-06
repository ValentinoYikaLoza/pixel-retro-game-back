<?php

namespace App\Http\Controllers;

use App\Events\MissionUpdated;
use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\StatusModel;
use App\Models\UserMissionModel;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserMissionController extends Controller
{

    // listar misiones
    public function list($userId)
    {
        $userMissions = UserMissionModel::with('mission')
            ->where('user_id', $userId)
            ->whereIn('status_id', [StatusModel::PENDING, StatusModel::IN_PROGRESS])
            ->get();

        return $this->ok($userMissions);
    }

    // avanzar progreso
    public function saveProgress(Request $request)
    {
        $userId = $request->user_id;
        $gameId = $request->game_id;
        $value = $request->value;

        if ($userId === null) {
            return $this->error('User id is required');
        }
        if ($gameId === null) {
            return $this->error('Game id is required');
        }

        $userMissions = UserMissionModel::with('mission')
            ->where('user_id', $userId)
            ->whereIn('status_id', [StatusModel::PENDING, StatusModel::IN_PROGRESS])
            ->whereHas('mission', function ($query) use ($gameId) {
                $query->where('game_id', $gameId);
            })
            ->get();

        $missionController = new MissionController();

        foreach ($userMissions as $userMission) {
            $missionController->progress($userMission, $value);
        }

        return $this->ok();
    }
}
