<?php

namespace Database\Seeders;

use App\Models\GameLevelModel;
use App\Models\GameModel;
use Illuminate\Database\Seeder;

/**
 * 10 niveles de Pixel Invaders. Reusa game_level: tick_ms = velocidad base de
 * avance de la formación (menos = más rápido), walls = celdas de los búnkeres
 * destructibles (en un grid de grid_width x grid_height), target_score = puntos
 * para superar el nivel. El nº de filas/columnas de invasores, la cadencia de
 * disparo, los tipos de enemigo, las oleadas y el jefe los deriva el cliente a
 * partir del nº de nivel.
 */
class InvadersLevelSeeder extends Seeder
{
    private const GW = 24;
    private const GH = 24;

    public function run(): void
    {
        // [level, step_ms, target]
        $defs = [
            [1, 600, 500],
            [2, 540, 900],
            [3, 500, 1400],
            [4, 460, 2000],
            [5, 420, 2800],   // nivel con jefe (level % 5 == 0)
            [6, 380, 3600],
            [7, 340, 4500],
            [8, 300, 5500],
            [9, 260, 6800],
            [10, 220, 8500],  // nivel con jefe
        ];

        foreach ($defs as [$level, $step, $target]) {
            // Menos búnkeres a mayor nivel (más difícil cubrirse).
            $bunkers = max(1, 4 - intdiv($level - 1, 3));

            GameLevelModel::updateOrCreate(
                ['game_id' => GameModel::PIXEL_INVADERS, 'level' => $level],
                [
                    'tick_ms' => $step,
                    'grid_width' => self::GW,
                    'grid_height' => self::GH,
                    'wrap_around' => false,
                    'walls' => $this->bunkers($bunkers),
                    'target_score' => $target,
                ],
            );
        }
    }

    /**
     * Distribuye [count] búnkeres (bloques 5x3) repartidos en el ancho, cerca
     * del fondo (sobre la nave). Cada celda es un bloque destructible.
     *
     * @return array<int, array{0:int,1:int}>
     */
    private function bunkers(int $count): array
    {
        $cells = [];
        $bw = 5;
        $bh = 3;
        $oy = self::GH - 6; // fila superior del búnker (deja hueco para la nave)
        $slot = self::GW / ($count + 1);

        for ($i = 1; $i <= $count; $i++) {
            $cx = (int) round($slot * $i);
            $ox = $cx - intdiv($bw, 2);
            for ($dy = 0; $dy < $bh; $dy++) {
                for ($dx = 0; $dx < $bw; $dx++) {
                    // Hueco central inferior (forma de fortín).
                    if ($dy === $bh - 1 && $dx === intdiv($bw, 2)) {
                        continue;
                    }
                    $x = $ox + $dx;
                    $y = $oy + $dy;
                    if ($x >= 0 && $x < self::GW && $y >= 0 && $y < self::GH) {
                        $cells[] = [$x, $y];
                    }
                }
            }
        }

        return $cells;
    }
}
