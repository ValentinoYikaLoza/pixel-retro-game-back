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
    public function run(): void
    {
        // 10 niveles. La dificultad sube combinando: velocidad (tick_ms ↓),
        // mapa (rota A→C→B→D, cada vez más cerrado) y la IA de fantasmas
        // (frightened más corto, menos scatter, salen antes) — esto último se
        // deriva del nivel en el cliente.
        //
        // target_score = nº de comestibles del mapa de ese nivel (validado):
        //   A=232, C=248, B=266, D=258. grid_height = filas del mapa (A/C=29,
        //   B/D=28). El layout del maze vive en el cliente (arte fijo), por eso
        //   walls va vacío.
        //
        // [level, tick_ms (ms/casilla), target_score, grid_height]
        $defs = [
            [1, 165, 232, 29], // A
            [2, 157, 232, 29], // A
            [3, 150, 248, 29], // C
            [4, 143, 248, 29], // C
            [5, 136, 266, 28], // B
            [6, 129, 266, 28], // B
            [7, 122, 258, 28], // D
            [8, 115, 258, 28], // D
            [9, 108, 266, 28], // B
            [10, 100, 258, 28], // D
        ];

        foreach ($defs as [$level, $tick, $target, $gridH]) {
            GameLevelModel::updateOrCreate(
                ['game_id' => GameModel::PACMAN, 'level' => $level],
                [
                    'tick_ms' => $tick,
                    'grid_width' => 28,
                    'grid_height' => $gridH,
                    'wrap_around' => true, // túnel: el cliente envuelve la fila central
                    'walls' => [],
                    'target_score' => $target,
                ],
            );
        }
    }
}
