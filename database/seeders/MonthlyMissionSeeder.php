<?php

namespace Database\Seeders;

use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\MonthlyMissionModel;
use App\Models\RewardModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class MonthlyMissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MonthlyMissionModel::truncate();

        $missionsToGenerate = 300;
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

        MonthlyMissionModel::insert($missions);
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
        $points = rand(10000, 50000);
        return [$points, "Obtén {$points} puntos en {$game}"];
    }

    private function generateMatchesMission(string $game): array
    {
        $matches = rand(50, 200);
        return [$matches, "Juega {$matches} partidas en {$game}"];
    }

    private function generateExactMission(string $game): array
    {
        $exactPoints = Arr::random([2000, 3000, 5000, 10000]);
        return [$exactPoints, "Obtén exactamente {$exactPoints} puntos en {$game}"];
    }

    private function calculateRewardValue(int $type, int $baseValue): float
    {
        // Ajustar peso según tipo de misión
        $multiplier = match ($type) {
            MissionTypeModel::POINTS  => 1.5,
            MissionTypeModel::MATCHES => 200,  // partidas mensuales valen más
            MissionTypeModel::EXACT   => 2.5,  // más complicada
        };

        return $baseValue * $multiplier;
    }

    private function getRewardLevel(float $value): int
    {
        return match (true) {
            $value < 5000    => RewardModel::BRONZE,
            $value < 15000   => RewardModel::SILVER,
            $value < 40000   => RewardModel::GOLD,
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
