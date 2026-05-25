<?php

namespace Database\Seeders;

use App\Models\DailyMissionModel;
use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use Illuminate\Database\Seeder;

/**
 * Pool CURADO de misiones diarias: para cada juego, una misión de cada tipo
 * con valores y recompensa diseñados a mano (no aleatorios). Solo las `active`
 * se asignan a los usuarios; el contenido viejo queda desactivado (sin borrar,
 * para no romper asignaciones existentes).
 */
class DailyMissionSeeder extends Seeder
{
    public function run(): void
    {
        $games = [
            GameModel::SNAKE => 'Snake',
            GameModel::TETRIS => 'Tetris',
            GameModel::PIXEL_INVADERS => 'Pixel Invaders',
            GameModel::PACMAN => 'Pacman',
        ];

        // [tipo, valor, recompensa, plantilla de descripción]
        $defs = [
            [MissionTypeModel::POINTS, 600, RewardModel::BRONZE, 'Acumula %d puntos en %s'],
            [MissionTypeModel::MATCHES, 5, RewardModel::BRONZE, 'Juega %d partidas de %s'],
            [MissionTypeModel::SINGLE_GAME_SCORE, 400, RewardModel::SILVER, 'Haz %d puntos en una sola partida de %s'],
        ];

        DailyMissionModel::query()->update(['active' => false]);

        $id = 1;
        foreach ($games as $gameId => $gameName) {
            foreach ($defs as [$type, $value, $reward, $template]) {
                DailyMissionModel::updateOrCreate(['id' => $id], [
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
