<?php

namespace App\Repositories\Eloquent;

use App\Models\UserModel;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentUserRepository implements UserRepositoryInterface
{
    /** Columnas que el dominio necesita de un usuario individual. */
    private const COLUMNS = ['id', 'name', 'coins', 'lives', 'score', 'streak', 'division_id'];

    public function findById(int $id): ?UserModel
    {
        return UserModel::query()->select(self::COLUMNS)->find($id);
    }

    public function ranking(?int $divisionId, int $ensureUserId): Collection
    {
        $users = $this->rankingQuery($divisionId)
            ->orderBy('u.score', 'DESC')
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
        $query = UserModel::query()
            ->from('user as u')
            ->leftJoin('division', 'u.division_id', '=', 'division.id')
            ->leftJoin('country', 'u.country_id', '=', 'country.id')
            ->select('u.id', 'u.name', 'u.score', 'u.times_ranked_first', 'country.flag');

        if ($divisionId) {
            $query->where('division.id', $divisionId);
        }

        return $query;
    }
}
