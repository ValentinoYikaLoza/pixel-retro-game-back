<?php

namespace App\Repositories\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface MissionRepositoryInterface
{
    /** @return Collection<int, Model> */
    public function dailyForUser(int $userId): Collection;

    /** @return Collection<int, Model> */
    public function weeklyForUser(int $userId): Collection;

    /** @return Collection<int, Model> */
    public function monthlyForUser(int $userId): Collection;

    /**
     * Misión asignada a un usuario por tipo ('daily'|'weekly'|'monthly') e id
     * de la misión base. Devuelve null si el tipo no existe o no hay asignación.
     */
    public function findUserMission(string $type, int $userId, int $missionId): ?Model;

    /**
     * Misiones activas (pendientes/en progreso) de un usuario para un juego, con
     * su definición, listas para avanzar tras una partida. Cada item:
     * { model: Model usuario-misión, mission_type_id: int, total_value: int }.
     *
     * @return Collection<int, array{model: Model, mission_type_id: int, total_value: int}>
     */
    public function advanceableForGame(int $userId, int $gameId): Collection;

    public function save(Model $mission): void;
}
