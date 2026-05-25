<?php

namespace App\Services;

use App\Events\MissionsUpdated;
use App\Exceptions\ApiException;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use App\Models\StatusModel;
use App\Repositories\Contracts\MissionRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;

class MissionService
{
    /** Monedas que otorga una misión completada, según el tier de su recompensa. */
    private const REWARD_COINS = [
        RewardModel::BRONZE => 50,
        RewardModel::SILVER => 150,
        RewardModel::GOLD => 400,
    ];

    public function __construct(
        private readonly MissionRepositoryInterface $missions,
        private readonly UserRepositoryInterface $users,
        private readonly UserService $userService,
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
        $coinsEarned = 0;
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
                // Otorga la recompensa exactamente una vez: al completarse la
                // misión sale del conjunto "advanceable", así que no se repite.
                $coinsEarned += self::REWARD_COINS[$item['reward_id'] ?? 0] ?? 0;
            } else {
                $mission->status_id = StatusModel::IN_PROGRESS;
            }

            $this->missions->save($mission);
            $changed = true;
        }

        if ($coinsEarned > 0) {
            $this->userService->addCoins($userId, $coinsEarned);
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
