<?php

namespace App\Services;

use App\Models\DivisionModel;
use App\Models\UserModel;
use Illuminate\Support\Facades\DB;

/**
 * Liga semanal de divisiones (estilo Duolingo). Cada semana:
 *  - el #1 de cada división suma un `times_ranked_first`,
 *  - los mejores ascienden a la división superior y los peores descienden,
 *  - se reinician los `weekly_points` de todos.
 *
 * Las divisiones son una escalera por `id` (1 = más baja, máx = más alta), que
 * es el orden con el que se siembran. El ascenso/descenso mueve ±1 ese id.
 */
class LeagueService
{
    /** Fracción de cada división que asciende y que desciende (20%). */
    private const MOVE_FRACTION = 5; // 1/5

    /**
     * Ejecuta el rollover de liga. Toma una foto del ranking de cada división,
     * calcula movimientos y campeones, y los aplica + reinicia en una sola
     * transacción (para no reprocesar a quien cambió de división).
     *
     * @return array{promoted:int, relegated:int, champions:int}
     */
    public function rollover(): array
    {
        $maxTier = (int) (DivisionModel::max('id') ?? 1);
        $minTier = (int) (DivisionModel::min('id') ?? 1);

        $moves = [];      // userId => nueva division_id
        $champions = [];  // userIds que fueron #1 (con puntos > 0)
        $promoted = 0;
        $relegated = 0;

        foreach (DivisionModel::orderBy('id')->pluck('id') as $divId) {
            $divId = (int) $divId;
            $users = UserModel::query()
                ->where('division_id', $divId)
                ->orderByDesc('weekly_points')
                ->orderBy('id')
                ->get(['id', 'weekly_points']);

            $n = $users->count();
            if ($n === 0) {
                continue;
            }

            // Campeón de la división (solo si participó).
            if ((int) $users[0]->weekly_points > 0) {
                $champions[] = (int) $users[0]->id;
            }

            $promote = $divId < $maxTier ? max(1, intdiv($n, self::MOVE_FRACTION)) : 0;
            $relegate = $divId > $minTier ? max(1, intdiv($n, self::MOVE_FRACTION)) : 0;
            // Sin solaparse cuando la división es pequeña.
            $promote = min($promote, $n);
            $relegate = min($relegate, $n - $promote);

            // Top: ascienden (solo si participaron; 0 puntos no sube).
            for ($i = 0; $i < $promote; $i++) {
                if ((int) $users[$i]->weekly_points > 0) {
                    $moves[(int) $users[$i]->id] = $divId + 1;
                    $promoted++;
                }
            }
            // Fondo: descienden (la inactividad también baja).
            for ($i = 0; $i < $relegate; $i++) {
                $u = $users[$n - 1 - $i];
                $moves[(int) $u->id] = $divId - 1;
                $relegated++;
            }
        }

        DB::transaction(function () use ($moves, $champions) {
            foreach ($champions as $uid) {
                UserModel::where('id', $uid)->increment('times_ranked_first');
            }
            // Agrupar por división destino para minimizar updates.
            $byTarget = [];
            foreach ($moves as $uid => $target) {
                $byTarget[$target][] = $uid;
            }
            foreach ($byTarget as $target => $ids) {
                UserModel::whereIn('id', $ids)->update(['division_id' => $target]);
            }
            // Reinicia la liga para la nueva semana.
            UserModel::query()->update(['weekly_points' => 0]);
        });

        return [
            'promoted' => $promoted,
            'relegated' => $relegated,
            'champions' => count($champions),
        ];
    }
}
