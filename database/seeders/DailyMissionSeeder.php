<?php

namespace Database\Seeders;

use App\Models\DailyMissionModel;
use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class DailyMissionSeeder extends Seeder
{
    public function run(): void
    {
        DailyMissionModel::truncate();

        $missionsToGenerate = 1000;
        $missions = [];

        for ($i = 0; $i < $missionsToGenerate; $i++) {

            $game = Arr::random([
                GameModel::TETRIS,
                GameModel::SNAKE,
                GameModel::PACMAN,
                GameModel::PIXEL_INVADERS,
            ]);

            $type = Arr::random([
                MissionTypeModel::POINTS,
                MissionTypeModel::POINTS,
                MissionTypeModel::MATCHES,
                MissionTypeModel::EXACT
            ]);

            [$points, $description] = $this->generateMission($type, $game);

            // Calcular valor total ponderado
            $rewardValue = $this->calculateRewardValue($type, $points);

            $missions[] = [
                'description'     => $description,
                'total_value'     => $points,
                'reward_id'       => $this->getRewardLevel($rewardValue),
                'mission_type_id' => $type,
                'game_id'         => $game,
            ];
        }

        DailyMissionModel::insert($missions);
    }

    private function generateMission(int $type, string $game): array
    {
        $game_name = $this->getGameName($game);

        return match ($type) {
            MissionTypeModel::POINTS    => $this->generatePointsMission($game_name),
            MissionTypeModel::MATCHES   => $this->generateMatchesMission($game_name),
            MissionTypeModel::EXACT     => $this->generateExactMission($game_name),
        };
    }

    private function generatePointsMission(string $game): array
    {
        $points = rand(100, 3000);
        return [$points, "Obtén {$points} puntos en {$game}"];
    }

    private function generateMatchesMission(string $game): array
    {
        $matches = rand(3, 15);
        return [$matches, "Juega {$matches} partidas en {$game}"];
    }

    private function generateExactMission(string $game): array
    {
        $exactPoints = Arr::random([100, 200, 500, 1000]);
        return [$exactPoints, "Obtén exactamente {$exactPoints} puntos en {$game}"];
    }

    private function calculateRewardValue(int $type, int $baseValue): float
    {
        // Ajustar peso según tipo de misión
        $multiplier = match ($type) {
            MissionTypeModel::POINTS => 1.0,
            MissionTypeModel::MATCHES => 100,  // cada partida equivale a 100 puntos
            MissionTypeModel::EXACT => 1.5,   // más difícil, mayor recompensa
        };

        return $baseValue * $multiplier;
    }

    private function getRewardLevel(float $value): int
    {
        return match (true) {
            $value < 500     => RewardModel::BRONZE,
            $value < 1500    => RewardModel::SILVER,
            $value < 3000    => RewardModel::GOLD,
            default          => RewardModel::BRONZE,
        };
    }

    private function getGameName(int $gameId): string
    {
        return match ($gameId) {
            GameModel::TETRIS         => 'Tetris',
            GameModel::SNAKE          => 'Snake',
            GameModel::PACMAN         => 'Pacman',
            GameModel::PIXEL_INVADERS => 'Pixel Invaders',
        };
    }
}
