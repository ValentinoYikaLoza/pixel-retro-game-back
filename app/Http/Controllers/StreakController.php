<?php

namespace App\Http\Controllers;

use App\Http\Requests\Streak\BuyFreezeRequest;
use App\Http\Requests\Streak\ClaimGoalRequest;
use App\Http\Requests\Streak\GetStreakRequest;
use App\Services\StreakService;

class StreakController extends Controller
{
    public function __construct(private readonly StreakService $service) {}

    public function getStreak(GetStreakRequest $request)
    {
        return $this->ok(
            'Racha',
            $this->service->overview((int) $request->user_id, $request->month),
        );
    }

    public function claimGoal(ClaimGoalRequest $request)
    {
        return $this->ok(
            'Meta reclamada',
            $this->service->claimMonthlyGoal((int) $request->user_id, (int) $request->goal_id),
        );
    }

    public function buyFreeze(BuyFreezeRequest $request)
    {
        return $this->ok(
            'Congelador comprado',
            $this->service->buyFreeze((int) $request->user_id),
        );
    }
}
