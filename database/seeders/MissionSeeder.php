<?php

namespace Database\Seeders;

use App\Models\FrequencyModel;
use App\Models\GameModel;
use App\Models\MissionModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class MissionSeeder extends Seeder
{
    public function run(): void
    {
        MissionModel::truncate();

        $missionsToGenerate = 1000;
        $missions = [];

        for ($i = 0; $i < $missionsToGenerate; $i++) {
            $frequency = Arr::random([
                FrequencyModel::DAILY,
                FrequencyModel::DAILY,
                FrequencyModel::DAILY,
                FrequencyModel::WEEKLY,
                FrequencyModel::WEEKLY,
                FrequencyModel::MONTHLY
            ]);

            $game = Arr::random([
                GameModel::TETRIS,
                GameModel::SNAKE,
                GameModel::PACMAN,
                GameModel::PIXEL_INVADERS,
                null,
            ]);

            $type = Arr::random([
                MissionTypeModel::POINTS,
                MissionTypeModel::POINTS,
                MissionTypeModel::MATCHES,
                MissionTypeModel::STREAK,
                MissionTypeModel::MULTIGAME,
                MissionTypeModel::EXACT
            ]);

            // Generar misión según tipo
            [$points, $description] = $this->generateMission($type, $frequency, $game);

            $missions[] = [
                'description'     => $description,
                'total_points'    => $points,
                'frequency_id'    => $frequency,
                'reward_id'       => $this->getRewardByPoints($points),
                'mission_type_id' => $type,
                'game_id'         => $game,
            ];
        }

        MissionModel::insert($missions);
    }

    private function generateMission(int $type, int $frequency, string $game): array
    {
        $game_name = $this->getGameName($game);

        return match ($type) {
            MissionTypeModel::POINTS    => $this->generatePointsMission($frequency, $game_name),
            MissionTypeModel::MATCHES   => $this->generateMatchesMission($frequency, $game_name),
            MissionTypeModel::STREAK    => $this->generateStreakMission($frequency, $game_name),
            MissionTypeModel::MULTIGAME => $this->generateMultiGameMission($frequency),
            MissionTypeModel::EXACT     => $this->generateExactMission($frequency, $game_name),
        };
    }

    private function generatePointsMission(int $frequency, string $game): array
    {
        $points = match ($frequency) {
            FrequencyModel::DAILY   => rand(50, 1500),
            FrequencyModel::WEEKLY  => rand(2000, 10000),
            FrequencyModel::MONTHLY => rand(15000, 100000),
        };
        return [$points, "Obtén {$points} puntos en {$game}"];
    }

    private function generateMatchesMission(int $frequency, string $game): array
    {
        $matches = match ($frequency) {
            FrequencyModel::DAILY   => rand(3, 10),
            FrequencyModel::WEEKLY  => rand(15, 40),
            FrequencyModel::MONTHLY => rand(50, 150),
        };
        return [$matches, "Juega {$matches} partidas en {$game}"];
    }

    private function generateStreakMission(int $frequency, string $game): array
    {
        $streak = match ($frequency) {
            FrequencyModel::DAILY   => rand(2, 5),
            FrequencyModel::WEEKLY  => rand(5, 10),
            FrequencyModel::MONTHLY => rand(10, 20),
        };
        return [$streak, "Juega {$streak} partidas seguidas sin perder en {$game}"];
    }

    private function generateMultiGameMission(int $frequency): array
    {
        $matches = match ($frequency) {
            FrequencyModel::DAILY   => rand(5, 10),
            FrequencyModel::WEEKLY  => rand(20, 40),
            FrequencyModel::MONTHLY => rand(50, 100),
        };
        return [$matches, "Juega {$matches} partidas combinadas entre Tetris y Snake"];
    }

    private function generateExactMission(int $frequency, string $game): array
    {
        $exactPoints = match ($frequency) {
            FrequencyModel::DAILY   => Arr::random([50, 100, 150, 200]),
            FrequencyModel::WEEKLY  => Arr::random([500, 1000, 1500]),
            FrequencyModel::MONTHLY => Arr::random([2000, 3000, 5000]),
        };
        return [$exactPoints, "Obtén exactamente {$exactPoints} puntos en {$game}"];
    }

    private function getRewardByPoints(int $points): int
    {
        return match (true) {
            $points < 500   => RewardModel::BRONZE,
            $points < 5000  => RewardModel::SILVER,
            default         => RewardModel::GOLD,
        };
    }

    private function getGameName(int $gameId): string
    {
        return match ($gameId) {
            GameModel::TETRIS         => 'Tetris Game',
            GameModel::SNAKE          => 'Snake Game',
            GameModel::PACMAN         => 'Pacman Game',
            GameModel::PIXEL_INVADERS => 'Pixel Invaders Game',
            default                    => 'cualquier juego',
        };
    }
}
