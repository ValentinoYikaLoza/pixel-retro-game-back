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
        // Obstáculos en forma de pilares (islas): la víbora los rodea, los
        // canales quedan siempre >= gap de ancho y nunca se forman callejones
        // ni alcobas. La dificultad sube con bloques más grandes / canales más
        // angostos, más velocidad y arena más chica.
        // [level, w, h, wrap, block, gap, tick, target]
        $defs = [
            [1, 30, 20, true,  0, 0, 240, 8],
            [2, 30, 20, true,  0, 0, 210, 15],
            [3, 30, 20, false, 0, 0, 190, 25],
            [4, 30, 20, false, 2, 4, 175, 35],
            [5, 30, 20, false, 2, 3, 160, 50],
            [6, 28, 18, false, 3, 4, 150, 65],
            [7, 28, 18, false, 3, 3, 140, 80],
            [8, 26, 16, false, 3, 3, 130, 100],
            [9, 24, 16, false, 3, 3, 120, 120],
            [10, 22, 14, false, 4, 3, 110, 150],
        ];

        foreach ($defs as [$level, $w, $h, $wrap, $block, $gap, $tick, $target]) {
            $cells = $block > 0 ? $this->pillars($w, $h, $block, $gap) : [];

            $walls = $this->finalize($w, $h, $cells);

            // Hace el nivel solvible: erosiona callejones sin salida (dead-ends)
            // y sella bolsones inalcanzables. Así no hay trampas de muerte
            // instantánea ni comida en zonas a las que no se puede llegar.
            [$walls, $freeCells] = $this->makeSolvable($w, $h, $wrap, $walls);

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
     * Garantiza un nivel solvible: (1) erosiona callejones sin salida hasta que
     * cada celda abierta tenga >= 2 salidas, y (2) sella como pared toda celda
     * abierta inalcanzable desde el centro. Devuelve [paredes, celdas libres].
     *
     * @param  array<int, array{0:int,1:int}>  $walls
     * @return array{0: array<int, array{0:int,1:int}>, 1: int}
     */
    private function makeSolvable(int $w, int $h, bool $wrap, array $walls): array
    {
        $blocked = [];
        foreach ($walls as [$x, $y]) {
            $blocked["$x,$y"] = true;
        }

        // 1) Erosiona dead-ends: una celda con <= 1 salida abierta es una trampa
        //    (la serpiente no podría salir), así que se rellena con pared. Se
        //    repite hasta estabilizar (consume el túnel entero hasta el cruce).
        $blocked = $this->erodeDeadEnds($w, $h, $wrap, $blocked);

        // 2) Sella bolsones inalcanzables desde el centro (donde nace la víbora).
        $reachable = $this->floodFill($w, $h, $wrap, $blocked, intdiv($w, 2), intdiv($h, 2));
        for ($y = 0; $y < $h; $y++) {
            for ($x = 0; $x < $w; $x++) {
                $k = "$x,$y";
                if (!isset($blocked[$k]) && !isset($reachable[$k])) {
                    $blocked[$k] = true;
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
     * Rellena con pared toda celda abierta que tenga <= 1 vecino abierto
     * (callejón sin salida), repitiendo hasta que no queden. La zona segura
     * central se preserva siempre. Recibe y devuelve el mapa de bloqueadas.
     *
     * @param  array<string, bool>  $blocked
     * @return array<string, bool>
     */
    private function erodeDeadEnds(int $w, int $h, bool $wrap, array $blocked): array
    {
        $cx = intdiv($w, 2);
        $cy = intdiv($h, 2);

        $changed = true;
        while ($changed) {
            $changed = false;
            for ($y = 0; $y < $h; $y++) {
                for ($x = 0; $x < $w; $x++) {
                    $k = "$x,$y";
                    if (isset($blocked[$k])) {
                        continue;
                    }
                    if (abs($x - $cx) <= 2 && abs($y - $cy) <= 2) {
                        continue; // no tocar la zona segura de spawn
                    }

                    $open = 0;
                    foreach ([[1, 0], [-1, 0], [0, 1], [0, -1]] as [$dx, $dy]) {
                        $nx = $x + $dx;
                        $ny = $y + $dy;
                        if ($wrap) {
                            $nx = ($nx + $w) % $w;
                            $ny = ($ny + $h) % $h;
                        } elseif ($nx < 0 || $nx >= $w || $ny < 0 || $ny >= $h) {
                            continue; // el borde cuenta como bloqueado
                        }
                        if (!isset($blocked["$nx,$ny"])) {
                            $open++;
                        }
                    }

                    if ($open <= 1) {
                        $blocked[$k] = true;
                        $changed = true;
                    }
                }
            }
        }

        return $blocked;
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

    /**
     * Pilares: bloques de pared `block`×`block` en una retícula, separados por
     * canales de ancho `gap` entre sí y respecto al borde. Como son islas, la
     * víbora siempre los rodea: no se generan callejones ni alcobas.
     */
    private function pillars(int $w, int $h, int $block, int $gap): array
    {
        $cells = [];
        $step = $block + $gap;
        for ($by = $gap; $by + $block <= $h - $gap; $by += $step) {
            for ($bx = $gap; $bx + $block <= $w - $gap; $bx += $step) {
                for ($dy = 0; $dy < $block; $dy++) {
                    for ($dx = 0; $dx < $block; $dx++) {
                        $cells[] = [$bx + $dx, $by + $dy];
                    }
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
