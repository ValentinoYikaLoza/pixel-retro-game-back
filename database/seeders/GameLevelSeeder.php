<?php

namespace Database\Seeders;

use App\Models\GameLevelModel;
use App\Models\GameModel;
use Illuminate\Database\Seeder;

/**
 * 10 niveles de Snake (máximo). La dificultad sube combinando todas las
 * mecánicas: más velocidad (tick_ms ↓), borde sólido (wrap_around=false),
 * obstáculos más densos (walls) y arena más chica (grid). El objetivo
 * (target_score) crece para superar el nivel y desbloquear el siguiente.
 */
class GameLevelSeeder extends Seeder
{
    public function run(): void
    {
        // [level, w, h, wrap, hbars[count,gap], vbars[count,gap], tick, target]
        $defs = [
            [1, 30, 20, true,  null,    null,    240, 8],
            [2, 30, 20, true,  null,    null,    210, 15],
            [3, 30, 20, false, null,    null,    190, 25],
            [4, 30, 20, false, [2, 8],  null,    175, 35],
            [5, 30, 20, false, [2, 8],  [2, 8],  160, 50],
            [6, 28, 18, false, [3, 7],  [2, 7],  150, 65],
            [7, 28, 18, false, [3, 6],  [3, 6],  140, 80],
            [8, 26, 16, false, [4, 6],  [3, 6],  130, 100],
            [9, 24, 16, false, [4, 5],  [4, 5],  120, 120],
            [10, 22, 14, false, [5, 4], [4, 4],  110, 150],
        ];

        foreach ($defs as [$level, $w, $h, $wrap, $hbars, $vbars, $tick, $target]) {
            $cells = [];
            if ($hbars) {
                $cells = array_merge($cells, $this->hbars($w, $h, $hbars[0], $hbars[1]));
            }
            if ($vbars) {
                $cells = array_merge($cells, $this->vbars($w, $h, $vbars[0], $vbars[1]));
            }

            GameLevelModel::updateOrCreate(
                ['game_id' => GameModel::SNAKE, 'level' => $level],
                [
                    'tick_ms' => $tick,
                    'grid_width' => $w,
                    'grid_height' => $h,
                    'wrap_around' => $wrap,
                    'walls' => $this->finalize($w, $h, $cells),
                    'target_score' => $target,
                ],
            );
        }
    }

    /** Barras horizontales equiespaciadas, cada una con un hueco central. */
    private function hbars(int $w, int $h, int $count, int $gap): array
    {
        $cells = [];
        for ($i = 1; $i <= $count; $i++) {
            $row = intdiv($h * $i, $count + 1);
            $gapStart = intdiv($w - $gap, 2);
            for ($x = 2; $x < $w - 2; $x++) {
                if ($x < $gapStart || $x >= $gapStart + $gap) {
                    $cells[] = [$x, $row];
                }
            }
        }
        return $cells;
    }

    /** Barras verticales equiespaciadas, cada una con un hueco central. */
    private function vbars(int $w, int $h, int $count, int $gap): array
    {
        $cells = [];
        for ($i = 1; $i <= $count; $i++) {
            $col = intdiv($w * $i, $count + 1);
            $gapStart = intdiv($h - $gap, 2);
            for ($y = 2; $y < $h - 2; $y++) {
                if ($y < $gapStart || $y >= $gapStart + $gap) {
                    $cells[] = [$col, $y];
                }
            }
        }
        return $cells;
    }

    /**
     * Limpia: clampa al grid, deduplica y libera la zona segura central (5x5)
     * para que la serpiente siempre tenga dónde aparecer.
     */
    private function finalize(int $w, int $h, array $cells): array
    {
        $cx = intdiv($w, 2);
        $cy = intdiv($h, 2);
        $out = [];
        foreach ($cells as [$x, $y]) {
            if ($x < 0 || $x >= $w || $y < 0 || $y >= $h) {
                continue;
            }
            if (abs($x - $cx) <= 2 && abs($y - $cy) <= 2) {
                continue;
            }
            $out["$x,$y"] = [$x, $y];
        }
        return array_values($out);
    }
}
