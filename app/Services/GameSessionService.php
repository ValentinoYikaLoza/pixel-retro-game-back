<?php

namespace App\Services;

use App\Events\StatsUpdated;
use App\Events\UsersUpdated;
use App\Exceptions\ApiException;
use App\Models\GameModel;
use App\Models\GameSessionModel;
use App\Models\UserModel;
use App\Repositories\Contracts\GameRepositoryInterface;
use App\Repositories\Contracts\GameSessionRepositoryInterface;
use App\Repositories\Contracts\UserGameStatRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Ciclo de vida de una partida con autoridad del servidor: start (consume
 * vida + crea sesión), finish (valida, persiste, otorga recompensas y avanza
 * misiones) y abandon (cierra y reembolsa si fue inmediato). El cliente nunca
 * suma exp/coins directamente: pasa por aquí.
 */
class GameSessionService
{
    /** 1 punto = 1 de exp (la exp se acumula en `user.score`, el del ranking). */
    private const EXP_PER_POINT = 1;
    /** 1 moneda por cada N puntos. */
    private const COINS_PER_POINTS = 10;
    /** Comida extra vale 5; sirve de cota superior anti-trampa. */
    private const MAX_POINTS_PER_FOOD = 5;
    /** Ventana (s) para reembolsar la vida al abandonar (cierre accidental). */
    private const REFUND_GRACE_SECONDS = 5;

    public function __construct(
        private readonly GameRepositoryInterface $games,
        private readonly UserRepositoryInterface $users,
        private readonly GameSessionRepositoryInterface $sessions,
        private readonly UserGameStatRepositoryInterface $stats,
        private readonly MissionService $missions,
    ) {}

    /**
     * Inicia una partida: valida vidas, descuenta el coste y abre la sesión.
     * Devuelve la config (semilla + dificultad) que el cliente debe usar.
     *
     * @return array<string, int>
     */
    public function start(int $userId, string $gameCode): array
    {
        $game = $this->games->findByCode($gameCode);
        if (!$game) {
            throw ApiException::notFound('Juego no encontrado');
        }
        if (!$game->enabled) {
            throw ApiException::unprocessable('Juego no disponible');
        }

        return DB::transaction(function () use ($userId, $game) {
            $user = $this->getUser($userId);

            if ($user->lives < $game->lives_cost) {
                throw ApiException::unprocessable('No tienes vidas suficientes');
            }

            $user->lives -= $game->lives_cost;
            $this->users->save($user);
            broadcast(new StatsUpdated($user))->toOthers();

            $seed = random_int(1, PHP_INT_MAX);
            $session = $this->sessions->create([
                'user_id' => $userId,
                'game_id' => $game->id,
                'status' => GameSessionModel::STATUS_IN_PROGRESS,
                'seed' => $seed,
                'started_at' => now(),
            ]);

            return [
                'session_id' => (int) $session->id,
                'seed' => $seed,
                'tick_ms' => (int) $game->tick_ms,
                'grid_width' => (int) $game->grid_width,
                'grid_height' => (int) $game->grid_height,
                'lives_left' => (int) $user->lives,
            ];
        });
    }

    /**
     * Cierra una partida: valida plausibilidad, persiste el resultado, otorga
     * exp/coins, actualiza el agregado y avanza misiones. Idempotente por sesión.
     *
     * @return array<string, int|bool>
     */
    public function finish(int $userId, int $sessionId, int $score, int $foodEaten, int $durationMs): array
    {
        return DB::transaction(function () use ($userId, $sessionId, $score, $foodEaten, $durationMs) {
            $session = $this->sessions->findForUser($sessionId, $userId);
            if (!$session) {
                throw ApiException::notFound('Sesión no encontrada');
            }

            // Idempotencia: si ya está cerrada, devolvemos el resultado previo
            // sin volver a otorgar recompensas (reintentos / doble submit).
            if ($session->status === GameSessionModel::STATUS_FINISHED) {
                $stat = $this->stats->find($userId, $session->game_id);

                return [
                    'is_high_score' => false,
                    'high_score' => (int) ($stat->high_score ?? $session->score),
                    'exp_gained' => (int) $session->exp_awarded,
                    'coins_gained' => (int) $session->coins_awarded,
                ];
            }
            if ($session->status !== GameSessionModel::STATUS_IN_PROGRESS) {
                throw ApiException::unprocessable('La sesión no está activa');
            }

            $game = $this->games->findById($session->game_id);
            $this->assertPlausible($score, $foodEaten, $durationMs, $game);

            $exp = $score * self::EXP_PER_POINT;
            $coins = intdiv($score, self::COINS_PER_POINTS);

            $session->status = GameSessionModel::STATUS_FINISHED;
            $session->score = $score;
            $session->food_eaten = $foodEaten;
            $session->duration_ms = $durationMs;
            $session->exp_awarded = $exp;
            $session->coins_awarded = $coins;
            $session->ended_at = now();
            $this->sessions->save($session);

            // Agregado por usuario+juego.
            $stat = $this->stats->firstOrNew($userId, $session->game_id);
            $isHighScore = $score > (int) $stat->high_score;
            $stat->high_score = max((int) $stat->high_score, $score);
            $stat->total_games = (int) $stat->total_games + 1;
            $stat->total_score = (int) $stat->total_score + $score;
            $stat->total_food = (int) $stat->total_food + $foodEaten;
            $stat->last_played_at = now();
            $this->stats->save($stat);

            // Recompensas al usuario (exp suma al `score` del ranking).
            $user = $this->getUser($userId);
            $user->score += $exp;
            $user->coins += $coins;
            $this->users->save($user);
            broadcast(new StatsUpdated($user))->toOthers();
            broadcast(new UsersUpdated($userId, [
                'users' => $this->users->ranking($user->division_id, $userId),
            ]))->toOthers();

            // Avance de misiones del juego (rebroadcasta MissionsUpdated dentro).
            $this->missions->advanceForGame($userId, $session->game_id, $score);

            return [
                'is_high_score' => $isHighScore,
                'high_score' => (int) $stat->high_score,
                'exp_gained' => $exp,
                'coins_gained' => $coins,
            ];
        });
    }

