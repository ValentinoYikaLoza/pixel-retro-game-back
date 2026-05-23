<?php

namespace App\Repositories\Eloquent;

use App\Models\GameModel;
use App\Repositories\Contracts\GameRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentGameRepository implements GameRepositoryInterface
{
    /** Solo lo que el cliente consume. */
    private const COLUMNS = ['id', 'name', 'title', 'enabled'];

    public function all(): Collection
    {
        return GameModel::query()->select(self::COLUMNS)->orderBy('id')->get();
    }
}
