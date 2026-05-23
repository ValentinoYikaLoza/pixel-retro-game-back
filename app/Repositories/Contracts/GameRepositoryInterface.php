<?php

namespace App\Repositories\Contracts;

use Illuminate\Support\Collection;

interface GameRepositoryInterface
{
    /**
     * Catálogo de juegos.
     *
     * @return Collection<int, \App\Models\GameModel>
     */
    public function all(): Collection;
}