    /**
     * Duplica los puntos (exp) de una partida terminada tras ver un anuncio
     * recompensado. Otorga una vez más `exp_awarded` al ranking. Idempotente
     * por sesión (rewarded_at): no se puede cobrar dos veces. No toca el
     * agregado del juego ni las misiones (el récord lo marca el juego, no el ad).
     *
     * @return array<string, int|bool>
     */
    public function doubleReward(int $userId, int $sessionId): array
    {
        return DB::transaction(function () use ($userId, $sessionId) {
            $session = $this->sessions->findForUser($sessionId, $userId);
            if (!$session) {
                throw ApiException::notFound('Sesión no encontrada');
            }
            if ($session->status !== GameSessionModel::STATUS_FINISHED) {
                throw ApiException::unprocessable('La partida no ha terminado');
            }

            // Idempotencia: si ya se duplicó, no se vuelve a otorgar.
            if ($session->rewarded_at !== null) {
                return ['exp_gained' => 0, 'already_rewarded' => true];
            }

            $bonus = (int) $session->exp_awarded;
            $session->rewarded_at = now();
            $this->sessions->save($session);

            if ($bonus > 0) {
                $user = $this->getUser($userId);
                $user->score += $bonus;
                $this->users->save($user);
                broadcast(new StatsUpdated($user))->toOthers();
                broadcast(new UsersUpdated($userId, [
                    'users' => $this->users->ranking($user->division_id, $userId),
                ]))->toOthers();
            }

            return ['exp_gained' => $bonus, 'already_rewarded' => false];
        });
    }

    /**
     * Marca una sesión como abandonada (salir sin terminar). Reembolsa la vida
     * solo si fue un cierre inmediato, para no penalizar accidentes ni permitir
     * farmeo. Idempotente: si no hay sesión activa, no hace nada.
     */
    public function abandon(int $userId, int $sessionId): void
    {
        DB::transaction(function () use ($userId, $sessionId) {
            $session = $this->sessions->findForUser($sessionId, $userId);
            if (!$session || $session->status !== GameSessionModel::STATUS_IN_PROGRESS) {
                return;
            }

            $session->status = GameSessionModel::STATUS_ABANDONED;
            $session->ended_at = now();
            $this->sessions->save($session);

            $elapsed = $session->started_at
                ? now()->diffInSeconds($session->started_at)
                : self::REFUND_GRACE_SECONDS + 1;

            if ($elapsed <= self::REFUND_GRACE_SECONDS) {
                $game = $this->games->findById($session->game_id);
                $user = $this->getUser($userId);
                $user->lives += $game->lives_cost;
                $this->users->save($user);
                broadcast(new StatsUpdated($user))->toOthers();
            }
        });
    }

    /**
     * Tabla de líderes por juego (mejor puntaje) + el resumen del solicitante.
     *
     * @return array{leaderboard: \Illuminate\Support\Collection, me: array<string, int>}
     */
    public function leaderboard(int $userId, string $gameCode): array
    {
        $game = $this->games->findByCode($gameCode);
        if (!$game) {
            throw ApiException::notFound('Juego no encontrado');
        }

        $me = $this->stats->find($userId, $game->id);

        return [
            'leaderboard' => $this->stats->leaderboard($game->id, $userId),
            'me' => [
                'high_score' => (int) ($me->high_score ?? 0),
                'total_games' => (int) ($me->total_games ?? 0),
                'total_score' => (int) ($me->total_score ?? 0),
            ],
        ];
    }

    private function getUser(int $userId): UserModel
    {
        $user = $this->users->findById($userId);
        if (!$user) {
            throw ApiException::notFound('Usuario no encontrado');
        }

        return $user;
    }

    /**
     * Anti-trampa por plausibilidad: el resultado debe ser coherente con la
     * duración (no más comidas que ticks) y con el rango de puntos por comida.
     * Barato y suficiente; no requiere replay determinista.
     */
    private function assertPlausible(int $score, int $foodEaten, int $durationMs, ?GameModel $game): void
    {
        if ($score < 0 || $foodEaten < 0 || $durationMs <= 0) {
            throw ApiException::unprocessable('Resultado de partida inválido');
        }

        $tickMs = max(1, (int) ($game->tick_ms ?? 200));
        $ticks = intdiv($durationMs, $tickMs);

        // No se puede comer más veces que ticks transcurridos (+1 de holgura).
        if ($foodEaten > $ticks + 1) {
            throw ApiException::unprocessable('Resultado de partida inválido');
        }
        // Cada comida vale entre 1 y MAX_POINTS_PER_FOOD puntos.
        if ($score < $foodEaten || $score > $foodEaten * self::MAX_POINTS_PER_FOOD) {
            throw ApiException::unprocessable('Resultado de partida inválido');
        }
    }
}
