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

    public function save(Model $mission): void;
}
