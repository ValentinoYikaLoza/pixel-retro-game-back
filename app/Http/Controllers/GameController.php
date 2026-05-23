<?php

namespace App\Http\Controllers;

use App\Http\Requests\Game\AbandonGameRequest;
use App\Http\Requests\Game\DoubleGameRewardRequest;
use App\Http\Requests\Game\FinishGameRequest;
use App\Http\Requests\Game\GameLeaderboardRequest;
use App\Http\Requests\Game\ListGameLevelsRequest;
use App\Http\Requests\Game\StartGameRequest;
use App\Http\Resources\GameLeaderboardResource;
use App\Http\Resources\GameResource;
use App\Services\GameService;
use App\Services\GameSessionService;

class GameController extends Controller
{
    public function __construct(
        private readonly GameService $service,
        private readonly GameSessionService $sessions,
    ) {}

    public function listGames()
    {
        return $this->ok(
            'Listado de juegos',
            GameResource::collection($this->service->list()),
        );
    }

    /** Niveles del juego con el progreso del usuario (para el selector). */
    public function listGameLevels(ListGameLevelsRequest $request)
    {
        $levels = $this->sessions->listLevels(
            (int) $request->user_id,
            (string) $request->game_code,
        );

        return $this->ok('Niveles del juego', ['levels' => $levels]);
    }

    /** Inicia una partida en un nivel: consume vida y devuelve semilla + config. */
    public function startGame(StartGameRequest $request)
    {
        $data = $this->sessions->start(
            (int) $request->user_id,
            (string) $request->game_code,
            (int) ($request->level ?? 1),
        );

        return $this->ok('Partida iniciada', $data);
    }

    /** Cierra una partida: valida, otorga recompensas y avanza misiones. */
    public function finishGame(FinishGameRequest $request)
    {
        $data = $this->sessions->finish(
            (int) $request->user_id,
            (int) $request->session_id,
            (int) $request->score,
            (int) $request->food_eaten,
            (int) $request->duration_ms,
        );

        return $this->ok('Partida finalizada', $data);
    }

    /** Duplica los puntos de una partida terminada tras ver un anuncio. */
    public function doubleGameReward(DoubleGameRewardRequest $request)
    {
        $data = $this->sessions->doubleReward(
            (int) $request->user_id,
            (int) $request->session_id,
        );

        return $this->ok('Recompensa duplicada', $data);
    }

    /** Marca una partida como abandonada (salir sin terminar). */
    public function abandonGame(AbandonGameRequest $request)
    {
        $this->sessions->abandon(
            (int) $request->user_id,
            (int) $request->session_id,
        );

        return $this->ok('Partida abandonada');
    }

    /** Tabla de líderes por juego + resumen del solicitante. */
    public function getGameLeaderboard(GameLeaderboardRequest $request)
    {
        $data = $this->sessions->leaderboard(
            (int) $request->user_id,
            (string) $request->game_code,
        );

        return $this->ok('Leaderboard del juego', [
            'leaderboard' => GameLeaderboardResource::collection($data['leaderboard']),
            'me' => $data['me'],
        ]);
    }
}
