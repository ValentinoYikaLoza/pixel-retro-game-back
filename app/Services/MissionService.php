<?php

namespace App\Services;

use App\Events\MissionsUpdated;
use App\Exceptions\ApiException;
use App\Repositories\Contracts\MissionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MissionService
{
    public function __construct(
        private readonly MissionRepositoryInterface $missions,
        private readonly UserRepositoryInterface $users,
    ) {}

    /**
     * Misiones diarias/semanales/mensuales de un usuario; emite el evento.
     *
     * @return array{daily: \Illuminate\Support\Collection, weekly: \Illuminate\Support\Collection, monthly: \Illuminate\Support\Collection}
     */
    public function listForUser(int $userId): array
    {
        $this->assertUserExists($userId);

        $missions = $this->gather($userId);
        $this->broadcastList($userId, $missions);

        return $missions;
    }

    /**
     * Suma progreso a una misión asignada y devuelve el listado actualizado.
     *
     * @return array{daily: \Illuminate\Support\Collection, weekly: \Illuminate\Support\Collection, monthly: \Illuminate\Support\Collection}
     */
    public function updateProgress(int $userId, string $type, int $missionId, int $progress): array
    {
        $this->assertUserExists($userId);

        return DB::transaction(function () use ($userId, $type, $missionId, $progress) {
            $mission = $this->missions->findUserMission($type, $userId, $missionId);

            if (!$mission) {
                throw ApiException::notFound('Misión no encontrada');
            }

            $mission->current_value += $progress;
            $this->missions->save($mission);

            $missions = $this->gather($userId);
            $this->broadcastList($userId, $missions);

            return $missions;
        });
    }

    private function assertUserExists(int $userId): void
    {
        if (!$this->users->findById($userId)) {
            throw ApiException::notFound('Usuario no encontrado');
        }
    }

    /**
     * @return array{daily: \Illuminate\Support\Collection, weekly: \Illuminate\Support\Collection, monthly: \Illuminate\Support\Collection}
     */
    private function gather(int $userId): array
    {
        return [
            'daily' => $this->missions->dailyForUser($userId),
            'weekly' => $this->missions->weeklyForUser($userId),
            'monthly' => $this->missions->monthlyForUser($userId),
        ];
    }

    private function broadcastList(int $userId, array $missions): void
    {
        broadcast(new MissionsUpdated($userId, [
            'dailyMissions' => $missions['daily'],
            'weeklyMissions' => $missions['weekly'],
            'monthlyMissions' => $missions['monthly'],
        ]))->toOthers();
    }
}
