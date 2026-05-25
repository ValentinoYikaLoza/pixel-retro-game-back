<?php

namespace Database\Seeders;

use App\Models\GameLevelModel;
use App\Models\GameModel;
use Illuminate\Database\Seeder;

/**
 * Niveles de Pac-Man. A diferencia de Snake/Invaders, el layout del laberinto
 * es arte fijo y vive en el cliente (necesita pellets/power/casa/spawn que el
 * esquema `walls` no guarda), así que aquí `walls` va vacío y el cliente dibuja
 * el maze por nivel. La dificultad sube con la velocidad (tick_ms = ms por
 * casilla; menor = más rápido) y, más adelante, con la IA de fantasmas.
 *
 * `target_score` = nº de comestibles del maze (pellets + power). Superar el
 * nivel = comerlos todos. El maze L1 validado tiene 232 comestibles.
 */
class PacmanLevelSeeder extends Seeder
{
    /** Comestibles del maze L1 (validado: 228 pellets + 4 power). */
    private const L1_PELLETS = 232;

    public function run(): void
    {
        // [level, tick_ms (ms/casilla), target_score]
        $defs = [
            [1, 165, self::L1_PELLETS],
            [2, 155, self::L1_PELLETS],
            [3, 145, self::L1_PELLETS],
            [4, 135, self::L1_PELLETS],
            [5, 128, self::L1_PELLETS],
            [6, 120, self::L1_PELLETS],
            [7, 113, self::L1_PELLETS],
            [8, 107, self::L1_PELLETS],
        ];

        foreach ($defs as [$level, $tick, $target]) {
            GameLevelModel::updateOrCreate(
                ['game_id' => GameModel::PACMAN, 'level' => $level],
                [
                    'tick_ms' => $tick,
                    'grid_width' => 28,
                    'grid_height' => 29,
                    'wrap_around' => true, // túnel: el cliente envuelve la fila central
                    'walls' => [],
                    'target_score' => $target,
                ],
            );
        }
    }
}
