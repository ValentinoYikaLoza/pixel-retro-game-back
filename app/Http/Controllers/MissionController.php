<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mission\UpdateProgressRequest;
use App\Http\Resources\MissionResource;
use App\Services\MissionService;

class MissionController extends Controller
{
    public function __construct(private readonly MissionService $service) {}

    public function index(int $userId)
    {
        $missions = $this->service->listForUser($userId);

        return $this->ok('Listado de misiones', [
            'dailyMissions' => MissionResource::collection($missions['daily']),
            'weeklyMissions' => MissionResource::collection($missions['weekly']),
            'monthlyMissions' => MissionResource::collection($missions['monthly']),
        ]);
    }

    public function updateProgress(UpdateProgressRequest $request)
    {
        $this->service->updateProgress(
            (int) $request->user_id,
            $request->mission_type,
            (int) $request->mission_id,
            (int) $request->progress,
        );

        // La lista actualizada llega al cliente vía el evento MissionsUpdated.
        return $this->ok('Progreso de misión actualizado');
    }
}
