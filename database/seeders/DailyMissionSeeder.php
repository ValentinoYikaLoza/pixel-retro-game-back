<?php

namespace Database\Seeders;

use App\Models\DailyMissionModel;
use App\Models\GameModel;
use App\Models\MissionTypeModel;
use App\Models\RewardModel;
use Illuminate\Database\Seeder;

/**
 * Pool CURADO de misiones diarias. Los valores están AFINADOS por juego, porque
 * cada uno puntúa en escalas muy distintas: Snake ≈ 1 pt/comida (decenas),
 * Tetris/Invaders ~ cientos por acción (miles por partida) y Pac-Man 10/pellet
 * (varios miles). Solo las `active` se asignan; el contenido viejo se desactiva.
 */
class DailyMissionSeeder extends Seeder
{
    public function run(): void
    {
        // Plantillas de descripción por tipo.
        $tpl = [
            MissionTypeModel::POINTS => 'Acumula %d puntos en %s',
            MissionTypeModel::MATCHES => 'Juega %d partidas de %s',
            MissionTypeModel::SINGLE_GAME_SCORE => 'Haz %d puntos en una sola partida de %s',
        ];

        // Por juego: [POINTS(acumular), MATCHES, SINGLE_GAME_SCORE] = [valor, recompensa]
        $B = RewardModel::BRONZE;
        $S = RewardModel::SILVER;
        $config = [
            [GameModel::SNAKE, 'Snake', ['p' => [120, $B], 'm' => [5, $B], 's' => [40, $S]]],
            [GameModel::TETRIS, 'Tetris', ['p' => [4000, $B], 'm' => [5, $B], 's' => [1500, $S]]],
            [GameModel::PIXEL_INVADERS, 'Pixel Invaders', ['p' => [4000, $B], 'm' => [5, $B], 's' => [1500, $S]]],
            [GameModel::PACMAN, 'Pacman', ['p' => [6000, $B], 'm' => [5, $B], 's' => [2500, $S]]],
        ];

        $this->seedCurated(DailyMissionModel::class, $tpl, $config);
    }

    /**
     * Desactiva el contenido previo y upserta el set curado con ids estables.
     *
     * @param  class-string<\Illuminate\Database\Eloquent\Model>  $model
     */
    public static function seedCurated(string $model, array $tpl, array $config): void
    {
        $model::query()->update(['active' => false]);

        $types = [
            'p' => MissionTypeModel::POINTS,
            'm' => MissionTypeModel::MATCHES,
            's' => MissionTypeModel::SINGLE_GAME_SCORE,
        ];

        $id = 1;
        foreach ($config as [$gameId, $gameName, $vals]) {
            foreach ($types as $key => $typeId) {
                [$value, $reward] = $vals[$key];
                $model::updateOrCreate(['id' => $id], [
                    'description' => sprintf($tpl[$typeId], $value, $gameName),
                    'total_value' => $value,
                    'game_id' => $gameId,
                    'mission_type_id' => $typeId,
                    'reward_id' => $reward,
                    'active' => true,
                ]);
                $id++;
            }
        }
    }
}
