<?php

namespace App\Repositories\Eloquent;

use App\Models\UserModel;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    /** Columnas que el dominio necesita de un usuario individual. */
    private const COLUMNS = ['id', 'name', 'coins', 'lives', 'score', 'weekly_points', 'streak', 'last_streak_date', 'streak_freezes', 'last_milestone', 'division_id'];

    public function findById(int $id): ?UserModel
    {
        return UserModel::query()->select(self::COLUMNS)->find($id);
    }

    public function findForUpdate(int $id): ?UserModel
    {
        // FOR UPDATE: bloquea la fila hasta el commit de la transacción actual,
        // serializando las mutaciones concurrentes de stats (anti lost-update).
        return UserModel::query()->select(self::COLUMNS)->lockForUpdate()->find($id);
    }

    public function ranking(?int $divisionId, int $ensureUserId): Collection
    {
        // El ranking de liga ordena por puntos SEMANALES (se reinician cada
        // semana), no por el score de por vida: así es una competencia real.
        $users = $this->rankingQuery($divisionId)
            ->orderBy('u.weekly_points', 'DESC')
            ->limit(20)
            ->get();

        // Garantizar que el usuario solicitante esté presente aunque no entre
        // en el top 20: se mantienen 19 y se añade al final.
        if (!$users->contains('id', $ensureUserId)) {
            $current = $this->rankingQuery(null)
                ->where('u.id', $ensureUserId)
                ->first();

            if ($current) {
                $users = $users->slice(0, 19)->values();
                $users->push($current);
            }
        }

        return $users->values();
    }

    public function save(UserModel $user): void
    {
        $user->save();
    }

    /**
     * Query base del ranking con los joins de división y país.
     */
    private function rankingQuery(?int $divisionId)
    {
        // Se expone weekly_points bajo el alias `score`: el cliente del ranking
        // muestra los puntos de liga de la semana sin cambios en el frontend.
        $query = UserModel::query()
            ->from('user as u')
            ->leftJoin('division', 'u.division_id', '=', 'division.id')
            ->leftJoin('country', 'u.country_id', '=', 'country.id')
            ->select('u.id', 'u.name', 'u.weekly_points as score', 'u.times_ranked_first', 'country.flag');

        if ($divisionId) {
            $query->where('division.id', $divisionId);
        }

        return $query;
    }
}
