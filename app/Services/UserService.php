<?php

namespace App\Services;

use App\Events\StatsUpdated;
use App\Events\UsersUpdated;
use App\Exceptions\ApiException;
use App\Models\UserModel;
use App\Repositories\Contracts\StreakRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserService
{
    /** Hitos de racha consecutiva → monedas que se otorgan al alcanzarlos. */
    public const STREAK_MILESTONES = [7 => 50, 30 => 250, 100 => 1000];

    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly StreakRepositoryInterface $streak,
    ) {}

    /**
     * Obtiene un usuario o lanza 404. Uso interno y para lecturas.
     */
    public function getById(int $id): UserModel
    {
        $user = $this->users->findById($id);

        if (!$user) {
            throw ApiException::notFound('Usuario no encontrado');
        }

        return $user;
    }

    /**
     * Como getById pero con bloqueo de fila; solo válido dentro de una
     * transacción. Lo usan las mutaciones de stats para evitar lost updates.
     */
    private function getForUpdate(int $id): UserModel
    {
        $user = $this->users->findForUpdate($id);

        if (!$user) {
            throw ApiException::notFound('Usuario no encontrado');
        }

        return $user;
    }

    /**
     * Stats de un usuario. Al cargarlos se hace el check-in diario de racha
     * (idempotente por día UTC) y se emite el evento de actualización.
     */
    public function show(int $id): UserModel
    {
        return $this->checkInDaily($id);
    }

    /**
     * Check-in diario de racha, server-authoritative con el día en UTC:
     * - mismo día que el último check-in → no cambia (idempotente)
     * - día consecutivo → +1
     * - hueco de >1 día → reinicia a 1, salvo que tenga congeladores suficientes
     *   para cubrir los días perdidos (se consumen y la racha continúa)
     * - sin registro previo → empieza en 1
     *
     * Además registra el día en el historial (para el calendario), otorga los
     * hitos consecutivos al cruzarlos y marca si la racha incrementó (para el
     * toast en el cliente). Emite StatsUpdated.
     */
    public function checkInDaily(int $id): UserModel
    {
        $user = DB::transaction(function () use ($id) {
            $user = $this->getForUpdate($id);

            $today = now('UTC')->toDateString();
            $last = $user->last_streak_date?->toDateString();
            $incremented = false;

            if ($last !== $today) {
                if ($last === null) {
                    $user->streak = 1;
                    $user->last_milestone = 0;
                } else {
                    $missed = (int) Carbon::parse($last)->diffInDays(Carbon::parse($today)) - 1;
                    if ($missed <= 0) {
                        $user->streak = (int) $user->streak + 1; // día consecutivo
                        $incremented = true;
                    } elseif ((int) $user->streak_freezes >= $missed) {
                        // Los congeladores cubren los días perdidos: la racha sigue.
                        $user->streak_freezes = (int) $user->streak_freezes - $missed;
                        $user->streak = (int) $user->streak + 1;
                        $incremented = true;
                    } else {
                        $user->streak = 1; // se rompió
                        $user->last_milestone = 0;
                    }
                }

                $user->last_streak_date = $today;
                $this->grantMilestones($user);
                $this->users->save($user);
                $this->streak->recordCheckIn($id, $today);
            }

            broadcast(new StatsUpdated($user))->toOthers();
            $user->setAttribute('streak_incremented', $incremented);

            return $user;
        });

        return $user;
    }

    /**
     * Otorga (en monedas, sobre el modelo en memoria) los hitos consecutivos que
     * la racha acaba de cruzar; evita repetirlos con `last_milestone`.
     */
    private function grantMilestones(UserModel $user): void
    {
        $coins = 0;
        foreach (self::STREAK_MILESTONES as $threshold => $reward) {
            if ((int) $user->streak >= $threshold && (int) $user->last_milestone < $threshold) {
                $coins += $reward;
                $user->last_milestone = $threshold;
            }
        }
        if ($coins > 0) {
            $user->coins = (int) $user->coins + $coins;
        }
    }

    /** Suma congeladores respetando un tope. Emite StatsUpdated. */
    public function addFreezes(int $id, int $amount, int $max): UserModel
    {
        return $this->applyStat($id, fn (UserModel $u) => $u->streak_freezes = min($max, (int) $u->streak_freezes + $amount));
    }

    /** Canjea monedas por vidas (valida saldo). Atómico. */
    public function purchaseLivesWithCoins(int $id, int $cost, int $lives): UserModel
    {
        return $this->applyStat($id, function (UserModel $u) use ($cost, $lives) {
            if ((int) $u->coins < $cost) {
                throw ApiException::unprocessable('No tienes monedas suficientes');
            }
            $u->coins = (int) $u->coins - $cost;
            $u->lives = (int) $u->lives + $lives;
        });
    }

    /** Compra un congelador con monedas (valida saldo y tope). Atómico. */
    public function purchaseFreeze(int $id, int $cost, int $max): UserModel
    {
        return $this->applyStat($id, function (UserModel $u) use ($cost, $max) {
            if ((int) $u->streak_freezes >= $max) {
                throw ApiException::unprocessable('Ya tienes el máximo de congeladores');
            }
            if ((int) $u->coins < $cost) {
                throw ApiException::unprocessable('No tienes monedas suficientes');
            }
            $u->coins = (int) $u->coins - $cost;
            $u->streak_freezes = (int) $u->streak_freezes + 1;
        });
    }

    /**
     * Ranking (top 20) de la división del usuario; emite el evento.
     *
     * @return Collection<int, UserModel>
     */
    public function ranking(int $userId): Collection
    {
        $user = $this->getById($userId);

        $users = $this->users->ranking($user->division_id, $userId);

        broadcast(new UsersUpdated($userId, ['users' => $users]))->toOthers();

        return $users;
    }

    public function addCoins(int $id, int $coins): UserModel
    {
        return $this->applyStat($id, fn (UserModel $u) => $u->coins += $coins);
    }

    public function addLives(int $id, int $lives): UserModel
    {
        return $this->applyStat($id, fn (UserModel $u) => $u->lives += $lives);
    }

    /**
     * La EXP suma al `score` de por vida (perfil) y a los `weekly_points` de la
     * liga (se reinician cada semana en el rollover de divisiones).
     */
    public function addExp(int $id, int $exp): UserModel
    {
        return $this->applyStat($id, function (UserModel $u) use ($exp) {
            $u->score += $exp;
            $u->weekly_points += $exp;
        });
    }

    /**
     * Aplica una mutación atómica sobre un stat del usuario y emite el evento.
     */
    private function applyStat(int $id, callable $mutate): UserModel
    {
        return DB::transaction(function () use ($id, $mutate) {
            $user = $this->getForUpdate($id);
            $mutate($user);
            $this->users->save($user);

            broadcast(new StatsUpdated($user))->toOthers();

            return $user;
        });
    }
}
