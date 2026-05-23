<?php

namespace Database\Seeders;

use App\Models\GameLevelModel;
use App\Models\GameModel;
use Illuminate\Database\Seeder;

/**
 * 10 niveles de Tetris. Reusa game_level: tick_ms = gravedad (ms por caída),
 * walls = bloques de basura iniciales (celdas pre-ocupadas), target_score =
 * puntos para superar el nivel. La dificultad sube con más velocidad, más
 * filas de basura y objetivo más alto. Tablero estándar 10x20.
 */
class TetrisLevelSeeder extends Seeder
{
    public function run(): void
    {
        // [level, gravity_ms, garbage_rows, target]
        $defs = [
            [1, 800, 0, 300],
            [2, 720, 0, 600],
            [3, 640, 1, 900],
            [4, 560, 1, 1300],
            [5, 480, 2, 1800],
            [6, 420, 2, 2400],
            [7, 360, 3, 3100],
            [8, 300, 4, 3900],
            [9, 240, 5, 4800],
            [10, 190, 6, 6000],
        ];

        $w = 10;
        $h = 20;

        foreach ($defs as [$level, $gravity, $garbage, $target]) {
            GameLevelModel::updateOrCreate(
                ['game_id' => GameModel::TETRIS, 'level' => $level],
                [
                    'tick_ms' => $gravity,
                    'grid_width' => $w,
                    'grid_height' => $h,
                    'wrap_around' => false,
                    'walls' => $this->garbage($w, $h, $garbage),
                    'target_score' => $target,
                ],
            );
        }
    }

    /**
     * Filas de basura al fondo: cada fila se llena salvo un hueco, y el hueco
     * se desplaza por fila para que no se limpien de una sola pieza.
     *
     * @return array<int, array{0:int,1:int}>
     */
    private function garbage(int $w, int $h, int $rows): array
    {
        $cells = [];
        for ($r = 0; $r < $rows; $r++) {
            $y = $h - 1 - $r;
            $hole = ($r * 3 + 1) % $w;
            for ($x = 0; $x < $w; $x++) {
                if ($x !== $hole) {
                    $cells[] = [$x, $y];
                }
            }
        }
        return $cells;
    }
}
