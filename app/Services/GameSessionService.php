<?php

namespace App\Services;

use App\Events\StatsUpdated;
use App\Events\UsersUpdated;
use App\Exceptions\ApiException;
use App\Models\GameLevelModel;
use App\Models\GameModel;
use App\Models\GameSessionModel;
use App\Models\UserModel;
use App\Repositories\Contracts\GameLevelRepositoryInterface;
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
    /** Comida extra vale 5; sirve de cota superior anti-trampa (Snake). */
    private const MAX_POINTS_PER_FOOD = 5;
    /** Ventana (s) para reembolsar la vida al abandonar (cierre accidental). */
    private const REFUND_GRACE_SECONDS = 5;

    /** Cotas anti-trampa de Tetris (generosas: solo atajan fraude grosero). */
    private const TETRIS_MAX_LINES_PER_SEC = 5;
    private const TETRIS_MAX_POINTS_PER_LINE = 2000;
    private const TETRIS_MAX_DROP_POINTS_PER_SEC = 300;

    public function __construct(
        private readonly GameRepositoryInterface $games,
        private readonly UserRepositoryInterface $users,
        private readonly GameSessionRepositoryInterface $sessions,
        private readonly UserGameStatRepositoryInterface $stats,
        private readonly GameLevelRepositoryInterface $levels,
        private readonly MissionService $missions,
    ) {}

    /**
     * Inicia una partida en un nivel: valida vidas y desbloqueo, descuenta el
     * coste y abre la sesión. Devuelve la config del nivel (semilla, velocidad,
     * grid, paredes, wrap y objetivo) que el cliente debe usar.
     *
     * @return array<string, mixed>
     */
    public function start(int $userId, string $gameCode, int $level = 1): array
    {
        $game = $this->games->findByCode($gameCode);
        if (!$game) {
            throw ApiException::notFound('Juego no encontrado');
        }
        if (!$game->enabled) {
            throw ApiException::unprocessable('Juego no disponible');
        }

        $levelConfig = $this->levels->find($game->id, $level);
        if (!$levelConfig) {
            throw ApiException::notFound('Nivel no encontrado');
        }
        if (!$this->isLevelUnlocked($userId, $game->id, $level)) {
            throw ApiException::unprocessable('Nivel bloqueado');
        }

        return DB::transaction(function () use ($userId, $game, $level, $levelConfig) {
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
                'level' => $level,
                'status' => GameSessionModel::STATUS_IN_PROGRESS,
                'seed' => $seed,
                'started_at' => now(),
            ]);

            return [
                'session_id' => (int) $session->id,
                'level' => $level,
                'seed' => $seed,
                'tick_ms' => (int) $levelConfig->tick_ms,
                'grid_width' => (int) $levelConfig->grid_width,
                'grid_height' => (int) $levelConfig->grid_height,
                'wrap_around' => (bool) $levelConfig->wrap_around,
                'walls' => $levelConfig->walls ?? [],
                'target_score' => (int) $levelConfig->target_score,
                'lives_left' => (int) $user->lives,
            ];
        });
    }

    /**
     * Niveles del juego con el progreso del usuario: mejor puntaje, superado y
     * desbloqueado (el nivel 1 siempre, los demás si el anterior está superado).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listLevels(int $userId, string $gameCode): array
    {
        $game = $this->games->findByCode($gameCode);
        if (!$game) {
            throw ApiException::notFound('Juego no encontrado');
        }

        $levels = $this->levels->forGame($game->id);
        // Progreso indexado por game_level_id.
        $progress = $this->levels->progressForUser($userId, $game->id)
            ->keyBy('game_level_id');

        $clearedByLevel = [];
        foreach ($levels as $lvl) {
            $p = $progress->get($lvl->id);
            $clearedByLevel[$lvl->level] = $p && $p->cleared_at !== null;
        }

        return $levels->map(function (GameLevelModel $lvl) use ($progress, $clearedByLevel) {
            $p = $progress->get($lvl->id);

            return [
                'level' => (int) $lvl->level,
                'tick_ms' => (int) $lvl->tick_ms,
                'grid_width' => (int) $lvl->grid_width,
                'grid_height' => (int) $lvl->grid_height,
                'wrap_around' => (bool) $lvl->wrap_around,
                'walls' => $lvl->walls ?? [],
                'target_score' => (int) $lvl->target_score,
                'best_score' => (int) ($p->best_score ?? 0),
                'cleared' => $p && $p->cleared_at !== null,
                'unlocked' => $lvl->level === 1
                    || ($clearedByLevel[$lvl->level - 1] ?? false),
            ];
        })->all();
    }

    /** Un nivel está desbloqueado si es el 1 o el anterior está superado. */
    private function isLevelUnlocked(int $userId, int $gameId, int $level): bool
    {
        if ($level <= 1) {
            return true;
        }

        $previous = $this->levels->find($gameId, $level - 1);
        if (!$previous) {
            return false;
        }

        return $this->levels->progressForUser($userId, $gameId)
            ->first(fn ($p) => $p->game_level_id === $previous->id
                && $p->cleared_at !== null) !== null;
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

            $levelConfig = $this->levels->find($session->game_id, $session->level);
            $tickMs = $levelConfig
                ? (int) $levelConfig->tick_ms
                : (int) (($this->games->findById($session->game_id))->tick_ms ?? 200);
            $this->assertPlausible($session->game_id, $score, $foodEaten, $durationMs, $tickMs);

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

            // Progreso del nivel: mejor puntaje y "superado" si alcanza el
            // objetivo; al superarlo por primera vez se desbloquea el siguiente.
            $levelCleared = false;
            $unlockedNext = false;
            if ($levelConfig) {
                $progress = $this->levels->firstOrNewProgress($userId, $levelConfig->id);
                $wasCleared = $progress->cleared_at !== null;
                $progress->best_score = max((int) $progress->best_score, $score);

                if ($score >= $levelConfig->target_score) {
                    $levelCleared = true;
                    if (!$wasCleared) {
                        $progress->cleared_at = now();
                        $unlockedNext = $this->levels
                            ->find($session->game_id, $session->level + 1) !== null;
                    }
                }

                $this->levels->saveProgress($progress);
            }

            // Avance de misiones del juego (rebroadcasta MissionsUpdated dentro).
            $this->missions->advanceForGame($userId, $session->game_id, $score);

            return [
                'is_high_score' => $isHighScore,
                'high_score' => (int) $stat->high_score,
                'exp_gained' => $exp,
                'coins_gained' => $coins,
                'level_cleared' => $levelCleared,
                'unlocked_next' => $unlockedNext,
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
     * Anti-trampa por plausibilidad, específico por juego. `$metric` es la
     * telemetría secundaria (comidas en Snake, líneas en Tetris). Barato y
     * suficiente; solo ataja resultados groseramente imposibles.
     */
    private function assertPlausible(int $gameId, int $score, int $metric, int $durationMs, int $tickMs): void
    {
        if ($score < 0 || $metric < 0 || $durationMs <= 0) {
            throw ApiException::unprocessable('Resultado de partida inválido');
        }

        if ($gameId === GameModel::SNAKE) {
            // metric = comidas: no más que ticks, y cada una vale 1..MAX puntos.
            $ticks = intdiv($durationMs, max(1, $tickMs));
            if ($metric > $ticks + 1) {
                throw ApiException::unprocessable('Resultado de partida inválido');
            }
            if ($score < $metric || $score > $metric * self::MAX_POINTS_PER_FOOD) {
                throw ApiException::unprocessable('Resultado de partida inválido');
            }
            return;
        }

        // Tetris (y demás por defecto): metric = líneas. Cotas por tiempo.
        $seconds = $durationMs / 1000;
        $maxLines = (int) ($seconds * self::TETRIS_MAX_LINES_PER_SEC) + 4;
        if ($metric > $maxLines) {
            throw ApiException::unprocessable('Resultado de partida inválido');
        }
        $maxScore = $metric * self::TETRIS_MAX_POINTS_PER_LINE
            + (int) ($seconds * self::TETRIS_MAX_DROP_POINTS_PER_SEC) + 500;
        if ($score > $maxScore) {
            throw ApiException::unprocessable('Resultado de partida inválido');
        }
    }
}
