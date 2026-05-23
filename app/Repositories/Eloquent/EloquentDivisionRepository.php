<?php

namespace App\Repositories\Eloquent;

use App\Models\DivisionModel;
use App\Repositories\Contracts\DivisionRepositoryInterface;
use Illuminate\Support\Collection;

class EloquentDivisionRepository implements DivisionRepositoryInterface
{
    private const COLUMNS = ['id', 'name'];

    public function all(): Collection
    {
        return DivisionModel::query()->select(self::COLUMNS)->orderBy('id')->get();
    }

    public function findById(int $id): ?DivisionModel
    {
        return DivisionModel::query()->select(self::COLUMNS)->find($id);
    }
}
