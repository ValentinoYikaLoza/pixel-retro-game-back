<?php

namespace App\Http\Controllers;

use App\Http\Resources\GameResource;
use App\Services\GameService;

class GameController extends Controller
{
    public function __construct(private readonly GameService $service) {}

    public function listGames()
    {
        return $this->ok(
            'Listado de juegos',
            GameResource::collection($this->service->list()),
        );
    }
}
