<?php

namespace Database\Seeders;

use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use App\Models\WeeklyMissionModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class WeeklyMissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WeeklyMissionModel::truncate();

        $missionsToGenerate = 500;
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

        WeeklyMissionModel::insert($missions);
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
        $points = rand(1000, 9000);
        return [$points, "Obtén {$points} puntos en {$game}"];
    }

    private function generateMatchesMission(string $game): array
    {
        $matches = rand(10, 40);
        return [$matches, "Juega {$matches} partidas en {$game}"];
    }

    private function generateExactMission(string $game): array
    {
        $exactPoints = Arr::random([500, 1000, 2000]);
        return [$exactPoints, "Obtén exactamente {$exactPoints} puntos en {$game}"];
    }

    private function calculateRewardValue(int $type, int $baseValue): float
    {
        // Ajustar peso según tipo de misión
        $multiplier = match ($type) {
            MissionTypeModel::POINTS  => 1.2,
            MissionTypeModel::MATCHES => 150,   // cada partida vale más que en una diaria
            MissionTypeModel::EXACT   => 2.0,   // más complicada, recompensa mejor
        };

        return $baseValue * $multiplier;
    }

    private function getRewardLevel(float $value): int
    {
        return match (true) {
            $value < 1500   => RewardModel::BRONZE,
            $value < 4000   => RewardModel::SILVER,
            $value < 8000   => RewardModel::GOLD,
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
