<?php

namespace Database\Seeders;

use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use App\Models\WeeklyMissionModel;
use Illuminate\Database\Seeder;

/**
 * Pool CURADO de misiones semanales (metas mayores que las diarias). Valores
 * afinados por juego. Reusa el upsert curado de DailyMissionSeeder.
 */
class WeeklyMissionSeeder extends Seeder
{
    public function run(): void
    {
        $tpl = [
            MissionTypeModel::POINTS => 'Acumula %d puntos en %s esta semana',
            MissionTypeModel::MATCHES => 'Juega %d partidas de %s esta semana',
            MissionTypeModel::SINGLE_GAME_SCORE => 'Haz %d puntos en una sola partida de %s',
        ];

        $S = RewardModel::SILVER;
        $G = RewardModel::GOLD;
        $config = [
            [GameModel::SNAKE, 'Snake', ['p' => [800, $S], 'm' => [25, $S], 's' => [70, $G]]],
            [GameModel::TETRIS, 'Tetris', ['p' => [28000, $S], 'm' => [25, $S], 's' => [2500, $G]]],
            [GameModel::PIXEL_INVADERS, 'Pixel Invaders', ['p' => [28000, $S], 'm' => [25, $S], 's' => [2500, $G]]],
            [GameModel::PACMAN, 'Pacman', ['p' => [40000, $S], 'm' => [25, $S], 's' => [4000, $G]]],
        ];

        DailyMissionSeeder::seedCurated(WeeklyMissionModel::class, $tpl, $config);
    }
}
