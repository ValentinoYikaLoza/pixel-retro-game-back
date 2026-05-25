<?php

namespace Database\Seeders;

use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\MonthlyMissionModel;
use App\Models\RewardModel;
use Illuminate\Database\Seeder;

/**
 * Pool CURADO de misiones mensuales (las metas más altas). Valores afinados por
 * juego. Reusa el upsert curado de DailyMissionSeeder.
 */
class MonthlyMissionSeeder extends Seeder
{
    public function run(): void
    {
        $tpl = [
            MissionTypeModel::POINTS => 'Acumula %d puntos en %s este mes',
            MissionTypeModel::MATCHES => 'Juega %d partidas de %s este mes',
            MissionTypeModel::SINGLE_GAME_SCORE => 'Haz %d puntos en una sola partida de %s',
        ];

        $G = RewardModel::GOLD;
        $config = [
            [GameModel::SNAKE, 'Snake', ['p' => [3000, $G], 'm' => [100, $G], 's' => [100, $G]]],
            [GameModel::TETRIS, 'Tetris', ['p' => [110000, $G], 'm' => [100, $G], 's' => [3500, $G]]],
            [GameModel::PIXEL_INVADERS, 'Pixel Invaders', ['p' => [110000, $G], 'm' => [100, $G], 's' => [3500, $G]]],
            [GameModel::PACMAN, 'Pacman', ['p' => [160000, $G], 'm' => [100, $G], 's' => [5500, $G]]],
        ];

        DailyMissionSeeder::seedCurated(MonthlyMissionModel::class, $tpl, $config);
    }
}
