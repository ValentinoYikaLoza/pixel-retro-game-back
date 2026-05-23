<?php

namespace App\Repositories\Contracts;

use App\Models\DivisionModel;
use Illuminate\Support\Collection;

interface DivisionRepositoryInterface
{
    /**
     * @return Collection<int, DivisionModel>
     */
    public function all(): Collection;

    public function findById(int $id): ?DivisionModel;
}
