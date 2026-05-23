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
        GameModel::truncate();
        $rows = [
            ['id' => GameModel::SNAKE, 'name' => 'snake', 'title' => 'Snake', 'enabled' => true],
            ['id' => GameModel::TETRIS, 'name' => 'tetris', 'title' => 'Tetris', 'enabled' => true],
            ['id' => GameModel::PIXEL_INVADERS, 'name' => 'invaders', 'title' => 'Pixel Invaders', 'enabled' => true],
            ['id' => GameModel::PACMAN, 'name' => 'pacman', 'title' => 'Pacman', 'enabled' => true],
        ];

        foreach ($rows as $row) {
            GameModel::updateOrCreate(
                ['id' => $row['id']],
                $row
            );
        }
    }
}
