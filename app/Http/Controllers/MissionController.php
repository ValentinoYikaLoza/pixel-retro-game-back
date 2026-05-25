<?php

namespace App\Http\Controllers;

use App\Http\Requests\Mission\ListMissionsRequest;
use App\Http\Resources\MissionResource;
use App\Services\MissionService;

class MissionController extends Controller
{
    public function __construct(private readonly MissionService $service) {}

    public function index(ListMissionsRequest $request)
    {
        $missions = $this->service->listForUser((int) $request->user_id);

        return $this->ok('Listado de misiones', [
            'dailyMissions' => MissionResource::collection($missions['daily']),
            'weeklyMissions' => MissionResource::collection($missions['weekly']),
            'monthlyMissions' => MissionResource::collection($missions['monthly']),
        ]);
    }
}
