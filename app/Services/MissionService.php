<?php

namespace App\Services;

use App\Events\MissionsUpdated;
use App\Exceptions\ApiException;
use App\Models\MissionTypeModel;
use App\Models\StatusModel;
use App\Repositories\Contracts\MissionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
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
        $this->rollover($userId);

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
        $this->rollover($userId);

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

    /**
     * Avanza las misiones del usuario asociadas a un juego tras una partida.
     * Lo invoca GameSessionService al terminar (el cliente nunca toca esto):
     *  - POINTS: suma el score de la partida.
     *  - MATCHES: +1 (una partida jugada).
     *  - EXACT: completa solo si el score coincide exactamente.
     * Rebroadcasta MissionsUpdated si hubo cambios.
     */
    public function advanceForGame(int $userId, int $gameId, int $score): void
    {
        $this->rollover($userId);
        $items = $this->missions->advanceableForGame($userId, $gameId);

        $changed = false;
        foreach ($items as $item) {
            $delta = match ($item['mission_type_id']) {
                MissionTypeModel::POINTS  => $score,
                MissionTypeModel::MATCHES => 1,
                MissionTypeModel::EXACT   => $score === $item['total_value'] ? $item['total_value'] : 0,
                default                   => 0,
            };

            if ($delta <= 0) {
                continue;
            }

            $mission = $item['model'];
            $mission->current_value += $delta;

            if ($mission->current_value >= $item['total_value']) {
                $mission->current_value = $item['total_value'];
                $mission->status_id = StatusModel::COMPLETED;
                $mission->completed_at = now();
            } else {
                $mission->status_id = StatusModel::IN_PROGRESS;
            }

            $this->missions->save($mission);
            $changed = true;
        }

        if ($changed) {
            $this->broadcastList($userId, $this->gather($userId));
        }
    }

    /**
     * Rollover perezoso de las misiones del usuario al período actual (UTC):
     * las del período vencido se reasignan y reinician antes de listar/avanzar.
     */
    private function rollover(int $userId): void
    {
        $now = Carbon::now('UTC');
        $this->missions->rolloverUserMissions($userId, [
            'daily' => $now->format('Y-m-d'),
            'weekly' => $now->format('o-W'),
            'monthly' => $now->format('Y-m'),
        ]);
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
