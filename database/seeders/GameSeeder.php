<?php

namespace Database\Seeders;

use App\Models\GameModel;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sin truncate: `game` ahora es referenciada por game_session /
        // user_game_stat / *_mission; updateOrCreate basta y es idempotente.
        $rows = [
            ['id' => GameModel::SNAKE, 'name' => 'snake', 'title' => 'Snake', 'enabled' => true, 'lives_cost' => 1, 'tick_ms' => 200, 'grid_width' => 30, 'grid_height' => 20],
            ['id' => GameModel::TETRIS, 'name' => 'tetris', 'title' => 'Tetris', 'enabled' => true, 'lives_cost' => 1, 'tick_ms' => 500, 'grid_width' => 10, 'grid_height' => 20],
            ['id' => GameModel::PIXEL_INVADERS, 'name' => 'invaders', 'title' => 'Pixel Invaders', 'enabled' => true, 'lives_cost' => 1, 'tick_ms' => 200, 'grid_width' => 20, 'grid_height' => 20],
            ['id' => GameModel::PACMAN, 'name' => 'pacman', 'title' => 'Pacman', 'enabled' => true, 'lives_cost' => 1, 'tick_ms' => 200, 'grid_width' => 28, 'grid_height' => 31],
        ];

        foreach ($rows as $row) {
            GameModel::updateOrCreate(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
