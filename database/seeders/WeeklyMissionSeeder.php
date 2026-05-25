<?php

namespace Database\Seeders;

use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use App\Models\WeeklyMissionModel;
use Illuminate\Database\Seeder;

/**
 * Pool CURADO de misiones semanales (metas mayores que las diarias). Una de
 * cada tipo por juego; solo las `active` se asignan.
 */
class WeeklyMissionSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            GameModel::SNAKE => 'Snake',
            GameModel::TETRIS => 'Tetris',
            GameModel::PIXEL_INVADERS => 'Pixel Invaders',
            GameModel::PACMAN => 'Pacman',
        ];

        $defs = [
            [MissionTypeModel::POINTS, 5000, RewardModel::SILVER, 'Acumula %d puntos en %s esta semana'],
            [MissionTypeModel::MATCHES, 25, RewardModel::SILVER, 'Juega %d partidas de %s esta semana'],
            [MissionTypeModel::SINGLE_GAME_SCORE, 1200, RewardModel::GOLD, 'Haz %d puntos en una sola partida de %s'],
        ];

        WeeklyMissionModel::query()->update(['active' => false]);

        $id = 1;
        foreach ($games as $gameId => $gameName) {
            foreach ($defs as [$type, $value, $reward, $template]) {
                WeeklyMissionModel::updateOrCreate(['id' => $id], [
                    'description' => sprintf($template, $value, $gameName),
                    'total_value' => $value,
                    'game_id' => $gameId,
                    'mission_type_id' => $type,
                    'reward_id' => $reward,
                    'active' => true,
                ]);
                $id++;
            }
        }
    }
}
