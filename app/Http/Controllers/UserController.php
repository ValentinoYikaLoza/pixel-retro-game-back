<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UpdateCoinsRequest;
use App\Http\Requests\User\UpdateLivesRequest;
use App\Http\Requests\User\UpdateStreakRequest;
use App\Http\Resources\UserRankingResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function ranking(int $id)
    {
        return $this->ok(
            'Listado de usuarios',
            UserRankingResource::collection($this->service->ranking($id)),
        );
    }

    public function show(int $id)
    {
        return $this->ok('Usuario', new UserResource($this->service->show($id)));
    }

    public function updateCoins(UpdateCoinsRequest $request, int $id)
    {
        $this->service->addCoins($id, (int) $request->coins);

        return $this->ok('Coins actualizados');
    }

    public function updateLives(UpdateLivesRequest $request, int $id)
    {
        $this->service->addLives($id, (int) $request->lives);

        return $this->ok('Lives actualizados');
    }

    public function updateStreak(UpdateStreakRequest $request, int $id)
    {
        $this->service->addStreak($id, (int) $request->streak);

        return $this->ok('Streak actualizados');
    }
}
