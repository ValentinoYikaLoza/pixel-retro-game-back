<?php

namespace App\Repositories\Eloquent;

use App\Models\GameModel;
use App\Repositories\Contracts\GameRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentGameRepository implements GameRepositoryInterface
{
    /** Solo lo que el catálogo del cliente consume. */
    private const COLUMNS = ['id', 'name', 'title', 'enabled'];

    /** Catálogo + config de partida (para start/finish). */
    private const COLUMNS_WITH_CONFIG = [
        'id', 'name', 'title', 'enabled',
        'lives_cost', 'tick_ms', 'grid_width', 'grid_height',
    ];

    public function all(): Collection
    {
        return GameModel::query()->select(self::COLUMNS)->orderBy('id')->get();
    }

    public function findByCode(string $code): ?GameModel
    {
        return GameModel::query()
            ->select(self::COLUMNS_WITH_CONFIG)
            ->where('name', $code)
            ->first();
    }

    public function findById(int $id): ?GameModel
    {
        return GameModel::query()
            ->select(self::COLUMNS_WITH_CONFIG)
            ->find($id);
    }
}
