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
    /** El objetivo se limita a esta fracción del área jugable (debe ser
     * alcanzable: la serpiente no puede crecer más que las celdas libres). */
    private const TARGET_FACTOR = 0.6;

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

            $walls = $this->finalize($w, $h, $cells);

            // Sella las zonas abiertas inalcanzables (pockets) convirtiéndolas
            // en pared: evita que aparezca comida en puntos muertos y que el
            // preview muestre interiores abiertos que no se pueden alcanzar.
            [$walls, $freeCells] = $this->sealPockets($w, $h, $wrap, $walls);

            // El objetivo nunca debe exceder lo que cabe en el área jugable:
            // la serpiente no puede crecer más que las celdas libres, así que un
            // target demasiado alto haría el nivel imposible de superar.
            $maxTarget = max(1, (int) floor(($freeCells - 1) * self::TARGET_FACTOR));
            $target = min($target, $maxTarget);

            GameLevelModel::updateOrCreate(
                ['game_id' => GameModel::SNAKE, 'level' => $level],
                [
                    'tick_ms' => $tick,
                    'grid_width' => $w,
                    'grid_height' => $h,
                    'wrap_around' => $wrap,
                    'walls' => $walls,
                    'target_score' => $target,
                ],
            );
        }
    }

    /**
     * Convierte en pared toda celda abierta que no sea alcanzable desde el
     * centro (donde aparece la serpiente). Devuelve [paredes, celdas libres].
     *
     * @param  array<int, array{0:int,1:int}>  $walls
     * @return array{0: array<int, array{0:int,1:int}>, 1: int}
     */
    private function sealPockets(int $w, int $h, bool $wrap, array $walls): array
    {
        $blocked = [];
        foreach ($walls as [$x, $y]) {
            $blocked["$x,$y"] = true;
        }

        $reachable = $this->floodFill($w, $h, $wrap, $blocked, intdiv($w, 2), intdiv($h, 2));

        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $k = "$x,$y";
                if (!isset($blocked[$k]) && !isset($reachable[$k])) {
                    $blocked[$k] = true; // pocket aislado -> pared
                }
            }
        }

        $sealed = [];
        foreach (array_keys($blocked) as $k) {
            [$x, $y] = explode(',', $k);
            $sealed[] = [(int) $x, (int) $y];
        }

        return [$sealed, count($reachable)];
    }

    /**
     * Flood fill 4-direccional desde (sx,sy) sobre celdas libres, respetando el
     * wrap-around. Devuelve el conjunto de celdas alcanzables como mapa key=>true.
     *
     * @param  array<string, bool>  $blocked
     * @return array<string, bool>
     */
    private function floodFill(int $w, int $h, bool $wrap, array $blocked, int $sx, int $sy): array
    {
        $reachable = [];
        $start = "$sx,$sy";
        if (isset($blocked[$start])) {
            return $reachable;
        }

        $reachable[$start] = true;
        $queue = [[$sx, $sy]];

        while ($queue) {
            [$x, $y] = array_pop($queue);
            foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                $nx = $x + $dx;
                $ny = $y + $dy;
                if ($wrap) {
                    $nx = ($nx + $w) % $w;
                    $ny = ($ny + $h) % $h;
                } elseif ($nx < 0 || $nx >= $w || $ny < 0 || $ny >= $h) {
                    continue;
                }

                $k = "$nx,$ny";
                if (isset($blocked[$k]) || isset($reachable[$k])) {
                    continue;
                }

                $reachable[$k] = true;
                $queue[] = [$nx, $ny];
            }
        }

        return $reachable;
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
