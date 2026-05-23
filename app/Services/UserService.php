<?php

namespace App\Services;

use App\Events\StatsUpdated;
use App\Events\UsersUpdated;
use App\Exceptions\ApiException;
use App\Models\UserModel;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function __construct(private readonly UserRepositoryInterface $users) {}

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
     * Stats de un usuario; además emite el evento de actualización.
     */
    public function show(int $id): UserModel
    {
        $user = $this->getById($id);

        broadcast(new StatsUpdated($user))->toOthers();

        return $user;
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

    public function addStreak(int $id, int $streak): UserModel
    {
        return $this->applyStat($id, fn (UserModel $u) => $u->streak += $streak);
    }

    /** La EXP del juego se almacena en `score` (es lo que muestra el ranking). */
    public function addExp(int $id, int $exp): UserModel
    {
        return $this->applyStat($id, fn (UserModel $u) => $u->score += $exp);
    }

    /**
     * Aplica una mutación atómica sobre un stat del usuario y emite el evento.
     */
    private function applyStat(int $id, callable $mutate): UserModel
    {
        return DB::transaction(function () use ($id, $mutate) {
            $user = $this->getById($id);
            $mutate($user);
            $this->users->save($user);

            broadcast(new StatsUpdated($user))->toOthers();

            return $user;
        });
    }
}
