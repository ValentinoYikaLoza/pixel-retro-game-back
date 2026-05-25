<?php

namespace Database\Seeders;

use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\MonthlyMissionModel;
use App\Models\RewardModel;
use Illuminate\Database\Seeder;

/**
 * Pool CURADO de misiones mensuales (las metas más altas). Una de cada tipo
 * por juego; solo las `active` se asignan.
 */
class MonthlyMissionSeeder extends Seeder
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
            [MissionTypeModel::POINTS, 25000, RewardModel::GOLD, 'Acumula %d puntos en %s este mes'],
            [MissionTypeModel::MATCHES, 100, RewardModel::GOLD, 'Juega %d partidas de %s este mes'],
            [MissionTypeModel::SINGLE_GAME_SCORE, 3000, RewardModel::GOLD, 'Haz %d puntos en una sola partida de %s'],
        ];

        MonthlyMissionModel::query()->update(['active' => false]);

        $id = 1;
        foreach ($games as $gameId => $gameName) {
            foreach ($defs as [$type, $value, $reward, $template]) {
                MonthlyMissionModel::updateOrCreate(['id' => $id], [
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
