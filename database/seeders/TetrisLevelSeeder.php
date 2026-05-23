<?php

namespace Database\Seeders;

use App\Models\GameLevelModel;
use App\Models\GameModel;
use Illuminate\Database\Seeder;

/**
 * 10 niveles de Tetris. Reusa game_level: tick_ms = gravedad (ms por caída),
 * walls = bloques de basura iniciales (celdas pre-ocupadas), target_score =
 * LÍNEAS para superar el nivel (Tetris se supera por líneas, no por puntos). La
 * dificultad sube con más velocidad, más filas de basura y más líneas a limpiar.
 * Tablero estándar 10x20.
 */
class TetrisLevelSeeder extends Seeder
{
    public function run(): void
    {
        // [level, gravity_ms, garbage_rows, target_lines]
        $defs = [
            [1, 800, 0, 5],
            [2, 720, 0, 8],
            [3, 640, 1, 12],
            [4, 560, 1, 16],
            [5, 480, 2, 20],
            [6, 420, 2, 25],
            [7, 360, 3, 30],
            [8, 300, 4, 36],
            [9, 240, 5, 42],
            [10, 190, 6, 50],
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
