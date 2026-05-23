<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\GetUserRequest;
use App\Http\Requests\User\ListUsersRequest;
use App\Http\Requests\User\UpdateCoinsRequest;
use App\Http\Requests\User\UpdateExpRequest;
use App\Http\Requests\User\UpdateLivesRequest;
use App\Http\Requests\User\UpdateStreakRequest;
use App\Http\Resources\UserRankingResource;
use App\Http\Resources\UserResource;
use App\Services\UserService;

class UserController extends Controller
{
    public function __construct(private readonly UserService $service) {}

    public function getUser(GetUserRequest $request)
    {
        return $this->ok(
            'Usuario',
            new UserResource($this->service->show((int) $request->user_id)),
        );
    }

    public function listUsers(ListUsersRequest $request)
    {
        // El frontend espera data = { userList: [...] }.
        return $this->ok('Listado de usuarios', [
            'userList' => UserRankingResource::collection(
                $this->service->ranking((int) $request->user_id),
            ),
        ]);
    }

    public function updateCoins(UpdateCoinsRequest $request)
    {
        $this->service->addCoins((int) $request->user_id, (int) $request->coins);

        return $this->ok('Coins actualizados');
    }

    public function updateLives(UpdateLivesRequest $request)
    {
        $this->service->addLives((int) $request->user_id, (int) $request->lives);

        return $this->ok('Lives actualizados');
    }

    public function updateStreak(UpdateStreakRequest $request)
    {
        // Check-in diario de racha (idempotente por día UTC). Devuelve el
        // usuario con la racha ya actualizada. Nota: también se ejecuta al
        // cargar el usuario (getUser), así que el cliente no necesita llamarlo.
        return $this->ok(
            'Streak actualizado',
            new UserResource($this->service->checkInDaily((int) $request->user_id)),
        );
    }

    public function updateExp(UpdateExpRequest $request)
    {
        $this->service->addExp((int) $request->user_id, (int) $request->exp);

        return $this->ok('Exp actualizado');
    }
}
